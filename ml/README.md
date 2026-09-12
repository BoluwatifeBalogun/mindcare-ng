# Trained risk classifier

This folder contains the machine-learning component of MindCare NG: a text
risk classifier **trained on the mental-health conversations dataset named in
Chapter 3.3** (CounselChat corpus - Bertagnolli, 2020, MIT licence).

## What was trained

- **Task**: classify a student's message into `low / moderate / high` risk.
- **Data**: 815 unique help-seeking questions from `20200325_counsel_chat.csv`
  (deduplicated so no text appears in both train and test splits).
- **Labels** (weak supervision, stated openly): `high` = self-harm topic or
  explicit crisis language; `moderate` = clinical-distress topics (depression,
  anxiety, trauma, grief, substance abuse, eating disorders, domestic
  violence); `low` = everything else.
- **Model**: TF-IDF (unigrams + bigrams, 5,000 features) + multinomial
  logistic regression with balanced class weights - scikit-learn.

## Results (held-out 20%, see metrics.txt and figures/)

| metric | value |
|---|---|
| Accuracy | 0.706 |
| Macro F1 | 0.645 |
| High-risk precision | 1.000 |
| 5-fold CV macro F1 | 0.544 +/- 0.033 |

## How it runs in the app

`train_classifier.py` exports the fitted weights to
`../includes/risk_model.json`; `../includes/ml_classifier.php` reproduces
scikit-learn's TF-IDF transform and scores messages **natively in PHP** - no
Python needed in production. `parity_check.py` verifies the two
implementations agree (300/300 identical predictions).

In the pipeline the classifier is one of **three independent risk voters**:

1. keyword rules (deterministic, works offline),
2. this trained classifier (generalises past the keyword list - e.g. it
   flags "I took a lot of pills last month" and "giving my things away,
   writing goodbye letters", which contain no listed keyword),
3. the language model's contextual read of the conversation.

The worst local verdict feeds the item-8 resolver, which arbitrates
disagreements toward human review, never silence.

## Reproduce

```bash
pip install scikit-learn matplotlib
python3 ml/train_classifier.py   # retrains, rewrites risk_model.json + figures
python3 ml/parity_check.py       # proves PHP scoring matches Python
php tests/run_tests.php          # includes classifier behaviour tests
```

## Limitations (say these in the defense before anyone asks)

- Only 25 high-risk training examples, so high-risk **recall** is modest
  (0.40 held out); the keyword tier and LLM voter cover that gap - by design
  the classifier adds recall on *unlisted* phrasings and its high-risk calls
  are high-precision.
- Labels are heuristic (topic + seed keywords), not clinician-annotated.
- Corpus is US counselling data; Nigerian Pidgin and local idioms are
  future work (collect and fine-tune).
