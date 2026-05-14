<?php
// Cuenta que recibira las solicitudes enviadas desde el sitio.
$recipient = 'holiverosa@yahoo.com.mx';
$subject = 'Nueva solicitud desde el sitio web de GEP';

function clean_field($value) {
  return trim(str_replace(array("\r", "\n"), ' ', $value ?? ''));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.html#contacto');
  exit;
}

$name = clean_field($_POST['Nombre'] ?? '');
$email = filter_var($_POST['Correo'] ?? '', FILTER_VALIDATE_EMAIL);
$message = trim($_POST['Mensaje'] ?? '');

if ($name === '' || !$email || $message === '') {
  header('Location: index.html?status=error#contacto');
  exit;
}

$body = "Nueva solicitud desde el sitio web de GEP\n\n";
$body .= "Nombre: {$name}\n";
$body .= "Correo: {$email}\n";
$body .= "Mensaje:\n{$message}\n";

$host = preg_replace('/[^a-zA-Z0-9.-]/', '', $_SERVER['HTTP_HOST'] ?? 'gepmex.com.mx');
$host = preg_replace('/^www\./', '', $host);
$sender = 'contacto@gepmex.com.mx';

$headers = "From: GEP Web <{$sender}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Return-Path: {$sender}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail($recipient, $subject, $body, $headers, "-f {$sender}");
$status = $sent ? 'ok' : 'error';
$logLine = sprintf(
  "[%s] status=%s recipient=%s sender=%s name=%s email=%s ip=%s\n",
  date('c'),
  $status,
  $recipient,
  $sender,
  $name,
  $email,
  $_SERVER['REMOTE_ADDR'] ?? 'unknown'
);
$logPath = dirname(__DIR__) . '/gep-contact-log.txt';

if (!is_writable(dirname($logPath))) {
  $logPath = __DIR__ . '/contact-log.txt';
}

file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);

if (!$sent) {
  error_log('GEP contact form: mail() failed for ' . $recipient);
}

header("Location: index.html?status={$status}#contacto");
exit;
?>
