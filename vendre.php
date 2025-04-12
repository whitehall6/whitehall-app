<?php
session_start();
require_once "config.php"; // Connexion à la base de données

// Vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupération du code de parrainage de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT code_affiliation FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Vérification que le code existe
$code = $user['code_affiliation'] ?? "Non défini";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendre des formations</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            text-align: center;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #2c3e50;
        }
        p {
            font-size: 18px;
            color: #34495e;
        }
        .code-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #e3e3e3;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }
        .code {
            font-weight: bold;
            flex-grow: 1;
            text-align: center;
        }
        .copy-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            margin-left: 10px;
        }
        .copy-btn:hover {
            background: #0056b3;
        }
    </style>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert("Copié : " + text);
            }, function(err) {
                alert("Erreur lors de la copie.");
            });
        }
    </script>
</head>
<body>

<div class="container">
    <h2>Vendez des formations</h2>
    <p>Partagez votre code et gagnez <strong>40% de commission</strong> sur chaque vente !</p>

    <div class="code-container">
        <span class="code"><?php echo $code; ?></span>
        <button class="copy-btn" onclick="copyToClipboard('<?php echo $code; ?>')">Copier</button>
    </div>

    <a href="dashboard.php" class="copy-btn" style="margin-top: 20px; text-decoration: none; display: inline-block;">Retour au Dashboard</a>
</div>

</body>
</html>