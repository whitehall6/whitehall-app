<?php
// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "whitehall");

if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
}

// Récupération des contacts depuis la table "contacts"
$result = $mysqli->query("SELECT nom, whatsapp FROM contacts");

if ($result->num_rows > 0) {
    // En-têtes pour forcer le téléchargement d'un fichier .vcf
    header('Content-Type: text/vcard; charset=utf-8');
    header('Content-Disposition: attachment; filename="contacts_whitehall.vcf"');

    while ($row = $result->fetch_assoc()) {
        $nom = htmlspecialchars($row['nom']);
        $whatsapp = htmlspecialchars($row['whatsapp']);

        echo "BEGIN:VCARD\r\n";
        echo "VERSION:3.0\r\n";
        echo "FN:$nom\r\n";
        echo "TEL;TYPE=CELL:$whatsapp\r\n";
        echo "END:VCARD\r\n";
    }
} else {
    echo "Aucun contact trouvé.";
}

$mysqli->close();
?>