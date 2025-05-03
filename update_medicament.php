<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "medicament");
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Récupérer et valider les données du formulaire
$id = $_POST['id'] ?? null;
$nom = $_POST['nom'] ?? '';
$posologie = $_POST['posologie'] ?? '';
$heure = $_POST['heure'] ?? '';
$frequence = $_POST['frequence'] ?? '';
$user_id = $_SESSION['user_id'];

if (!$id || empty($nom) || empty($posologie) || empty($heure) || empty($frequence)) {
    exit("❌ Erreur : tous les champs sont requis.");
}

// Mise à jour du médicament
$sql = "UPDATE rappels SET nom = ?, posologie = ?, heure = ?, frequence = ? WHERE id = ? AND user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ssssii", $nom, $posologie, $heure, $frequence, $id, $user_id);

if ($stmt->execute()) {
    header("Location: medicament.php?success=1");
    exit;
} else {
    echo "❌ Erreur lors de la mise à jour : " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
