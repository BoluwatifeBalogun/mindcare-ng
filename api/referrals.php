<?php
require_once __DIR__ . '/../includes/auth.php';
$u = require_role('counsellor');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $st = db()->query('SELECT r.id, r.source, r.risk, r.summary, r.context_json, r.status,
                              DATE_FORMAT(r.created_at, "%e %b, %H:%i") AS time, s.name AS student, s.id AS student_id
                       FROM referrals r JOIN users s ON s.id = r.student_id
                       ORDER BY r.status = "open" DESC, r.id DESC LIMIT 100');
    json_out(['referrals' => $st->fetchAll()]);
}
$b = body();
if (($b['action'] ?? '') === 'take') {
    // Take the case + auto-schedule the follow-up (final flowchart step)
    $id = (int)($b['id'] ?? 0);
    $st = db()->prepare('SELECT student_id FROM referrals WHERE id = ? AND status = "open"');
    $st->execute([$id]);
    $sid = $st->fetchColumn();
    if (!$sid) json_out(['error' => 'Referral not found or already taken'], 404);
    $hours = get_setting('follow_up_hours', '48');
    $pdo = db();
    $pdo->prepare('UPDATE referrals SET status = "taken", taken_by = ? WHERE id = ?')->execute([$u['id'], $id]);
    $pdo->prepare('INSERT INTO appointments (student_id, counsellor_id, when_text, reason, is_follow_up)
                   VALUES (?, ?, ?, "Referral follow-up support", 1)')
        ->execute([$sid, $u['id'], "Within {$hours}h (auto-scheduled)"]);
    json_out(['ok' => true]);
}
if (($b['action'] ?? '') === 'reply') {
    // Counsellor note lands inside the student's chat, clearly attributed (Fig 3.2: "respond to students")
    $sid = (int)($b['student_id'] ?? 0);
    $text = mb_substr(trim($b['text'] ?? ''), 0, 2000);
    if (!$sid || $text === '') json_out(['error' => 'Empty note'], 422);
    db()->prepare('INSERT INTO chat_messages (student_id, role, from_name, text) VALUES (?, "counsellor", ?, ?)')
       ->execute([$sid, $u['name'], $text]);
    json_out(['ok' => true]);
}
json_out(['error' => 'Unknown action'], 422);
