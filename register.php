<?php
$mysqli = new mysqli("localhost", "root", "", "mediassist");

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

$prenom = $_POST['prenom'] ?? '';
$nom = $_POST['nom'] ?? '';
$email = $_POST['email'] ?? '';
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$confirmation = $_POST['confirmation'] ?? '';

if ($mot_de_passe !== $confirmation) {
    exit("Les mots de passe ne correspondent pas.");
}

$hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users (prenom, nom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $prenom, $nom, $email, $hash);

if ($stmt->execute()) {
    header("Location: login.html?inscription=success");
    exit;
} else {
    echo "Erreur : " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
