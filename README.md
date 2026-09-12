# MindCare NG

**Counselling and Therapist AI for Nigerian Institutions** — full implementation of
the HND seminar project by Anozia Mary (F/HD/24/3211080), Department of Computer
Science, Yaba College of Technology, supervised by Mr. Ogundele Isreal.

Students get a confidential, 24-hour AI counselling companion (Amara), mood
tracking, standard PHQ-9/GAD-7 self-assessments, and session booking. Counsellors
get an auto-referral queue with conversation context and a direct reply channel.
Admins get analytics, user management, configurable crisis settings, and CSV
reports. The AI complements human counsellors; it never replaces them.

## Stack

PHP 8 · MySQL · Bootstrap 5 · JavaScript/AJAX (Chapter 3.7 of the report), with
Chart.js for charts and the Anthropic API (Claude) as the counselling model,
called through a server-side proxy so the API key never reaches the browser.
An interactive React demo of the same system lives in [`demo/`](demo/).

## Quick start (XAMPP)

1. Clone into your web root, e.g. `C:\xampp\htdocs\mindcare`
2. Start Apache and MySQL, then import `database/schema.sql` via phpMyAdmin
3. `cp config.sample.php config.php` and fill in your DB credentials and
   Anthropic API key (console.anthropic.com)
4. Open http://localhost/mindcare

Seeded staff accounts (change passwords after first login): `admin`,
`adebayo.f`, `chukwu.d` — password `changeme123`. Students self-register.

## The counselling pipeline (Figure 3.3)

Every student message runs server-side through `api/chat.php`:

1. **Local risk screen** — two independent voters, worst verdict wins:
   tiered keyword rules (`includes/risk.php`) and a **trained TF-IDF +
   logistic-regression classifier** (`includes/ml_classifier.php`, trained on
   the CounselChat corpus by `ml/train_classifier.py` — see `ml/README.md`).
   Both run server-side with no network, so crisis language is caught even if
   the AI API is down
2. **Inference** — Claude with a CBT-informed system prompt plus few-shot
   exemplars in the format of the *NLP Mental Health Conversations* corpus
   (Chapter 3.3); the model appends a hidden machine-read `<risk>` tag
3. **Resolve** — agreement on high risk opens the crisis panel and files a
   high-priority referral with conversation context; keyword/model disagreement
   downgrades to a supportive banner plus a human-review referral (never
   silence); API failure falls back to the pre-scan alone
4. **Route & persist** — referrals land in the counsellor queue; taking a case
   auto-schedules a follow-up within the admin-configured window

PHQ-9 item 9 files a high referral regardless of total score. Scoring uses the
published Kroenke/Spitzer cutoffs and runs server-side.

## Tests

```bash
php tests/run_tests.php
```

32 assertions over the risk engine, trained classifier, item-8 resolver, scoring
bands, and risk-tag
parsing. CI (`.github/workflows/ci.yml`) lints every PHP file and runs the suite
on each push.

## Before real students use this

Replace the seeded emergency contacts with the institution's verified lines
(admin → System settings), serve over HTTPS, and have the counselling unit
review Amara's responses per the report's AI Response Validation step (3.9).

## Hosting it online

Free hosts (InfinityFree and similar) are a trap for this app: they block or
throttle non-browser traffic and outbound API calls, which breaks the Claude
proxy. Use one of these instead:

**Option 1 — Railway (fastest from GitHub, ~$5/mo hobby):**
1. Push this repo to GitHub, then railway.app -> New Project -> Deploy from
   GitHub repo (the included `Dockerfile` is picked up automatically)
2. Add a MySQL database service to the same project
3. On the web service -> Variables, add: `DB_HOST`, `DB_PORT`, `DB_USER`,
   `DB_PASS`, `DB_NAME` (copy values from the MySQL service) and
   `ANTHROPIC_API_KEY`
4. Import the schema: MySQL service -> Data tab -> run the contents of
   `database/schema.sql` (skip the CREATE DATABASE/USE lines and use the
   database Railway created)
5. Settings -> Networking -> Generate Domain (target port 80). Done — HTTPS
   included.

**Option 2 — shared cPanel hosting (most defense-proof, ~$2-3/mo,
Namecheap/WhoGoHost/QServers):**
1. Upload the repo files into `public_html` via File Manager
2. cPanel -> MySQL Databases: create a database + user, note the credentials
3. phpMyAdmin -> import `database/schema.sql` (use the database cPanel created)
4. Copy `config.sample.php` to `config.php` and fill in the cPanel DB
   credentials and your Anthropic API key
5. Enable AutoSSL for HTTPS

Either way, first thing after it's live: sign in as admin, change the seeded
passwords, and set the institution's real emergency contacts in System
settings.

## License

MIT — see [LICENSE](LICENSE).
