<?php
/* MindCare NG — configuration.
   Local/XAMPP: copy this file to config.php and fill in the values below.
   Docker/Railway/Render: leave as-is; values are read from environment variables. */
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'mindcare');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');   // default XAMPP root has no password
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');            // console.anthropic.com
define('ANTHROPIC_MODEL', getenv('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-6');
define('APP_NAME', 'MindCare NG');
