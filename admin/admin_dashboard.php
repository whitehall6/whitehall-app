<?php
session_start();
include '../config/db.php';
require '../vendor/autoload.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    $stmt = $conn->prepare("SELECT montant, frais, user_id FROM retraits WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $retrait = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT email FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $retrait['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $email = $user['email'];

    if ($action === 'valider') {
        $stmt = $conn->prepare("UPDATE retraits SET status = 'payé' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
        $subject = "Retrait approuvé";
        $body = "Votre retrait de {$retrait['montant']} XOF a été validé avec succès. Fonds transféré sur le numéro fournit lors de la demande de retrait";
    } elseif ($action === 'rejeter') {
        $montant_total = $retrait['montant'] + $retrait['frais'];
        $stmt1 = $conn->prepare("UPDATE retraits SET status = 'rejeté' WHERE id = ?");
        $stmt2 = $conn->prepare("UPDATE utilisateurs SET solde = solde + ? WHERE id = ?");
        if ($stmt1 && $stmt2) {
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            $stmt2->bind_param("ii", $montant_total, $retrait['user_id']);
            $stmt2->execute();
        }
        $subject = "Retrait rejeté";
        $body = "Votre demande de retrait de {$montant_total} XOF a été rejetée. Le montant a été entièrement re-crédité.";
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'whitehall.sarl@gmail.com';
        $mail->Password = 'jyotwsjivtaozdax';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('whitehall.sarl@gmail.com', 'White Hall');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "<p>Bonjour,</p><p>{$body}</p><p>Merci,<br>L'équipe White Hall</p>";
        $mail->send();
    } catch (Exception $e) {}

    header("Location: admin_dashboard.php");
    exit();
}

$pays = $_GET['pays'] ?? '';
$reseau = $_GET['reseau'] ?? '';
$statut = $_GET['statut'] ?? '';

$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;

$where = "1";
$params = [];
$types = "";

if ($pays) {
    $where .= " AND r.pays = ?";
    $params[] = $pays;
    $types .= "s";
}
if ($reseau) {
    $where .= " AND r.reseau = ?";
    $params[] = $reseau;
    $types .= "s";
}
if ($statut) {
    $where .= " AND r.status = ?";
    $params[] = $statut;
    $types .= "s";
}

$query = "SELECT r.*, u.nom FROM retraits r JOIN utilisateurs u ON u.id = r.user_id WHERE $where ORDER BY r.date_transaction DESC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$retraits = $stmt->get_result();

function getCount($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM retraits WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_row()[0];
}
$en_attente = getCount($conn, 'en attente');
$payes = getCount($conn, 'payé');
$rejetes = getCount($conn, 'rejeté');

