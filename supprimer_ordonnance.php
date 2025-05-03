<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "mediassist");
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id'] ?? 0);

// Supprimer seulement si l'ordonnance appartient à l'utilisateur
$stmt = $mysqli->prepare("DELETE FROM ordonnances WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

header("Location: ordonnances.php");
exit;
?>
