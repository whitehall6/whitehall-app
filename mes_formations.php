<?php
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Répertoire où se trouvent les formations (fichiers PDF)
$dir = "uploads/";
// Récupérer la liste des fichiers en excluant '.' et '..'
$allFiles = scandir($dir);
$files = array();
foreach ($allFiles as $file) {
    // Exclure les fichiers système et les entrées . et ..
    if ($file === '.' || $file === '..') {
        continue;
    }
    // Exclure desktop.ini en toute casse
    if (strtolower($file) === 'desktop.ini') {
        continue;
    }
    $files[] = $file;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Formations</title>
  <style>
    body {
      font-family: 'Calibri', sans-serif;
      background-color: #1a1a2e;
      color: #fff;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: auto;
      background: #16213e;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }
    h2 {
      text-align: center;
      color: #f4c10f;
    }
    .formation {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      border-bottom: 1px solid #ddd;
    }
    .formation:last-child {
      border-bottom: none;
    }
    .btn-download {
      background-color: #f4c10f;
      color: #16213e;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s ease-in-out;
    }
    .btn-download:hover {
      background-color: #d1a109;
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
    <h2>Mes Formations</h2>
    <?php if (!empty($files)): ?>
      <?php foreach ($files as $file): ?>
        <div class="formation">
          <span><?php echo htmlspecialchars($file); ?></span>
          <a href="<?php echo $dir . urlencode($file); ?>" class="btn-download" download>Télécharger</a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Aucune formation disponible pour le moment.</p>
    <?php endif; ?>
  </div>
</body>
</html>