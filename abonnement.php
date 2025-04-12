<?php
session_start();
include 'config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'utilisateur a déjà payé son abonnement
$stmt = $conn->prepare("SELECT id FROM achats WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // L'utilisateur est déjà abonné, le rediriger directement au dashboard
    header("Location: dashboard.php");
    exit();
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 16px;
            background-color: #f8f8f8;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 600px;
            background: #ffffff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        img {
            max-width: 100px;
            margin-bottom: 20px;
        }
        
        h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 24px;
        }

        p {
            color: #555;
            font-size: 16px;
            margin-bottom: 25px;
        }

        .abonnement {
            background-color: #f2f2f2;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .abonnement h3 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 22px;
        }

        .abonnement ul {
            list-style: none;
            padding: 0;
            text-align: left;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .abonnement ul li {
            margin-bottom: 10px;
            color: #666;
        }

        h4 {
            color: #333;
            font-size: 18px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 50px;
            font-size: 18px;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .abonnement ul li, .abonnement h4 {
            text-align: left;
            padding-left: 10px;
        }

        .abonnement ul li:before {
            content: "✔️";
            padding-right: 10px;
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="container">
    <img src='/White-Hall/images/logo.jpeg' alt='White Hall' class='logo'>
    <h2>Achat de Formation WHITE-HALL</h2>
    <p>Pour 5000 FCFA, accédez à toutes les formations disponibles. Après avoir maîtrisé une formation, devenez vendeur et percevez une commission sur chaque vente réalisée.</p>
    
    <div class="abonnement">
        <h3>5000 FCFA pour accéder à toutes les Formations</h3>
        <ul>
            <li>- Accès à une fiche de contacts</li>
            <li>- Devenir vendeur après maîtrise d'une formation</li>
            <li>- Accès complet à toutes les formations</li>
            <li>- Possibilité de gagner des commissions sur chaque vente</li>
            <li>- Questionnaire obligatoire pour devenir vendeur</li>
            <li>- Support client par email</li>
            <li>- Accès à une liste de contacts WhatsApp</li>
        </ul>
        
        <h4>Formations incluses :</h4>
        <ul>
            <li>- Marketing Digital</li>
            <li>- Développement Web</li>
            <li>- Entrepreneuriat</li>
            <li>- Réussir sur TikTok</li>
            <li>- Facebook Ads</li>
        </ul>

        <a href="payer_abonnement.php" class="btn">Aller au paiement</a>
    </div>
</div>

</body>
</html>