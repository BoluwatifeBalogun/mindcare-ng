<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

/* POST = create a staff account (admin-only, by design: there is deliberately
   no public staff registration, since counsellors can read student referrals). */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b = body();
    $name = trim($b['name'] ?? '');
    $username = strtolower(trim($b['username'] ?? ''));
    $password = $b['password'] ?? '';
    $role = $b['role'] ?? 'counsellor';
    $title = trim($b['title'] ?? '');

    if ($name === '' || $username === '') json_out(['error' => 'Name and username are required'], 422);
    if (!preg_match('/^[a-z0-9._-]{3,40}$/', $username)) json_out(['error' => 'Username: 3-40 chars, letters/numbers/dots/dashes'], 422);
    if (strlen($password) < 8) json_out(['error' => 'Password must be at least 8 characters'], 422);
    if (!in_array($role, ['counsellor', 'admin'], true)) json_out(['error' => 'Role must be counsellor or admin'], 422);

    $st = db()->prepare('SELECT id FROM users WHERE username = ?');
    $st->execute([$username]);
    if ($st->fetch()) json_out(['error' => 'That username is taken'], 409);

    db()->prepare('INSERT INTO users (name, username, password_hash, role, title) VALUES (?, ?, ?, ?, ?)')
        ->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role, $title ?: null]);
    json_out(['ok' => true, 'created' => $username]);
}

$rows = db()->query('SELECT id, name, username, role, dept, level, title, DATE_FORMAT(created_at, "%e %b %Y") AS joined FROM users ORDER BY role, name')->fetchAll();
json_out(['users' => $rows]);
