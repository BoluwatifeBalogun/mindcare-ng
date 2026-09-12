<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = db()->query('SELECT name, value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
    json_out(['settings' => $rows]);
}
$b = body();
$allowed = ['crisis_line', 'centre', 'follow_up_hours'];
$st = db()->prepare('INSERT INTO settings (name, value) VALUES (?,?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
foreach ($allowed as $k) if (isset($b[$k])) $st->execute([$k, mb_substr(trim((string)$b[$k]), 0, 255)]);
json_out(['ok' => true]);
