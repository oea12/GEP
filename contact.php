<?php
// Cuenta que recibira las solicitudes enviadas desde el sitio.
$recipient = 'holiveros@yahoo.com.mx';
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

if ($name === '' || !$email) {
  header('Location: index.html?status=error#contacto');
  exit;
}

$body = "Nueva solicitud desde el sitio web de GEP\n\n";
$body .= "Nombre: {$name}\n";
$body .= "Correo: {$email}\n";
$body .= "Mensaje:\n{$message}\n";

$host = preg_replace('/[^a-zA-Z0-9.-]/', '', $_SERVER['HTTP_HOST'] ?? 'gepgestoria.com');
$host = preg_replace('/^www\./', '', $host);
$sender = "no-reply@{$host}";

$headers = "From: GEP Web <{$sender}>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$sent = mail($recipient, $subject, $body, $headers);
$status = $sent ? 'ok' : 'error';

header("Location: index.html?status={$status}#contacto");
exit;
?>
