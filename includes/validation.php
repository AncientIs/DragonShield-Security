<?php
// DragonShield Security - Input Validation & Sanitization
function sanitize_string(string $input): string {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}
function sanitize_email(string $email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) { return false; }
    if (strlen($email) > 254) { return false; }
    return $email;
}
function sanitize_phone(string $phone) {
    $phone = trim($phone);
    if (!preg_match('/^[+]?[0-9 ()\-]{7,20}$/', $phone)) { return false; }
    return htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
}
function validate_name(string $name, int $maxLen = 100): array {
    $name = sanitize_string($name);
    if (empty($name)) { return ['valid'=>false,'value'=>'','error'=>'Name is required.']; }
    if (strlen($name) > $maxLen) { return ['valid'=>false,'value'=>$name,'error'=>"Name must be {$maxLen} chars or less."]; }
    return ['valid'=>true,'value'=>$name,'error'=>''];
}
function validate_company(string $co, int $maxLen = 200): array {
    $co = sanitize_string($co);
    if (empty($co)) { return ['valid'=>false,'value'=>'','error'=>'Company name is required.']; }
    if (strlen($co) > $maxLen) { return ['valid'=>false,'value'=>$co,'error'=>"Company must be {$maxLen} chars or less."]; }
    return ['valid'=>true,'value'=>$co,'error'=>''];
}
function validate_message(string $msg, int $maxLen = 2000): array {
    $msg = trim($msg);
    $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
    if (empty($msg)) { return ['valid'=>false,'value'=>'','error'=>'Message is required.']; }
    if (strlen($msg) > $maxLen) { return ['valid'=>false,'value'=>$msg,'error'=>"Message must be {$maxLen} chars or less."]; }
    return ['valid'=>true,'value'=>$msg,'error'=>''];
}
function validate_service(string $svc): array {
    $allowed = ['network-penetration','web-application','mobile-security','social-engineering','cloud-security','red-team','compliance','incident-response','other'];
    $svc = sanitize_string($svc);
    if (empty($svc)) { return ['valid'=>false,'value'=>'','error'=>'Please select a service.']; }
    if (!in_array($svc, $allowed, true)) { return ['valid'=>false,'value'=>$svc,'error'=>'Invalid service selection.']; }
    return ['valid'=>true,'value'=>$svc,'error'=>''];
}
function detect_spam(array $fields): bool {
    foreach ($fields as $val) {
        if (!is_string($val)) continue;
        if (preg_match('/(https?:\/\/.*){3,}/i', $val)) return true;
        if (preg_match('/\[url=/i', $val)) return true;
        if (preg_match('/<a\s+href/i', $val)) return true;
        if (preg_match('/viagra|casino|lottery|prize|winner|click here/i', $val)) return true;
    }
    return false;
}
