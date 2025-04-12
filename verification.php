<?php
session_start();
require 'config/db.php'; // Fichier de connexion à la base de données

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $code_saisi = $_POST['code'];

    // Vérifier si le code existe et est toujours valide
    $stmt = $conn->prepare("SELECT code_verification, expiration, abonnement FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($code_enregistre, $expiration, $abonnement);
        $stmt->fetch();

        if ($code_saisi == $code_enregistre) {
            // Vérifier l'expiration du code
            if (strtotime($expiration) > time()) {
                // Connexion réussie, mise à jour du code pour qu'il ne soit plus réutilisable
                $stmt = $conn->prepare("UPDATE utilisateurs SET code_verification = NULL, expiration = NULL WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();

                // Vérifier l'abonnement et rediriger en conséquence
                if ($abonnement) {
                    header("Location: dashboard.php");
                } else {
                    header("Location: abonnement.php");
                }
                exit();
            } else {
                $erreur = "Le code a expiré. Veuillez vous reconnecter.";
            }
        } else {
            $erreur = "Code incorrect. Veuillez réessayer.";
        }
    } else {
        $erreur = "Erreur. Veuillez vous reconnecter.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        input {
            padding: 10px;
            margin: 10px 0;
            width: 80%;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background: #0056b3;
        }
        .erreur {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Vérification</h2>
        <p>Un code de vérification a été envoyé à votre adresse email,consultez vos spams si vous n'avez rien reçu. Veuillez le saisir ci-dessous :</p>
        <?php if (isset($erreur)) echo "<p class='erreur'>$erreur</p>"; ?>
        <form method="POST">
            <input type="text" name="code" placeholder="Entrez votre code" required>
            <button type="submit">Vérifier</button>
        </form>
    </div>
</body>
</html>