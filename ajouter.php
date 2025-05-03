<?php
session_start();

// Vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    exit("Erreur : utilisateur non connecté.");
}

$mysqli = new mysqli("localhost", "root", "", "medicament");

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Récupération des données du formulaire
$nom = $_POST['nom'] ?? '';
$posologie = $_POST['posologie'] ?? '';
$heure = $_POST['heure'] ?? '';
$frequence = $_POST['frequence'] ?? '';
$user_id = $_SESSION['user_id']; // identifiant de l'utilisateur connecté

// Vérifie que tous les champs sont remplis
if (empty($nom) || empty($posologie) || empty($heure) || empty($frequence)) {
    exit("Erreur : tous les champs sont requis.");
}

// Insertion du médicament lié à l'utilisateur
$sql = "INSERT INTO rappels (nom, posologie, heure, frequence, user_id) VALUES (?, ?, ?, ?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ssssi", $nom, $posologie, $heure, $frequence, $user_id);

if ($stmt->execute()) {
    header("Location: http://localhost/medicament/medicament.php?success=1");

} else {
    echo "Erreur lors de l'enregistrement : " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
