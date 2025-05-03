<?php
session_start(); 

$mysqli = new mysqli("localhost", "root", "", "mediassist");

if ($mysqli->connect_error) {
    die("Erreur de connexion à la base de données : " . $mysqli->connect_error);
}

$email = $_POST['email'] ?? '';
$mot_de_passe = $_POST['password'] ?? '';

// Requête pour récupérer l'utilisateur
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($users = $result->fetch_assoc()) {
    if (password_verify($mot_de_passe, $users['mot_de_passe'])) {
        
        $_SESSION['user_id'] = $users['id'];
        $_SESSION['user_email'] = $users['email']; // optionnel
        $_SESSION['user_nom'] = $users['nom']; // optionnel

    
        header("Location: dashboard.html");
        exit;
    } else {
        echo "Mot de passe incorrect.";
    }
} else {
    echo "Aucun compte trouvé avec cet email.";
}

$stmt->close();
$mysqli->close();
?>
