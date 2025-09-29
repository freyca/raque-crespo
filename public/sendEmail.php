<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'PHPMailer.php';

function sendEmail(
    string $email_address,
    string $name,
    string $message
): void {
    $mail = new PHPMailer(true);

    try {
        //Recipients
        $mail->setFrom('info@raquelcrespocastro.com', $name);
        $mail->addAddress('info@raquelcrespocastro.com', 'Raquel Crespo');
        $mail->addReplyTo($email_address, $name);

        //Content
        $mail->isHTML(false);
        $mail->Subject = $name . ' contactou dende o formulario web';
        $mail->Body    = $message;

        $mail->send();

        jsonResponse(['success' => 'Enviado correctamente.'], 200);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo], 500);
    }
}

function validateInput(string $name, string $email, string $message): void
{
    if (empty($name) || empty($email) || empty($message)) {
        jsonResponse(['error' => 'Campo inválido.'], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['error' => 'Introduce un email válido.'], 400);
    }
}

function validateHoneyPotFields(string $honey_phone, string $honey_name): void
{
    if (!empty($honey_phone) || !empty($honey_name)) {
        jsonResponse(['error' => 'Spam detectado.'], 400);
    }
}

function jsonResponse($data, $status = 200): void
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function main(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Method Not Allowed'], 405);
    }

    $name = $_POST['input_name'] ?? '';
    $email = $_POST['input_email'] ?? '';
    $message = $_POST['input_text'] ?? '';

    $honey_phone = $_POST['phone-number	'] ?? '';
    $honey_name = $_POST['second-name	'] ?? '';

    // Avoid form abusing
    validateHoneyPotFields($honey_phone, $honey_name);

    // Basic validation
    validateInput($name, $email, $message);

    sendEmail($email, $name, $message);
}

main();
