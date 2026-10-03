<?php
/* MindCare NG — configuration.
   Local/XAMPP: copy this file to config.php and fill in the values below.
   Docker/Railway/Render: leave as-is; values are read from environment variables. */
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'mindcare');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');   // default XAMPP root has no password
define('DB_PORT', getenv('DB_PORT') ?: '3306');
/* AI provider for Amara's replies: 'anthropic' (Claude) or 'gemini' (Google).
   Gemini keys are free at aistudio.google.com, useful where card payments are hard.
   The risk pipeline is identical on both; only the reply generator changes. */
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: 'anthropic');
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');            // console.anthropic.com
define('ANTHROPIC_MODEL', getenv('ANTHROPIC_MODEL') ?: 'claude-sonnet-4-6');
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');                  // aistudio.google.com (free tier)
define('GEMINI_MODEL', getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash');
define('APP_NAME', 'MindCare NG');
