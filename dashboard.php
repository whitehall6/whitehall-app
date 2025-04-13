<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les infos utilisateur
$stmt = $conn->prepare("SELECT nom, solde FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$nom = $user['nom'];
$solde = $user['solde'] ?? 0;
$stmt->close();

// Retraits pour graphique
$retraits_data = [];
$result = $conn->query("SELECT DATE(date_transaction) as date, SUM(montant) as total FROM retraits GROUP BY DATE(date_transaction)");
while ($row = $result->fetch_assoc()) {
    $retraits_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - White Hall</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Calibri, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #002147;
            color: white;
            padding: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        .header img {
            width: 50px;
            margin-right: 10px;
        }

        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: 250px;
            height: calc(100vh - 60px);
            background-color: #0a1931;
            color: white;
            overflow-y: auto;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }

        .content {
            margin-left: 250px;
            margin-top: 60px;
            padding: 20px;
        }

        .dashboard-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 300px;
            margin-bottom: 20px;
        }

        .btn-retrait {
            background-color: #f39c12;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn-retrait:hover {
            background-color: #e67e22;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="https://b20a966ebd5972ee8787ba1fbdbfa6b7.serveo.net/White-Hall/images/logos.jpeg" alt="Logo White Hall" class="logo">
    <span>White Hall</span>
</div>

<div class="sidebar"> 
    <a href="dashboard.php">🏠 Accueil</a>
    <a href="mes_formations.php">📚 Mes formations</a>
    <a href="vendre.php">💼 Vendeur</a>
    <a href="historique.php">💰 Historique des transactions</a>
    <a href="support.php">📞 Support</a>
    <a href="logout.php">🚪 Déconnexion</a>
</div>

<div class="content">
    <h2>Bienvenue, <?php echo htmlspecialchars($nom); ?> !</h2>

    <div class="dashboard-card">
        <h3>💰 Solde vendeur</h3>
        <p><strong><?php echo number_format($solde, 0, ',', ' '); ?> XOF</strong></p>
        <a href="retrait.php" class="btn-retrait">Retirer mes gains</a>
    </div>

    <div class="dashboard-card">
        <h3>📈 Évolution des retraits</h3>
        <canvas id="retraitChart"></canvas>
    </div>

    <div class="dashboard-card">
        <h3>📥 Télécharger les contacts</h3>
        <a href="export_contacts.php" class="btn-retrait" style="background-color: #2980b9;">Télécharger en CSV</a>
    </div>
</div>

<script>
    var ctx = document.getElementById('retraitChart').getContext('2d');
    var retraitChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [<?php foreach ($retraits_data as $data) echo '"' . $data['date'] . '",'; ?>],
            datasets: [{
                label: 'Retraits XOF',
                data: [<?php foreach ($retraits_data as $data) echo $data['total'] . ','; ?>],
                borderColor: 'blue',
                fill: false
            }]
        }
    });
</script>

</body>
</html>