$total_stmt = $conn->query("SELECT SUM(montant + frais) AS total FROM retraits WHERE status = 'en attente'");
$total_en_attente = number_format($total_stmt->fetch_assoc()['total'] ?? 0, 0, ',', ' ');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Retraits</title>
    <style>
        body { font-family: Calibri; background: #f0f0f0; padding: 20px; }
        h2 { color: #002147; text-align: center; }

        .stats { display: flex; justify-content: space-around; margin-bottom: 20px; flex-wrap: wrap; }
        .stat-box {
            background: #002147; color: white; padding: 20px;
            border-radius: 10px; width: 30%; text-align: center;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1); margin-bottom: 15px;
        }

        .filters {
            margin-bottom: 20px;
            text-align: center;
        }

        .filters select, .filters button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-family: Calibri;
            margin: 5px;
        }

        .filters button {
            background-color: #3399ff;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .filters button:hover {
            background-color: #287dc1;
        }

        .extras {
            text-align: center;
            margin: 20px 0;
        }

        .extras a {
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            margin: 5px;
            font-weight: bold;
        }

        .actualiser { background: #3498db; color: white; }
        .export-valides { background: #2ecc71; color: white; }
        .export-rejetes { background: #e74c3c; color: white; }

        .retrait-card {
            background: #fff;
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .actions a {
            text-decoration: none;
            padding: 8px 14px;
            color: white;
            margin-right: 10px;
            border-radius: 5px;
        }

        .valider { background: #2ecc71; }
        .rejeter { background: #e67e22; }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 6px 12px;
            text-decoration: none;
            background: #002147;
            color: white;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<a href="logout_admin.php" style="float:right; background:#c0392b; padding:10px 15px; color:white; text-decoration:none; border-radius:5px;">Déconnexion</a>
<h2>Interface Administrateur WHITE HALL - Gestion des retraits</h2>

<div class="stats">
    <div class="stat-box"><strong><?= $en_attente ?></strong><br>EN ATTENTE</div>
    <div class="stat-box"><strong><?= $payes ?></strong><br>VALIDES</div>
    <div class="stat-box"><strong><?= $rejetes ?></strong><br>REJETES</div>
</div>

<form method="get" class="filters">
    <select name="pays">
        <option value="">Filtrer par pays</option>
        <option value="BENIN" <?= $pays === "BENIN" ? "selected" : "" ?>>Bénin</option>
        <option value="COTE D'IVOIRE" <?= $pays === "COTE D'IVOIRE" ? "selected" : "" ?>>Côte d’Ivoire</option>
    </select>

    <select name="reseau">
        <option value="">Filtrer par réseau</option>
        <option value="MTN" <?= $reseau === "MTN" ? "selected" : "" ?>>MTN</option>
        <option value="MOOV" <?= $reseau === "MOOV" ? "selected" : "" ?>>MOOV</option>
        <option value="ORANGE CI" <?= $reseau === "ORANGE CI" ? "selected" : "" ?>>ORANGE CI</option>
        <option value="WAVE CI" <?= $reseau === "WAVE CI" ? "selected" : "" ?>>WAVE CI</option>
        <option value="MTN CI" <?= $reseau === "MTN CI" ? "selected" : "" ?>>MTN CI</option>
        <option value="MOOV CI" <?= $reseau === "MOOV CI" ? "selected" : "" ?>>MOOV CI</option>
    </select>

    <select name="statut">
        <option value="">Filtrer par statut</option>
        <option value="en attente" <?= $statut === "en attente" ? "selected" : "" ?>>En attente</option>
        <option value="payé" <?= $statut === "payé" ? "selected" : "" ?>>Payé</option>
        <option value="rejeté" <?= $statut === "rejeté" ? "selected" : "" ?>>Rejeté</option>
    </select>

    <button type="submit">Appliquer</button>
</form>

<div class="extras">
    <a href="admin_dashboard.php" class="actualiser">Actualiser</a>
    <a href="export_retraits_valides.php?type=payé" class="export-valides">Exporter Validés</a>
    <a href="export_retraits_rejetes.php?type=rejeté" class="export-rejetes">Exporter Rejetés</a>
</div>

<div class="stats" style="justify-content: center;">
    <div class="stat-box" style="background: #ffcc00; color: #000;">
        <strong><?= $total_en_attente ?> XOF</strong><br>Montant total en attente
    </div>
</div>

<?php while ($r = $retraits->fetch_assoc()): ?>
    <div class="retrait-card">
        <strong><?= htmlspecialchars($r['nom']) ?></strong> - <?= number_format($r['montant'], 0, ',', ' ') ?> XOF<br>
        <small><?= htmlspecialchars($r['pays']) ?> | <?= htmlspecialchars($r['reseau']) ?> | <?= htmlspecialchars($r['numero']) ?> | <?= $r['date_transaction'] ?></small><br><br>
        Statut : <strong><?= strtoupper($r['status']) ?></strong><br><br>

        <?php if ($r['status'] === 'en attente'): ?>
            <div class="actions">
                <a href="?action=valider&id=<?= $r['id'] ?>" class="valider" onclick="return confirm('Confirmer la validation de ce retrait ?')">Valider</a>
                <a href="?action=rejeter&id=<?= $r['id'] ?>" class="rejeter" onclick="return confirm('Confirmer le rejet de ce retrait ?')">Rejeter</a>
            </div>
        <?php endif; ?>
    </div>
<?php endwhile; ?>

<div class="pagination">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <a href="?page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

</body>
</html>