<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/db.php'; // Connexion à la base de données
require 'vendor/autoload.php'; // Chargement de PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = ""; // Message d'erreur ou de succès

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $whatsapp = trim($_POST["whatsapp"]);
    $ville = trim($_POST["ville"]);
    $code_parrainage = isset($_POST["parrain_code"]) ? trim($_POST["parrain_code"]) : null;

    // Vérification que les mots de passe correspondent
    if ($password !== $confirm_password) {
        echo "<p style='color:red;'>Les mots de passe ne correspondent pas.</p>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $code_affiliation = strtoupper(substr(md5(time() . $email), 0, 8)); // Génération du code unique
    $vendeur_id = null;

    if (!empty($code_parrainage)) {
        // Vérification si le code de parrainage existe
        $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE code_affiliation = ?");
        $stmt->bind_param("s", $code_parrainage);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $vendeur_id = $row['id'];
        }
        $stmt->close();
    }

    // Vérification de l'email existant
    $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo "<p style='color:red;'>Cet email est déjà utilisé.</p>";
    } else {
        // Insérer le nouvel utilisateur
        $stmt = $conn->prepare("INSERT INTO utilisateurs (nom, email, password, whatsapp, ville, code_affiliation, vendeur_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $nom, $email, $hashed_password, $whatsapp, $ville, $code_affiliation, $vendeur_id);
        if ($stmt->execute()) {
            $nouvel_utilisateur_id = $stmt->insert_id; 
        
            $user_id = $conn->insert_id; // Récupère l’ID du nouvel utilisateur

            $insert_contact = $conn->prepare("INSERT INTO contacts (user_id, nom, whatsapp) VALUES (?, ?, ?)");
            $insert_contact->bind_param("iss", $user_id, $nom, $whatsapp);
            $insert_contact->execute();
            $insert_contact->close();

            if ($vendeur_id) {
                // Enregistrement de la commission avec statut 'pending'
                $commission = 2000;
                $montant_paye = 5000;
            
                // Vérifier qu'aucune commission "pending" n'existe déjà pour cet acheteur/vendeur
                $check = $conn->prepare("SELECT id FROM ventes_vendeurs WHERE vendeur_id = ? AND acheteur_id = ? AND status = 'en attente'");
                $check->bind_param("ii", $vendeur_id, $nouvel_utilisateur_id);
                $check->execute();
                $result = $check->get_result();
            
                if ($result->num_rows === 0) {
                    // Aucun enregistrement existant, on peut l’ajouter
                    $stmt = $conn->prepare("INSERT INTO ventes_vendeurs (vendeur_id, acheteur_id, gain, montant, status) VALUES (?, ?, ?, ?, 'en attente')");
                    $stmt->bind_param("iiii", $vendeur_id, $nouvel_utilisateur_id, $commission, $montant_paye);
                    $stmt->execute();
                    $stmt->close();
                }
            
                $check->close();
            }

            // Envoi du mail de bienvenue
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'whitehall.sarl@gmail.com'; 
            $mail->Password = 'jyotwsjivtaozdax'; 
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('whitehall.sarl@gmail.com', 'WHITE HALL');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenue sur WHITE HALL';
            $mail->Body = "
                <div style='text-align:center; font-family: Arial, sans-serif;'>
                    <img src='images/logo.jpeg' alt='White Hall' class='logo' style='max-width:150px; margin-bottom:20px;'>
                    <h2 style='color:#003366;'>Bienvenue sur WHITE HALL</h2>
                    <p>Bonjour <strong>$nom</strong>,</p>
                    <p>Nous sommes ravis de vous compter parmi nous.</p>
                    <p>Vous pouvez maintenant vous connecter et profiter de nos formations.</p>
                    <p>Merci et à bientôt !</p>
                    <p><strong>L'équipe White Hall</strong></p>
                </div>
            ";
            $mail->send();

            // Redirection vers la page de connexion après inscription réussie
            header("Location: login.php");
            exit();
        } else {
            echo "<p style='color:red;'>Une erreur est survenue lors de l'inscription.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - WHITE HALL</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .success-message {
            width: 50%;
            margin: 10% auto;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            border: 2px solid #007bff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .success-message h3 {
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
        }

        .success-message p {
            color: #333;
            font-size: 18px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
        }
        .logo {
            width: 90px;
            margin: 35px auto 0 auto;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        h2 {
            margin-bottom: 10px;
            font-weight: 600;
        }
        input, select {
            width: 90%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        .checkbox-container a {
            color: #007bff;
            text-decoration: none;
            margin-left: 5px;
        }
        .btn {
            background-color: #0056b3;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #004494;
        }
        .terms label {
            display: flex;
            align-items: center;
            gap: 5px; /* Ajuste cette valeur si nécessaire */
            font-size: 14px;
        }

        .terms input {
            margin: 0;
            width: 16px;
            height: 16px;
        }

        .show-password {
            cursor: pointer;
            color: #007bff;
            font-size: 16px;
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            width: 80%;
        }

        .show-password:hover {
            text-decoration: underline;
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

        .legal-text {
            font-size: 12px;
            margin-top: 20px;
        }
        </style>
</head>
<body>

<div class="container">
    <img src='/White-Hall/images/logo.jpeg' alt='White Hall' class='logo'>
    <h2>Inscription</h2>
    <form action="register.php" method="POST">
        <input type="text" name="nom" placeholder="Nom et prénom" required>
        <input type="email" name="email" placeholder="Email" required>
        
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="password" name="confirm_password" placeholder="Confirmez le mot de passe" required>

        <input type="text" name="whatsapp" placeholder="Indicatif+Numéro WhatsApp" required>
        <input type="text" name="ville" placeholder="Ville" required>
        <input type="text" name="parrain_code" placeholder="Code d'affiliation (optionnel)">

        <label>
           <input type="checkbox" name="conditions" required>
           J’accepte les <a href="conditions_generales.php" target="_blank">conditions générales</a>
        </label>

        <button type="submit" class="btn">S'inscrire</button>
    </form>
</div>

</body>
</html>