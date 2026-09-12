<?php
/* Server-side scoring: the browser sends raw answers; escalation rules run here. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/risk.php';
$u = require_role('student');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $st = db()->prepare('SELECT tool, score, band, DATE_FORMAT(created_at, "%e %b") AS date FROM assessments WHERE student_id = ? ORDER BY id DESC LIMIT 20');
    $st->execute([$u['id']]);
    json_out(['assessments' => $st->fetchAll()]);
}
$b = body();
$tool = $b['tool'] ?? '';
$answers = $b['answers'] ?? [];
$n = $tool === 'phq' ? 9 : ($tool === 'gad' ? 7 : 0);
if (!$n || !is_array($answers) || count($answers) !== $n) json_out(['error' => 'Invalid submission'], 422);
foreach ($answers as $a) if (!is_int($a) && !ctype_digit((string)$a) || (int)$a < 0 || (int)$a > 3) json_out(['error' => 'Answers must be 0 to 3'], 422);
$answers = array_map('intval', $answers);
$score = array_sum($answers);
$band = $tool === 'phq' ? phq_band($score) : gad_band($score);
$item9 = $tool === 'phq' ? $answers[8] : 0;
$toolName = $tool === 'phq' ? 'PHQ-9' : 'GAD-7';

db()->prepare('INSERT INTO assessments (student_id, tool, score, band, item9) VALUES (?,?,?,?,?)')
   ->execute([$u['id'], $toolName, $score, $band, $item9]);

$flagged = $item9 > 0;
if ($flagged || $score >= 15) {
    $summary = $flagged
        ? 'Item 9 answered positively on PHQ-9. Priority outreach recommended.'
        : "Score of {$score} ({$band}). Follow-up recommended.";
    db()->prepare('INSERT INTO referrals (student_id, source, risk, summary) VALUES (?,?,?,?)')
       ->execute([$u['id'], "{$toolName} self-assessment", $flagged ? 'high' : 'moderate', $summary]);
}
json_out(['score' => $score, 'band' => $band, 'flagged' => $flagged, 'refer' => $flagged || $score >= 10]);
