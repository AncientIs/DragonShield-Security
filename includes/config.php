<?php
// DragonShield Security - Configuration
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');
define('SITE_NAME', 'DragonShield Security');
define('ADMIN_EMAIL', 'admin@dragonshield-security.com');
define('NOREPLY_EMAIL', 'noreply@dragonshield-security.com');
define('MAX_SUBMISSIONS_PER_HOUR', 5);
define('RATE_LIMIT_DIR', sys_get_temp_dir() . '/dragonshield_ratelimit/');
