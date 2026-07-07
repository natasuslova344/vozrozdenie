<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешён']);
    exit;
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Неверный формат данных']);
    exit;
}

$errors = [];

$name = trim($data['name'] ?? '');
if ($name === '') {
    $errors[] = 'Укажите имя или название компании';
}

$phone = trim($data['phone'] ?? '');
$phoneDigits = preg_replace('/\D/', '', $phone);
if (strlen($phoneDigits) !== 11 || !in_array($phoneDigits[0], ['7', '8'])) {
    $errors[] = 'Неверный формат телефона';
}

$email = trim($data['email'] ?? '');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Неверный формат email';
}

$message = trim($data['message'] ?? '');
if ($message === '') {
    $errors[] = 'Укажите, что требуется';
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode('; ', $errors)]);
    exit;
}

try {
    $db = new Database();
    $leadId = $db->saveLead([
        'name'    => $name,
        'phone'   => $phone,
        'email'   => $email ?: null,
        'message' => $message,
        'source'  => trim($data['source'] ?? 'Сайт'),
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (Exception $e) {
    // Ошибку базы данных пишем в лог, но не показываем пользователю
    error_log('[Vozrozhdenie] DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка сервера. Позвоните нам: +7 (932)-328-41-92']);
    exit;
}

$emailBody  = "Новая заявка с сайта ООО «Возрождение»\n";
$emailBody .= "========================================\n";
$emailBody .= "Имя / Компания : {$name}\n";
$emailBody .= "Телефон        : {$phone}\n";
$emailBody .= "E-mail         : " . ($email ?: '—') . "\n";
$emailBody .= "Сообщение      :\n{$message}\n";
$emailBody .= "========================================\n";
$emailBody .= "Источник       : " . ($data['source'] ?? 'Сайт') . "\n";
$emailBody .= "ID в базе      : #{$leadId}\n";
$emailBody .= "Дата/время     : " . date('d.m.Y H:i:s') . "\n";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_USER, SITE_NAME);
    $mail->addAddress(NOTIFY_EMAIL);
    if ($email) {
        $mail->addReplyTo($email, $name);
    }

    $mail->Subject = 'Новая заявка #' . $leadId . ' от ' . $name;
    $mail->Body    = $emailBody;
    $mail->send();
} catch (\Throwable $e) {
    // Если письмо не ушло — не показываем ошибку пользователю,
    // заявка уже сохранена в базе данных, просто пишем в лог
    error_log('[Vozrozhdenie] Mail error: ' . $e->getMessage());
}

echo json_encode(['success' => true, 'message' => 'Заявка принята', 'id' => $leadId]);
