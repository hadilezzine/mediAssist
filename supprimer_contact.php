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

if (isset($_GET['id'])) {
    $contact_id = $_GET['id'];

    // Supprimer le contact
    $stmt = $mysqli->prepare("DELETE FROM contacts_urgence WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contact_id, $user_id);
    $stmt->execute();

    header("Location: contacts.php");
    exit;
} else {
    die("Aucun identifiant de contact fourni.");
}
