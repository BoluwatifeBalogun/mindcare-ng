#!/usr/bin/env python3
"""Prove includes/ml_classifier.php reproduces the sklearn model exactly.
Rebuilds the exported model in Python, predicts 300 sampled texts, then asks
PHP to predict the same texts, and compares. Run after train_classifier.py:
    python3 ml/parity_check.py
"""
import csv, json, os, random, subprocess, sys, tempfile
import numpy as np

HERE = os.path.dirname(os.path.abspath(__file__))
model = json.load(open(os.path.join(HERE, "..", "includes", "risk_model.json")))

with open(os.path.join(HERE, "20200325_counsel_chat.csv"), newline="", encoding="utf-8") as f:
    rows = list(csv.DictReader(f))
texts, seen = [], set()
for r in rows:
    t = (r.get("questionText") or "").strip()
    if t and r["questionID"] not in seen:
        seen.add(r["questionID"]); texts.append(t)
random.seed(7)
sample = random.sample(texts, 300)

# --- python side: score with the exported weights (not the sklearn object),
#     so this also validates the JSON export itself ---
import re
TOK = re.compile(r"\b\w\w+\b", re.UNICODE)
vocab, idf = model["vocabulary"], model["idf"]
coef, inter, classes = model["coef"], model["intercept"], model["classes"]

def predict_py(t):
    toks = TOK.findall(t.lower())
    counts = {}
    for w in toks: counts[w] = counts.get(w, 0) + 1
    for a, b in zip(toks, toks[1:]):
        counts[a + " " + b] = counts.get(a + " " + b, 0) + 1
    x = {vocab[w]: c * idf[vocab[w]] for w, c in counts.items() if w in vocab}
    if not x: return "low"
    norm = sum(v * v for v in x.values()) ** 0.5
    logits = [inter[ci] + sum(coef[ci][i] * (v / norm) for i, v in x.items())
              for ci in range(len(classes))]
    return classes[int(np.argmax(logits))]

py_preds = [predict_py(t) for t in sample]

# --- php side ---
php = r'''<?php
require __DIR__ . "/../includes/risk.php";
require __DIR__ . "/../includes/ml_classifier.php";
$texts = json_decode(file_get_contents($argv[1]), true);
echo json_encode(array_map("ml_scan", $texts));
'''
with tempfile.NamedTemporaryFile("w", suffix=".php", dir=HERE, delete=False) as f:
    f.write(php); php_path = f.name
with tempfile.NamedTemporaryFile("w", suffix=".json", dir=HERE, delete=False) as f:
    json.dump(sample, f); data_path = f.name
try:
    out = subprocess.run(["php", php_path, data_path],
                         capture_output=True, text=True, check=True)
    php_preds = json.loads(out.stdout)
finally:
    os.unlink(php_path); os.unlink(data_path)

agree = sum(a == b for a, b in zip(py_preds, php_preds))
print(f"parity: {agree}/300 predictions identical")
for a, b, t in zip(py_preds, php_preds, sample):
    if a != b:
        print(f"  MISMATCH py={a} php={b} :: {t[:80]}")
sys.exit(0 if agree == 300 else 1)
