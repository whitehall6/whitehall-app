<?php
session_start();

// Vérifier si l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - White Hall</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        img {
            max-width: 100px;
            margin-bottom: 15px;
        }
        h1 {
            font-size: 22px;
            color: #333;
        }
        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #0056b3;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 10px 0;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #004494;
        }
        .btn-retour {
            display: inline-block;
            background-color: #0056b3;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-family: Calibri, sans-serif;
            font-size: 14px;
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .btn-retour:hover {
        background-color: #004494;
        }
    </style>
</head>
<body>
<a href="dashboard.php" class="btn-retour">← Retour à l'Acceuil</a>

<div class="container">
    <img src="/White-Hall/images/logo.jpeg" alt="White Hall" class="logo">
    <h1>Support Client</h1>
    <p>Besoin d'aide ? Contactez-nous via :</p>
    
    <a href="mailto:serviceclientwhitehall@gmail.com" class="btn">Envoyer un email</a>
    <a href="https://wa.me/22950041001" target="_blank" class="btn">WhatsApp</a>
</div>

</body>
</html>