<?php
require '../config/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=retraits_valides.csv');

$output = fopen("php://output", "w");
fputcsv($output, ['Nom', 'Pays', 'Réseau', 'Numéro', 'Montant (XOF)', 'Date']);

// Requête des retraits validés
$query = $conn->query("SELECT u.nom, r.pays, r.reseau, r.numero, r.montant, r.date_transaction
                       FROM retraits r
                       JOIN utilisateurs u ON u.id = r.user_id
                       WHERE r.status = 'payé'
                       ORDER BY r.date_transaction DESC");

while ($row = $query->fetch_assoc()) {
    fputcsv($output, [$row['nom'], $row['pays'], $row['reseau'], $row['numero'], $row['montant'], $row['date_transaction']]);
}

fclose($output);
exit();
?>