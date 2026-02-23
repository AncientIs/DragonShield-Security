<?php
// DragonShield Security - Newsletter Subscription Handler
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf_token.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/rate_limiter.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    http_response_code(405);
    echo json_encode($response); exit;
}

$csrf = filter_input(INPUT_POST, '_csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!validate_csrf_token($csrf ?? '')) {
    $response['message'] = 'Invalid security token.';
    http_response_code(403);
    echo json_encode($response); exit;
}

if (is_rate_limited('newsletter', 3)) {
    $response['message'] = 'Too many attempts. Try again later.';
    http_response_code(429);
    echo json_encode($response); exit;
}

$emailRaw = filter_input(INPUT_POST, 'email', FILTER_DEFAULT) ?? '';
$email = sanitize_email($emailRaw);
if ($email === false) {
    $response['message'] = 'Please enter a valid email address.';
    http_response_code(422);
    echo json_encode($response); exit;
}

// Log newsletter subscription
error_log('[DragonShield Newsletter] ' . json_encode(['time'=>date('c'),'ip'=>get_client_ip(),'email'=>$email]));

// Send confirmation email
$subject = 'Welcome to DragonShield Security Newsletter';
$body = "Thank you for subscribing to DragonShield Security updates!\n\n";
$body .= "You will receive cybersecurity insights and threat intelligence.\n";
$body .= "To unsubscribe, reply to this email with 'UNSUBSCRIBE'.\n";
$headers = "From: " . NOREPLY_EMAIL . "\r\n";
@mail($email, $subject, $body, $headers);

// Notify admin
@mail(ADMIN_EMAIL, '[DragonShield] New Newsletter Subscriber', "New subscriber: " . $email . "\nIP: " . get_client_ip(), "From: " . NOREPLY_EMAIL);

$response['success'] = true;
$response['message'] = 'Subscribed successfully! Check your email for confirmation.';
echo json_encode($response);
