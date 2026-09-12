<?php
require_once __DIR__ . '/../includes/auth.php';
$b = body();
$st = db()->prepare('SELECT * FROM users WHERE username = ?');
$st->execute([trim($b['username'] ?? '')]);
$row = $st->fetch();
if (!$row || !password_verify($b['password'] ?? '', $row['password_hash'])) {
    json_out(['error' => 'Matric number/username or password is incorrect'], 401);
}
$_SESSION['user'] = ['id' => (int)$row['id'], 'name' => $row['name'], 'role' => $row['role'], 'dept' => $row['dept'], 'level' => $row['level'], 'username' => $row['username']];
json_out(['ok' => true, 'user' => $_SESSION['user']]);
