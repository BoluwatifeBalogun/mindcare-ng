#!/usr/bin/env python3
"""
MindCare NG - risk classifier training
======================================
Trains a 3-class text risk classifier (low / moderate / high) on the
CounselChat corpus (Bertagnolli, 2020) named in Chapter 3.3 of the report,
then exports it to ../includes/risk_model.json so the PHP application can
score messages with no Python at runtime.

Labelling (weak supervision, stated openly in the report):
  high     - topic == "self-harm" OR the question contains explicit
             crisis language (same seed patterns as the app's Tier-3 list)
  moderate - clinical-distress topics: depression, anxiety, trauma,
             grief-and-loss, substance-abuse, addiction, eating-disorders,
             domestic-violence
  low      - everything else (relationships, parenting, self-esteem, ...)

Because labels are seeded partly by keywords, the classifier's value is
GENERALISATION: TF-IDF + logistic regression learns the vocabulary that
co-occurs with crisis posts (hopeless, anymore, goodbye, burden, pills...)
and can flag phrasings the literal keyword list misses. It votes alongside
the keyword scan and the LLM's contextual read; it never replaces them.

Run:  python3 ml/train_classifier.py
Outputs: ../includes/risk_model.json, figures/*.png, metrics.txt
"""
import csv, json, re, os, sys
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split, StratifiedKFold, cross_val_score
from sklearn.metrics import (accuracy_score, f1_score, classification_report,
                             confusion_matrix)
import matplotlib
matplotlib.use("Agg")
import matplotlib.pyplot as plt

HERE = os.path.dirname(os.path.abspath(__file__))
RAW = os.path.join(HERE, "20200325_counsel_chat.csv")
MODEL_OUT = os.path.join(HERE, "..", "includes", "risk_model.json")
FIG = os.path.join(HERE, "figures")
os.makedirs(FIG, exist_ok=True)

CLASSES = ["low", "moderate", "high"]
MODERATE_TOPICS = {"depression", "anxiety", "trauma", "grief-and-loss",
                   "substance-abuse", "addiction", "eating-disorders",
                   "domestic-violence"}
CRISIS = re.compile(
    r"suicid|kill (myself|me)\b|end (my|it all|my life)|"
    r"don'?t want to (live|be alive|exist)|take my (own )?life|"
    r"hurt(ing)? myself|self[- ]?harm|no reason to live|"
    r"better off (dead|without me)|want to die|wish i (was|were) dead",
    re.I)

def load():
    with open(RAW, newline="", encoding="utf-8") as f:
        rows = list(csv.DictReader(f))
    uniq = {}
    for r in rows:                      # many therapist answers per question:
        qid = r["questionID"]           # dedupe so no text leaks across the
        txt = (r.get("questionText") or "").strip()   # train/test split
        if qid not in uniq and txt:
            uniq[qid] = r
    X, y = [], []
    for r in uniq.values():
        t, topic = r["questionText"].strip(), r["topic"]
        if topic == "self-harm" or CRISIS.search(t):
            lab = "high"
        elif topic in MODERATE_TOPICS:
            lab = "moderate"
        else:
            lab = "low"
        X.append(t); y.append(lab)
    return X, y

