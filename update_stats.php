<?php
include 'config/db.php';
session_start();

$user_id = $_SESSION['user_id'];

// Récupérer le total retiré par l'utilisateur
$stmt = $conn->prepare("SELECT SUM(montant) AS total_retiré FROM retraits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total_retiré_utilisateur = $result->fetch_assoc()['total_retiré'] ?? 0;
$stmt->close();

// Récupérer le total retiré par tous les utilisateurs
$stmt = $conn->prepare("SELECT SUM(montant) AS total_retiré FROM retraits");
$stmt->execute();
$result = $stmt->get_result();
$total_retiré_global = $result->fetch_assoc()['total_retiré'] ?? 0;
$stmt->close();

// Retourner les données en JSON
echo json_encode([
    "total_retiré_utilisateur" => number_format($total_retiré_utilisateur, 2),
    "total_retiré_global" => number_format($total_retiré_global, 2)
]);
?>