<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "mediassist");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT id, date, heure, medecin, motif FROM rendezvous WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'title' => $row['medecin'] . ' - ' . $row['motif'],
        'start' => $row['date'] . 'T' . $row['heure'],
        'allDay' => false
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
