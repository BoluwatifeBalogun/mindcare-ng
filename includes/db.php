<?php
$__cfg = __DIR__ . '/../config.php';
if (!file_exists($__cfg)) {
    http_response_code(500);
    exit('Setup incomplete: copy config.sample.php to config.php and fill in your database credentials and Anthropic API key. See README.md.');
}
require_once $__cfg;
ini_set('display_errors', '0');   // errors must never leak HTML into JSON responses
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 8,
            ]);
        } catch (PDOException $e) {
            error_log('DB connect failed: ' . $e->getMessage());
            header('Content-Type: application/json', true, 500);
            exit(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()
                . ' — check the DB_HOST/DB_PORT/DB_NAME/DB_USER/DB_PASS environment variables and that the schema was imported.']));
        }
    }
    return $pdo;
}
function get_setting(string $key, string $fallback = ''): string {
    $st = db()->prepare('SELECT value FROM settings WHERE name = ?');
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? $fallback : $v;
}
