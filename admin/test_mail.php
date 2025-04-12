<?php
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'whitehall.sarl@gmail.com';
    $mail->Password = 'jyotwsjivtaozdax';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('whitehall.sarl@gmail.com', 'White Hall');
    $mail->addAddress('whitehall.sarl@gmail.com');
    $mail->Subject = 'sarosepay@gmail.com';
    $mail->Body = 'Ceci est un test depuis White Hall.';

    $mail->send();
    echo 'Message envoyé !';
} catch (Exception $e) {
    echo "Erreur d'envoi : " . $mail->ErrorInfo;
}