<?php
session_start();

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "medicament");

if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

if ($id) {
    $sql = "DELETE FROM rappels WHERE id = ? AND user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        header("Location: medicament.php?success=3");
        exit;
    } else {
        echo "Erreur lors de la suppression : " . $stmt->error;
    }
} else {
    echo "ID invalide.";
}
?>