def main():
    X, y = load()
    print(f"{len(X)} unique questions | "
          + " | ".join(f"{c}: {y.count(c)}" for c in CLASSES))

    Xtr, Xte, ytr, yte = train_test_split(
        X, y, test_size=0.2, stratify=y, random_state=42)

    vec = TfidfVectorizer(ngram_range=(1, 2), max_features=5000,
                          min_df=2, sublinear_tf=False)   # keep defaults the
    clf = LogisticRegression(max_iter=2000, C=1.0,        # PHP scorer replicates
                             class_weight="balanced")
    Xtr_v = vec.fit_transform(Xtr)
    clf.fit(Xtr_v, ytr)
    yp = clf.predict(vec.transform(Xte))

    acc = accuracy_score(yte, yp)
    mf1 = f1_score(yte, yp, average="macro")
    report = classification_report(yte, yp, labels=CLASSES, digits=3)
    print(report)

    # 5-fold CV on the full set for a stability figure
    skf = StratifiedKFold(n_splits=5, shuffle=True, random_state=42)
    cv = cross_val_score(
        LogisticRegression(max_iter=2000, class_weight="balanced"),
        vec.fit_transform(X), y, cv=skf, scoring="f1_macro")
    print(f"5-fold macro-F1: {cv.mean():.3f} +/- {cv.std():.3f}")

    # ---- figures for Chapter 4 ----
    cm = confusion_matrix(yte, yp, labels=CLASSES)
    fig, ax = plt.subplots(figsize=(5, 4.2))
    im = ax.imshow(cm, cmap="Purples")
    ax.set_xticks(range(3), CLASSES); ax.set_yticks(range(3), CLASSES)
    ax.set_xlabel("Predicted"); ax.set_ylabel("Actual")
    ax.set_title("Risk classifier - confusion matrix (held-out 20%)")
    for i in range(3):
        for j in range(3):
            ax.text(j, i, cm[i, j], ha="center", va="center",
                    color="white" if cm[i, j] > cm.max()/2 else "black")
    fig.colorbar(im); fig.tight_layout()
    fig.savefig(os.path.join(FIG, "confusion_matrix.png"), dpi=160)

    fig2, ax2 = plt.subplots(figsize=(5, 3.4))
    from sklearn.metrics import precision_recall_fscore_support
    p, r, f, _ = precision_recall_fscore_support(yte, yp, labels=CLASSES)
    xpos = np.arange(3); w = 0.26
    ax2.bar(xpos - w, p, w, label="precision", color="#8b7cc8")
    ax2.bar(xpos,     r, w, label="recall",    color="#5fae8f")
    ax2.bar(xpos + w, f, w, label="F1",        color="#c8a24b")
    ax2.set_xticks(xpos, CLASSES); ax2.set_ylim(0, 1.05)
    ax2.set_title("Per-class metrics (held-out 20%)"); ax2.legend()
    fig2.tight_layout()
    fig2.savefig(os.path.join(FIG, "per_class_metrics.png"), dpi=160)

    with open(os.path.join(HERE, "metrics.txt"), "w") as f_:
        f_.write(f"accuracy {acc:.3f}\nmacro-F1 {mf1:.3f}\n"
                 f"5-fold macro-F1 {cv.mean():.3f} +/- {cv.std():.3f}\n\n{report}")

    # ---- export: refit on ALL data (standard practice after evaluation) ----
    vec_full = TfidfVectorizer(ngram_range=(1, 2), max_features=5000, min_df=2)
    Xf = vec_full.fit_transform(X)
    clf_full = LogisticRegression(max_iter=2000, class_weight="balanced")
    clf_full.fit(Xf, y)

    order = [list(clf_full.classes_).index(c) for c in CLASSES]
    model = {
        "about": "MindCare NG risk classifier - TF-IDF(1-2gram) + logistic "
                 "regression, trained on CounselChat (Bertagnolli 2020). "
                 "Scored natively in PHP by includes/ml_classifier.php.",
        "classes": CLASSES,
        "vocabulary": {t: int(i) for t, i in vec_full.vocabulary_.items()},
        "idf": [round(float(v), 6) for v in vec_full.idf_],
        "coef": [[round(float(v), 6) for v in clf_full.coef_[o]] for o in order],
        "intercept": [round(float(clf_full.intercept_[o]), 6) for o in order],
        "eval": {"held_out_accuracy": round(acc, 4),
                 "held_out_macro_f1": round(mf1, 4),
                 "cv_macro_f1_mean": round(float(cv.mean()), 4),
                 "cv_macro_f1_std": round(float(cv.std()), 4),
                 "n_train_total": len(X)},
    }
    with open(MODEL_OUT, "w") as f_:
        json.dump(model, f_, separators=(",", ":"))
    print(f"exported {MODEL_OUT} "
          f"({os.path.getsize(MODEL_OUT)//1024} KB, {len(model['vocabulary'])} features)")

if __name__ == "__main__":
    sys.exit(main())
