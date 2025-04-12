<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Assurez-vous que PHPMailer est bien installé

include 'config/db.php'; // Connexion à la base de données
session_start();

$message = ""; // Message d'erreur ou de succès

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Vérifier la connexion à la base de données
    if (!$conn) {
        die("Erreur de connexion à la base de données");
    }

    // Vérifier si l'utilisateur existe
    $stmt = $conn->prepare("SELECT id, password FROM utilisateurs WHERE email = ?");
    if ($stmt === false) {
        die("Erreur de préparation de la requête SQL : " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;

            // Générer un code de vérification
            $code = rand(100000, 999999);
            $expiration = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // Enregistrer le code dans la base de données
            $stmt = $conn->prepare("UPDATE utilisateurs SET code_verification = ?, expiration = ? WHERE email = ?");
            $stmt->bind_param("sss", $code, $expiration, $email);
            $stmt->execute();

            // Envoi du code par email avec PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Configuration SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'whitehall.sarl@gmail.com'; // Remplace par ton adresse Gmail
                $mail->Password   = 'jyotwsjivtaozdax'; // Mot de passe d'application Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Expéditeur et destinataire
                $mail->setFrom('whitehall.sarl@gmail.com', 'White Hall');
                $mail->addAddress($email);

                // Contenu du mail
                $mail->isHTML(true);
                $mail->Subject = 'Votre code de vérification - White Hall';
                $mail->Body    = "<p>Bonjour,</p><p>Votre code de vérification est : <b>$code</b></p><p>Ce code est valable 10 minutes.</p><p>Merci,<br>L'équipe White Hall.</p>";

                // Envoi du mail
                $mail->send();

                // Rediriger vers la page de vérification
                header("Location: verification.php");
                exit();
            } catch (Exception $e) {
                die("Erreur lors de l'envoi du mail : " . $mail->ErrorInfo);
            }
        } else {
            $message = "<p class='message error'>Mot de passe incorrect.</p>";
        }
    } else {
        $message = "<p class='message error'>Aucun compte trouvé avec cet email.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }
        .logo {
            width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        h2 {
            color: #333;
        }
        input {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px auto;
            display: block;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            width: 50%;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 10px;
            padding: 8px;
            border-radius: 5px;
            font-size: 14px;
        }
        .message.error {
            color: red;
            background-color: #ffe5e5;
        }
        .message.success {
            color: green;
            background-color: #e5ffe5;
        }
        .register-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Affichage du logo -->
        <img src="http://localhost/White-Hall/images/logo.jpeg" alt="Logo White Hall" class="logo">

        <h2>Connexion</h2>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($message)) { echo $message; } ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>

        <footer style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
            <p>Établissement Légal au Bénin: N° DE REGISTRE DU COMMERCE    COTONOU N° RB/ABC/25 A 121076  WHITE HALL n'est pas un site d'investissement.</p>
        </footer>
        <div class="register-link">
            Pas encore inscrit ? <a href="register.php">Créez un compte ici</a>
        </div>
    </div>
</body>
</html>