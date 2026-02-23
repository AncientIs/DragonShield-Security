<?php
// DragonShield Security - Rate Limiter
require_once __DIR__ . '/config.php';
function is_rate_limited(string $action, int $max = 0, int $window = 3600): bool {
    if ($max === 0) { $max = MAX_SUBMISSIONS_PER_HOUR; }
    $ip = get_client_ip();
    $dir = RATE_LIMIT_DIR;
    if (!is_dir($dir)) { mkdir($dir, 0700, true); }
    $file = $dir . md5($ip . '_' . $action) . '.json';
    $attempts = [];
    if (file_exists($file)) {
        $data = file_get_contents($file);
        $attempts = json_decode($data, true) ?: [];
    }
    $now = time();
    $attempts = array_filter($attempts, function($ts) use ($now, $window) {
        return ($now - $ts) < $window;
    });
    if (count($attempts) >= $max) { return true; }
    $attempts[] = $now;
    file_put_contents($file, json_encode(array_values($attempts)), LOCK_EX);
    return false;
}
function get_client_ip(): string {
    $headers = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            $ip = $_SERVER[$h];
            if (strpos($ip, ',') !== false) { $ip = trim(explode(',', $ip)[0]); }
            if (filter_var($ip, FILTER_VALIDATE_IP)) { return $ip; }
        }
    }
    return '0.0.0.0';
}
