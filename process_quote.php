<?php
// DragonShield Security - Quote Request Handler
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

$csrf = filter_input(INPUT_POST, '_csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!validate_csrf_token($csrf ?? '')) {
    $response['message'] = 'Invalid security token. Please refresh and try again.';
    http_response_code(403);
    echo json_encode($response); exit;
}

if (is_rate_limited('quote')) {
    $response['message'] = 'Too many submissions. Please try again later.';
    http_response_code(429);
    echo json_encode($response); exit;
}

if (!empty($_POST['website_url'])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Rejected.']); exit;
}

// Validate fields
$nameR = validate_name(filter_input(INPUT_POST, 'full-name', FILTER_DEFAULT) ?? '');
if (!$nameR['valid']) { $response['errors']['full-name'] = $nameR['error']; }

$emailRaw = filter_input(INPUT_POST, 'email', FILTER_DEFAULT) ?? '';
$email = sanitize_email($emailRaw);
if ($email === false) { $response['errors']['email'] = 'Valid email required.'; }

$compR = validate_company(filter_input(INPUT_POST, 'company', FILTER_DEFAULT) ?? '');
if (!$compR['valid']) { $response['errors']['company'] = $compR['error']; }

$phoneRaw = filter_input(INPUT_POST, 'phone', FILTER_DEFAULT) ?? '';
$phone = !empty($phoneRaw) ? sanitize_phone($phoneRaw) : '';
if ($phone === false) { $response['errors']['phone'] = 'Valid phone required.'; }

$svcR = validate_service(filter_input(INPUT_POST, 'service-interest', FILTER_DEFAULT) ?? '');
if (!$svcR['valid']) { $response['errors']['service-interest'] = $svcR['error']; }

$msgR = validate_message(filter_input(INPUT_POST, 'message', FILTER_DEFAULT) ?? '');
if (!$msgR['valid']) { $response['errors']['message'] = $msgR['error']; }

// Optional: budget and timeline
$budget = sanitize_string(filter_input(INPUT_POST, 'budget', FILTER_DEFAULT) ?? '');
$timeline = sanitize_string(filter_input(INPUT_POST, 'timeline', FILTER_DEFAULT) ?? '');

if (detect_spam([$nameR['value'],$emailRaw,$compR['value'],$msgR['value']])) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Spam detected.']); exit;
}

if (!empty($response['errors'])) {
    $response['message'] = 'Please correct the errors below.';
    http_response_code(422);
    echo json_encode($response); exit;
}

$to = ADMIN_EMAIL;
$subject = '[DragonShield] Quote Request: ' . $svcR['value'];
$body = "New quote request:\n\n";
$body .= "Name: " . $nameR['value'] . "\nEmail: " . $email . "\n";
$body .= "Company: " . $compR['value'] . "\nPhone: " . $phone . "\n";
$body .= "Service: " . $svcR['value'] . "\n";
$body .= "Budget: " . $budget . "\nTimeline: " . $timeline . "\n";
$body .= "Details:\n" . $msgR['value'] . "\n";
$body .= "\n---\nIP: " . get_client_ip() . "\nTime: " . date('Y-m-d H:i:s T');

$headers = "From: " . NOREPLY_EMAIL . "\r\nReply-To: " . $email . "\r\n";
$headers .= "X-Mailer: DragonShield-PHP/" . phpversion();
@mail($to, $subject, $body, $headers);

error_log('[DragonShield Quote] ' . json_encode(['time'=>date('c'),'ip'=>get_client_ip(),'name'=>$nameR['value'],'email'=>$email,'service'=>$svcR['value']]));

$response['success'] = true;
$response['message'] = 'Quote request received! We will send your custom quote within 48 hours.';
echo json_encode($response);
