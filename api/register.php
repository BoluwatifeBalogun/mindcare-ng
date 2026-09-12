<?php
require_once __DIR__ . '/../includes/auth.php';
$b = body();
foreach (['name','matric','dept','level','password'] as $f) {
    if (empty(trim($b[$f] ?? ''))) json_out(['error' => "Missing field: {$f}"], 422);
}
if (strlen($b['password']) < 8) json_out(['error' => 'Password must be at least 8 characters'], 422);
try {
    $st = db()->prepare('INSERT INTO users (name, username, password_hash, role, dept, level) VALUES (?,?,?,?,?,?)');
    $st->execute([trim($b['name']), trim($b['matric']), password_hash($b['password'], PASSWORD_BCRYPT), 'student', trim($b['dept']), trim($b['level'])]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') json_out(['error' => 'That matric number is already registered. Try signing in.'], 409);
    throw $e;
}
$u = ['id' => (int)db()->lastInsertId(), 'name' => trim($b['name']), 'role' => 'student', 'dept' => trim($b['dept']), 'level' => trim($b['level']), 'username' => trim($b['matric'])];
$_SESSION['user'] = $u;
json_out(['ok' => true, 'user' => $u]);
