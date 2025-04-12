<?php
include 'config/db.php'; // Connexion à la base de données

// Lire les données envoyées par l’agrégateur
$data = file_get_contents("php://input");
$paymentInfo = json_decode($data, true);

// Vérifier que la réponse contient un ID utilisateur et que le paiement est validé
if (isset($paymentInfo['user_id']) && isset($paymentInfo['status']) && $paymentInfo['status'] === 'success') {
    $user_id = $paymentInfo['user_id'];

    // Vérifier si l'utilisateur n'a pas déjà un abonnement
    $stmt = $conn->prepare("SELECT id FROM achats WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Insérer l'achat dans la table achats
        $stmt = $conn->prepare("INSERT INTO achats (user_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Répondre à l’agrégateur pour confirmer la réception
http_response_code(200);
echo json_encode(["message" => "Paiement confirmé"]);
?>