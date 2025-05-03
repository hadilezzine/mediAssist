<?php
$servername = "localhost";
$username = "root";  // Par défaut dans XAMPP
$password = "";      // Mot de passe vide par défaut
$dbname = "medicament";  // Assure-toi que la base s’appelle bien "medicament"

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
