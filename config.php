<?php
$conn = new mysqli("localhost", "root", "", "whitehall");

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer doit être installé via Composer

// Fonction d'envoi d'email
function sendEmail($toEmail, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP (Gmail)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'whitehall.sarl@gmail.com'; // Remplace par ton email Gmail
        $mail->Password = 'jyotwsjivtaozdax'; // Remplace par ton mot de passe d'application Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Expéditeur et destinataire
        $mail->setFrom('whitehall.sarl@gmail.com', 'White Hall');
        $mail->addAddress($toEmail);

        // Contenu de l'email
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Envoi de l'email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>