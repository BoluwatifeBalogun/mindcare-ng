<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$rows = db()->query('SELECT id, name, username, role, dept, level, title, DATE_FORMAT(created_at, "%e %b %Y") AS joined FROM users ORDER BY role, name')->fetchAll();
json_out(['users' => $rows]);
