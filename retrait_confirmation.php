<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de retrait</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .confirmation-box {
            background-color: white;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            max-width: 400px;
        }

        .confirmation-box h2 {
            color: #003366;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .confirmation-box p {
            color: #333;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .btn-retour {
            background-color: #003366;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
        }

        .btn-retour:hover {
            background-color: #002244;
        }
    </style>
</head>
<body>
    <div class="confirmation-box">
        <h2>Votre retrait est en attente d’approbation</h2>
        <p>Nous traitons votre demande dans les plus brefs délais (entre 3h et 48h).</p>
        <a href="dashboard.php" class="btn-retour">Retour à l’accueil</a>
    </div>
</body>
</html>