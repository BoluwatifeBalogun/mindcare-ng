<?php
require_once __DIR__ . '/../includes/auth.php';
$u = require_role('student', 'counsellor');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($u['role'] === 'student') {
        $st = db()->prepare('SELECT a.id, a.when_text, a.status, a.reason, a.is_follow_up, c.name AS counsellor
                             FROM appointments a LEFT JOIN users c ON c.id = a.counsellor_id
                             WHERE a.student_id = ? ORDER BY a.id DESC');
        $st->execute([$u['id']]);
    } else {
        $st = db()->query('SELECT a.id, a.when_text, a.status, a.reason, a.is_follow_up, s.name AS student, c.name AS counsellor
                           FROM appointments a JOIN users s ON s.id = a.student_id
                           LEFT JOIN users c ON c.id = a.counsellor_id ORDER BY a.id DESC LIMIT 100');
    }
    json_out(['appointments' => $st->fetchAll()]);
}
$b = body();
if (($b['action'] ?? '') === 'book' && $u['role'] === 'student') {
    $cid = (int)($b['counsellor_id'] ?? 0);
    $when = mb_substr(trim($b['when'] ?? ''), 0, 80);
    if (!$cid || $when === '') json_out(['error' => 'Pick a counsellor and a time slot'], 422);
    db()->prepare('INSERT INTO appointments (student_id, counsellor_id, when_text, reason) VALUES (?,?,?,?)')
       ->execute([$u['id'], $cid, $when, mb_substr(trim($b['reason'] ?? 'General session'), 0, 255)]);
    json_out(['ok' => true]);
}
if (($b['action'] ?? '') === 'confirm' && $u['role'] === 'counsellor') {
    db()->prepare('UPDATE appointments SET status = "confirmed", counsellor_id = COALESCE(counsellor_id, ?) WHERE id = ?')
       ->execute([$u['id'], (int)($b['id'] ?? 0)]);
    json_out(['ok' => true]);
}
json_out(['error' => 'Unknown action'], 422);
