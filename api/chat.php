<?php
/* The counselling pipeline (Figure 3.3), server-side.
   Intake -> pre-scan -> context -> inference -> item-8 resolve -> route -> persist. */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/risk.php';
require_once __DIR__ . '/../includes/ml_classifier.php';
require_once __DIR__ . '/../includes/ai.php';

$u = require_role('student');

if (($_GET['action'] ?? '') === 'history') {
    $st = db()->prepare('SELECT role, from_name, text, risk, created_at FROM chat_messages WHERE student_id = ? ORDER BY id ASC LIMIT 200');
    $st->execute([$u['id']]);
    json_out(['messages' => $st->fetchAll()]);
}

$b = body();
$text = trim($b['message'] ?? '');
if ($text === '') json_out(['error' => 'Empty message'], 422);
if (mb_strlen($text) > 4000) json_out(['error' => 'Message too long'], 422);

/* Local risk screen: keyword rules + the trained classifier (risk_model.json)
   vote; worst verdict wins. Runs before and independently of the LLM call,
   so escalation still works when the API is down. */
$screen = local_scan($text);
$pre = $screen['risk'];

// Build model context: prior turns (counsellor notes excluded from model input)
$st = db()->prepare("SELECT role, text FROM chat_messages WHERE student_id = ? AND role IN ('user','assistant') ORDER BY id DESC LIMIT 20");
$st->execute([$u['id']]);
$prior = array_reverse($st->fetchAll());
$messages = array_map(fn($m) => ['role' => $m['role'], 'content' => $m['text']], $prior);
$messages[] = ['role' => 'user', 'content' => $text];

// Latest assessment context note
$st = db()->prepare('SELECT tool, score, band FROM assessments WHERE student_id = ? ORDER BY id DESC LIMIT 2');
$st->execute([$u['id']]);
$parts = array_map(fn($a) => "{$a['tool']} {$a['score']} ({$a['band']})", $st->fetchAll());
$ctx = $parts ? 'latest self-assessment: ' . implode(', ', $parts) : null;

$model_risk = null;
try {
    $r = ask_amara($messages, $ctx);
    $reply = $r['text'];
    $model_risk = $r['risk'];
} catch (Throwable $e) {
    $reply = "I'm having trouble connecting right now, but I'm still here with you. While I reconnect, try one slow breath in for 4 and out for 6. If things feel urgent, please reach the campus counsellor or call " . get_setting('crisis_line', '112') . ".";
}

$res = resolve_risk($pre, $model_risk);
$risk = $res['risk']; $action = $res['action'];

$pdo = db();
$pdo->prepare('INSERT INTO chat_messages (student_id, role, text) VALUES (?, "user", ?)')->execute([$u['id'], $text]);
$pdo->prepare('INSERT INTO chat_messages (student_id, role, text, risk) VALUES (?, "assistant", ?, ?)')
    ->execute([$u['id'], $reply, $action === 'crisis' ? 'high' : null]);

if ($action !== 'none') {
    $context = array_slice(array_merge($messages, [['role' => 'assistant', 'content' => $reply]]), -4);
    $flags = 'screens: keywords=' . $screen['kw'] . ', classifier=' . ($screen['ml'] ?? 'n/a') . ', model=' . ($model_risk ?? 'unreachable');
    $summaries = [
        'crisis' => 'Crisis-level risk confirmed in a live session. Immediate human follow-up recommended. (' . $flags . ')',
        'review' => 'Local risk screen flagged possible crisis language; Amara read it as non-crisis in context. Queued for human review. (' . $flags . ')',
        'soft'   => 'Persistent distress signals detected in conversation. Suggested follow-up within ' . get_setting('follow_up_hours', '48') . ' hours. (' . $flags . ')',
    ];
    $pdo->prepare('INSERT INTO referrals (student_id, source, risk, summary, context_json) VALUES (?, "AI chat session", ?, ?, ?)')
        ->execute([$u['id'], $action === 'crisis' ? 'high' : 'moderate', $summaries[$action], json_encode($context)]);
}

json_out([
    'reply' => $reply,
    'action' => $action,
    'crisis' => $action === 'crisis' ? ['line' => get_setting('crisis_line', '112'), 'centre' => get_setting('centre', '')] : null,
]);
