<?php
$host = "localhost"; // Adresse du serveur MySQL
$user = "root"; // Nom d'utilisateur MySQL (par défaut sur XAMPP)
$password = ""; // Mot de passe MySQL (vide par défaut sur XAMPP)
$database = "whitehall"; // Nom de ta base de données

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}
?>