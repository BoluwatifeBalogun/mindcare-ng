<?php
require_once __DIR__ . '/../includes/auth.php';
$u = require_role('student');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $st = db()->prepare('SELECT entry_date, value, note FROM mood_entries WHERE student_id = ? ORDER BY entry_date DESC LIMIT 14');
    $st->execute([$u['id']]);
    json_out(['moods' => array_reverse($st->fetchAll())]);
}
$b = body();
$v = (int)($b['value'] ?? 0);
if ($v < 1 || $v > 5) json_out(['error' => 'Mood value must be 1 to 5'], 422);
$note = mb_substr(trim($b['note'] ?? ''), 0, 255);
db()->prepare('INSERT INTO mood_entries (student_id, entry_date, value, note) VALUES (?, CURDATE(), ?, ?)
               ON DUPLICATE KEY UPDATE value = VALUES(value), note = VALUES(note)')
   ->execute([$u['id'], $v, $note]);
json_out(['ok' => true]);
