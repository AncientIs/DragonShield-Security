<?php
// DragonShield Security - CSRF Token Management
function generate_csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}
function validate_csrf_token(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (empty($_SESSION['csrf_token']) || empty($token)) { return false; }
    if (time() - ($_SESSION['csrf_token_time'] ?? 0) > 1800) {
        unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        return false;
    }
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
    return $valid;
}
function csrf_token_field(): string {
    $token = generate_csrf_token();
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
