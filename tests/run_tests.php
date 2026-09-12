<?php
/* Pure-function test suite — mirrors the validated JS suites.
   Run: php tests/run_tests.php  (no DB or network needed) */
require_once __DIR__ . '/../includes/risk.php';
$pass = 0; $fail = 0;
function t(string $n, $got, $want): void {
    global $pass, $fail;
    $ok = $got === $want;
    $ok ? $pass++ : $fail++;
    echo ($ok ? 'PASS' : 'FAIL') . "  {$n} -> " . json_encode($got) . ($ok ? '' : ' (want ' . json_encode($want) . ')') . "\n";
}
echo "--- pre_scan ---\n";
t('crisis phrase', pre_scan('I want to end my life'), 'high');
t('better off without me', pre_scan('everyone would be better off without me'), 'high');
t('hurting myself', pre_scan('I have been hurting myself'), 'high');
t('hopeless -> moderate', pre_scan('I feel so hopeless'), 'moderate');
t("can't cope -> moderate", pre_scan("I honestly can't cope anymore"), 'moderate');
t('exam stress -> low', pre_scan("I'm stressed about my exams"), 'low');
echo "--- resolve_risk (item 8) ---\n";
t('both high -> crisis', resolve_risk('high', 'high'), ['risk' => 'high', 'action' => 'crisis']);
t('keywords high, model low -> review', resolve_risk('high', 'low'), ['risk' => 'moderate', 'action' => 'review']);
t('keywords high, model moderate -> review', resolve_risk('high', 'moderate'), ['risk' => 'moderate', 'action' => 'review']);
t('model-only high -> crisis', resolve_risk('low', 'high'), ['risk' => 'high', 'action' => 'crisis']);
t('API down + keywords high -> crisis fail-safe', resolve_risk('high', null), ['risk' => 'high', 'action' => 'crisis']);
t('moderate agreement -> soft', resolve_risk('moderate', 'moderate'), ['risk' => 'moderate', 'action' => 'soft']);
t('calm -> none', resolve_risk('low', 'low'), ['risk' => 'low', 'action' => 'none']);
echo "--- scoring bands ---\n";
t('PHQ 4', phq_band(4), 'Minimal');  t('PHQ 5', phq_band(5), 'Mild');
t('PHQ 10', phq_band(10), 'Moderate'); t('PHQ 15', phq_band(15), 'Moderately severe'); t('PHQ 20', phq_band(20), 'Severe');
t('GAD 4', gad_band(4), 'Minimal'); t('GAD 5', gad_band(5), 'Mild'); t('GAD 10', gad_band(10), 'Moderate'); t('GAD 15', gad_band(15), 'Severe');
echo "--- risk tag parsing (as in ai.php) ---\n";
$raw = "I'm here with you.\n<risk>moderate</risk>";
preg_match('/<risk>(low|moderate|high)<\/risk>/i', $raw, $m);
t('tag parsed', strtolower($m[1]), 'moderate');
t('tag stripped', trim(preg_replace('/<risk>(low|moderate|high)<\/risk>/i', '', $raw)), "I'm here with you.");
echo "\n{$pass} passed, {$fail} failed\n";
exit($fail ? 1 : 0);
