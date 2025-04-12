<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Commissions payées
$commissions = $conn->prepare("SELECT gain AS montant, 'commission' AS type, date_commission AS date_transaction FROM ventes_vendeurs WHERE vendeur_id = ? AND status = 'payé'");
$commissions->bind_param("i", $user_id);
$commissions->execute();
$result_comm = $commissions->get_result();
$liste_commissions = $result_comm->fetch_all(MYSQLI_ASSOC);
$commissions->close();

// Retraits utilisateur
$retraits = $conn->prepare("SELECT id, montant, 'retrait' AS type, date_transaction, status FROM retraits WHERE user_id = ? ORDER BY date_transaction DESC");
$retraits->bind_param("i", $user_id);
$retraits->execute();
$result_ret = $retraits->get_result();
$liste_retraits = $result_ret->fetch_all(MYSQLI_ASSOC);
$retraits->close();

// Fusion & tri
$transactions = array_merge($liste_commissions, $liste_retraits);
usort($transactions, function ($a, $b) {
    return strtotime($b['date_transaction']) - strtotime($a['date_transaction']);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des transactions - White Hall</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            font-size: 14px;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #002147;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 20px;
        }
        .transaction {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }
        .transaction:last-child {
            border-bottom: none;
        }
        .statut-attente {
            color: #3399ff;
            font-weight: bold;
        }
        .statut-paye {
            color: #2ecc71;
            font-weight: bold;
        }
        .statut-rejete {
            color: #e74c3c;
            font-weight: bold;
        }
        .message {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .date {
            font-size: 12px;
            color: #888;
        }
        .btn-retour {
            display: inline-block;
            background-color: #002147;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .btn-retour:hover {
            background-color: #001730;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn-retour">← Retour à l'accueil</a>
    <h2>Historique des transactions</h2>

    <?php if (count($transactions) > 0): ?>
        <?php foreach ($transactions as $trans): ?>
            <div class="transaction">
                <?php if ($trans['type'] === 'commission'): ?>
                    <div class="message">
                        Vous avez reçu une commission sur vente de <strong><?php echo number_format($trans['montant'], 0, ',', ' '); ?> XOF</strong> déjà créditée sur votre solde.
                    </div>
                    <div class="date"><?php echo date('d/m/Y à H:i', strtotime($trans['date_transaction'])); ?></div>
                <?php elseif ($trans['type'] === 'retrait'): ?>
                    <?php if ($trans['status'] === 'en attente'): ?>
                        <div class="message">
                            Demande Nº<?php echo $trans['id']; ?> pour retirer des fonds d’un montant de <strong><?php echo number_format($trans['montant'], 0, ',', ' '); ?> XOF</strong>
                            <span class="statut-attente">En attente</span>
                        </div>
                    <?php elseif ($trans['status'] === 'payé'): ?>
                        <div class="message">
                            Un virement électronique d’un montant de <strong><?php echo number_format($trans['montant'], 0, ',', ' '); ?> XOF</strong> a été réalisé par « Paiements mobiles »
                            <span class="statut-paye">Payé</span>
                        </div>
                    <?php elseif ($trans['status'] === 'rejeté'): ?>
                        <div class="message">
                            Votre demande de retrait de <strong><?php echo number_format($trans['montant'], 0, ',', ' '); ?> XOF</strong> a été rejetée. Le montant a été re-crédité.
                            <span class="statut-rejete">Rejeté</span>
                        </div>
                    <?php endif; ?>
                    <div class="date"><?php echo date('d/m/Y à H:i', strtotime($trans['date_transaction'])); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune transaction</p>
    <?php endif; ?>
</div>

</body>
</html>