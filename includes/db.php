<?php
$__cfg = __DIR__ . '/../config.php';
if (!file_exists($__cfg)) {
    http_response_code(500);
    exit('Setup incomplete: copy config.sample.php to config.php and fill in your database credentials and Anthropic API key. See README.md.');
}
require_once $__cfg;
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function get_setting(string $key, string $fallback = ''): string {
    $st = db()->prepare('SELECT value FROM settings WHERE name = ?');
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? $fallback : $v;
}
