<?php
// DragonShield Security - Contact Form Handler
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/csrf_token.php';
require_once __DIR__ . '/includes/validation.php';
require_once __DIR__ . '/includes/rate_limiter.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$response = ['success' => false, 'errors' => [], 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    http_response_code(405);
    echo json_encode($response); exit;
}

// CSRF validation
$csrf = filter_input(INPUT_POST, '_csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!validate_csrf_token($csrf ?? '')) {
    $response['message'] = 'Invalid security token. Please refresh and try again.';
    http_response_code(403);
    echo json_encode($response); exit;
}

// Rate limiting
if (is_rate_limited('contact')) {
    $response['message'] = 'Too many submissions. Please try again later.';
    http_response_code(429);
    echo json_encode($response); exit;
}

// Honeypot check (hidden field)
if (!empty($_POST['website_url'])) {
    $response['message'] = 'Submission rejected.';
    http_response_code(400);
    echo json_encode($response); exit;
}

// Validate all fields
$nameResult = validate_name(filter_input(INPUT_POST, 'full-name', FILTER_DEFAULT) ?? '');
if (!$nameResult['valid']) { $response['errors']['full-name'] = $nameResult['error']; }

$emailRaw = filter_input(INPUT_POST, 'email', FILTER_DEFAULT) ?? '';
$email = sanitize_email($emailRaw);
if ($email === false) { $response['errors']['email'] = 'Please enter a valid email address.'; }

$companyResult = validate_company(filter_input(INPUT_POST, 'company', FILTER_DEFAULT) ?? '');
if (!$companyResult['valid']) { $response['errors']['company'] = $companyResult['error']; }

$phoneRaw = filter_input(INPUT_POST, 'phone', FILTER_DEFAULT) ?? '';
if (!empty($phoneRaw)) {
    $phone = sanitize_phone($phoneRaw);
    if ($phone === false) { $response['errors']['phone'] = 'Please enter a valid phone number.'; }
} else { $phone = ''; }

$serviceResult = validate_service(filter_input(INPUT_POST, 'service-interest', FILTER_DEFAULT) ?? '');
if (!$serviceResult['valid']) { $response['errors']['service-interest'] = $serviceResult['error']; }

$messageResult = validate_message(filter_input(INPUT_POST, 'message', FILTER_DEFAULT) ?? '');
if (!$messageResult['valid']) { $response['errors']['message'] = $messageResult['error']; }

// Spam detection
if (detect_spam([$nameResult['value'], $emailRaw, $companyResult['value'], $messageResult['value']])) {
    $response['message'] = 'Submission flagged as spam.';
    http_response_code(400);
    echo json_encode($response); exit;
}

// If validation errors exist
if (!empty($response['errors'])) {
    $response['message'] = 'Please correct the errors below.';
    http_response_code(422);
    echo json_encode($response); exit;
}

// Send email
$to = ADMIN_EMAIL;
$subject = '[DragonShield] New Contact: ' . $serviceResult['value'];
$body = "New contact form submission:\n\n";
$body .= "Name: " . $nameResult['value'] . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Company: " . $companyResult['value'] . "\n";
$body .= "Phone: " . $phone . "\n";
$body .= "Service Interest: " . $serviceResult['value'] . "\n";
$body .= "Message:\n" . $messageResult['value'] . "\n";
$body .= "\n---\nIP: " . get_client_ip() . "\nTime: " . date('Y-m-d H:i:s T');

$headers = "From: " . NOREPLY_EMAIL . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: DragonShield-PHP/" . phpversion();

$sent = @mail($to, $subject, $body, $headers);

// Log submission
$logData = [
    'timestamp' => date('c'),
    'ip' => get_client_ip(),
    'name' => $nameResult['value'],
    'email' => $email,
    'company' => $companyResult['value'],
    'service' => $serviceResult['value'],
    'email_sent' => $sent
];
error_log('[DragonShield Contact] ' . json_encode($logData));

$response['success'] = true;
$response['message'] = 'Thank you! Your message has been sent. We will respond within 24 hours.';
http_response_code(200);
echo json_encode($response);
