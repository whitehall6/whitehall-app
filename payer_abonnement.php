<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'] ?? null;

    if ($transaction_id === "success") {
        // Enregistrement dans la table achats
        $stmt = $conn->prepare("INSERT INTO achats (user_id, date_achat) VALUES (?, NOW())");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        // Récupérer le vendeur lié à l'utilisateur
        $checkVendeur = $conn->prepare("SELECT vendeur_id FROM utilisateurs WHERE id = ?");
        $checkVendeur->bind_param("i", $user_id);
        $checkVendeur->execute();
        $result = $checkVendeur->get_result();
        $row = $result->fetch_assoc();
        $vendeur_id = $row['vendeur_id'] ?? null;

        if (!empty($vendeur_id)) {
            $gain = 2000;
            $montant = 5000;

            // Vérifie s’il existe une commission en attente
            $check = $conn->prepare("SELECT id FROM ventes_vendeurs WHERE vendeur_id = ? AND acheteur_id = ? AND status = 'en attente'");
            $check->bind_param("ii", $vendeur_id, $user_id);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                // Mettre à jour la commission en "payé"
                $commission = $result->fetch_assoc();
                $update = $conn->prepare("UPDATE ventes_vendeurs SET status = 'payé' WHERE id = ?");
                $update->bind_param("i", $commission['id']);
                $update->execute();

                // Créditer le gain au solde du vendeur
                $updateSolde = $conn->prepare("UPDATE utilisateurs SET solde = solde + ? WHERE id = ?");
                $updateSolde->bind_param("ii", $gain, $vendeur_id);
                $updateSolde->execute();
            }
        }

        header("Location: dashboard.php");
        exit();
    } else {
        $message = "Le paiement a échoué. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement de l'abonnement</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            background-color: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
            transition: transform 0.3s ease;
        }
        .container:hover {
            transform: scale(1.03);
        }
        img {
            max-width: 120px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
        }
        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
        }
        .btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px 25px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <img src="/White-Hall/images/logo.jpeg" alt="White Hall">
    <h1>Paiement de l'Abonnement</h1>
    <p>Payez <strong>5000 FCFA</strong> pour débloquer toutes les formations et commencer à toucher des commissions sur vos ventes.</p>

    <?php if (!empty($message)) : ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="transaction_id" value="success"> <!-- Simulation d’un paiement -->
        <button type="submit" class="btn">Payer maintenant</button>
    </form>

    <p style="margin-top: 15px;">Besoin d'aide ? Contactez notre support.</p>
</div>
</body>
</html>