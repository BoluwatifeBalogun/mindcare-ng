<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}
function require_role(string ...$roles): array {
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not authorised']);
        exit;
    }
    return $u;
}
function require_page_role(string ...$roles): array {
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) { header('Location: index.php'); exit; }
    return $u;
}
function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
function body(): array {
    $raw = file_get_contents('php://input');
    $d = json_decode($raw, true);
    return is_array($d) ? $d : $_POST;
}
