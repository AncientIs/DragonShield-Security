<?php
// DragonShield Security - CSRF Token Endpoint for AJAX forms
session_start();
require_once __DIR__ . '/includes/csrf_token.php';
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']); exit;
}
$token = generate_csrf_token();
echo json_encode(['csrf_token' => $token]);
