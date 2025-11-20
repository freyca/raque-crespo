<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'PHPMailer.php';

class EmailSender
{
    private string $name;
    private string $email;
    private string $message;
    private string $honey_phone;
    private string $honey_name;

    function __construct(array $post_data)
    {
        $this->name = $post_data['input_name'] ?? '';
        $this->email = $post_data['input_email'] ?? '';
        $this->message = $post_data['input_text'] ?? '';

        $this->honey_phone = $post_data['phone-number'] ?? '';
        $this->honey_name = $post_data['second-name	'] ?? '';
    }

    public function sendEmail(): void
    {
        $this->validateInput();
        $this->validateHoneyPotFields();

        $mail = new PHPMailer(true);

        try {
            //Recipients
            $mail->setFrom('info@raquelcrespocastro.com', $this->name);
            $mail->addAddress('raquel@guillermopresaabogados.com ', 'Raquel Crespo');
            $mail->addReplyTo($this->email, $this->name);

            //Content
            $mail->isHTML(false);
            $mail->Subject = $this->name . ' contactou dende o formulario web';
            $mail->Body    = $this->message;

            $mail->send();

            Response::jsonResponse(['success' => 'Enviado correctamente.'], 200);
        } catch (Exception $e) {
            Response::jsonResponse(['error' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo], 500);
        }
    }

    private function validateInput(): void
    {
        if (empty($this->name) || empty($this->email) || empty($this->message)) {
            Response::jsonResponse(['error' => 'Campo inválido.'], 400);
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            Response::jsonResponse(['error' => 'Introduce un email válido.'], 400);
        }
    }

    private function validateHoneyPotFields(): void
    {
        if (!empty($this->honey_phone) || !empty($this->honey_name)) {
            Response::jsonResponse(['error' => 'Spam detectado.'], 400);
        }
    }
}

class Response
{
    static public function jsonResponse($data, $status = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}

function main(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::jsonResponse(['error' => 'Method Not Allowed'], 405);
    }

    $emailSender = new EmailSender($_POST);
    $emailSender->sendEmail();
}

main();
