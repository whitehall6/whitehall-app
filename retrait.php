<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT nom, solde, password FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = "";
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pays = $_POST['pays'];
    $reseau = $_POST['reseau'];
    $numero = trim($_POST['numero']);
    $montant = intval($_POST['montant']);
    $passeword = $_POST['passeword'];
    $frais = ($montant <= 50000) ? 500 : 1000;
    $montant_net = $montant - $frais;

    if ($montant < 500) {
        $erreur = "Montant minimum pour un retrait : 500 XOF.";
    } elseif ($montant > $user['solde']) {
        $erreur = "Solde insuffisant pour effectuer ce retrait.";
    } elseif (!password_verify($passeword, $user['password'])) {
        $erreur = "Mot de passe incorrect.";
    } else {
        // Insérer la demande de retrait
        $stmt = $conn->prepare("INSERT INTO retraits (user_id, pays, reseau, numero, montant, frais, status, type, date_transaction) VALUES (?, ?, ?, ?, ?, ?, 'en attente', 'retrait', NOW())");
        $stmt->bind_param("isssii", $user_id, $pays, $reseau, $numero, $montant_net, $frais);
        $stmt->execute();

        // Débiter le solde
        $stmt = $conn->prepare("UPDATE utilisateurs SET solde = solde - ? WHERE id = ?");
        $stmt->bind_param("ii", $montant, $user_id);
        $stmt->execute();

        header("Location: retrait_confirmation.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de retrait</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            background: #f4f4f4;
            padding: 40px 10px;
            text-align: center;
        }
        .container {
            background: white;
            padding: 30px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        label {
            text-align: left;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        .password-wrapper {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            cursor: pointer;
        }
        button {
            padding: 12px 25px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .notif-toggle {
            background-color: #003366;
            color: white;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .notif-content {
            display: none;
            background: #ffeeba;
            color: #856404;
            font-weight: bold;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #003366;
            font-weight: bold;
        }
        .msg, .error {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: fadeOut 6s forwards;
        }
        .msg { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        @keyframes fadeOut {
            0% { opacity: 1; }
            85% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
    <script>
        function updateNetworks() {
            const pays = document.getElementById('pays').value;
            const reseau = document.getElementById('reseau');
            reseau.innerHTML = '';
            let options = [];

            if (pays === 'BENIN') options = ['MTN', 'MOOV'];
            if (pays === "COTE D'IVOIRE") options = ['ORANGE CI', 'MTN CI', 'MOOV CI', 'WAVE CI'];

            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt;
                option.text = opt;
                reseau.appendChild(option);
            });
        }

        function toggleNotifications() {
            const content = document.getElementById('notif-content');
            content.style.display = (content.style.display === 'block') ? 'none' : 'block';
        }

        function togglePassword() {
            const input = document.getElementById('mot_de_passe');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        function updateFrais() {
            const montant = document.getElementById('montant').value;
            const frais = (montant <= 50000) ? 500 : 1000;
            if (montant) {
                document.getElementById('frais').value = frais + " XOF";
            } else {
                document.getElementById('frais').value = "";
            }
        }
    </script>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="back-link">← Retour au Dashboard</a>

    <h2>Demande de retrait</h2>

    <div class="notif-toggle" onclick="toggleNotifications()">Afficher les notifications</div>

    <div id="notif-content" class="notif-content">
        Les retraits sont disponibles uniquement au <strong>Bénin</strong> et en <strong>Côte d’Ivoire</strong>.<br><br>
        Frais :<br>
        - 500 XOF pour les montants ≤ 50 000 XOF<br>
        - 1000 XOF pour les montants entre 50 001 et 100 000 XOF<br><br>
        Traitement entre 3h et 48h.
    </div>

    <?php if ($message): ?><div class="msg"><?= $message ?></div><?php endif; ?>
    <?php if ($erreur): ?><div class="error"><?= $erreur ?></div><?php endif; ?>

    <form method="POST">
        <label for="pays">Pays</label>
        <select name="pays" id="pays" onchange="updateNetworks()" required>
            <option value="">Sélectionnez un pays</option>
            <option value="BENIN">Bénin</option>
            <option value="COTE D'IVOIRE">Côte d’Ivoire</option>
        </select>

        <label for="reseau">Réseau</label>
        <select name="reseau" id="reseau" required>
            <option value="">Choisissez un réseau</option>
        </select>

        <label for="numero">Indicatif + Numéro</label>
        <input type="text" name="numero" placeholder="+229XXXXXXXX" required pattern="^\+[0-9]{6,15}$">

        <label for="montant">Montant à retirer (XOF)</label>
        <input type="number" id="montant" name="montant" required min="500" onchange="updateFrais()">

        <label for="frais">Frais (automatique)</label>
        <input type="text" id="frais" readonly>

        <label for="mot_de_passe">Mot de passe</label>
        <div class="password-wrapper">
            <input type="password" id="passeword" name="passeword" required autocomplete="off">
            <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <button type="submit">Lancer le retrait</button>
    </form>
</div>
</body>
</html>