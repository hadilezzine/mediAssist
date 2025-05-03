<?php
session_start(); // ← Indispensable

$pdo = new PDO("mysql:host=localhost;dbname=mediassist;charset=utf8", "root", "");

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Erreur : utilisateur non connecté.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"];
    $heure = $_POST["heure"];
    $medecin = $_POST["medecin"];
    $motif = $_POST["motif"];

    // 🔧 Ajout de user_id dans la requête SQL
    $stmt = $pdo->prepare("INSERT INTO rendezvous (user_id, date, heure, medecin, motif) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $date, $heure, $medecin, $motif]);

    header("Location: rendezvous.php?success=1");
    exit;
}

// On ne récupère que les rendez-vous de l’utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM rendezvous WHERE user_id = ?");
$stmt->execute([$user_id]);
$rendezvous = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Génération du calendrier
$events = [];
foreach ($rendezvous as $r) {
    $events[] = [
        "title" => "🩺 " . $r["medecin"] . " – " . $r["motif"],
        "start" => $r["date"] . "T" . $r["heure"],
        "allDay" => false
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rendez-vous | MediAssist</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #7C3AED;
      --primary-light: #8B5CF6;
      --primary-soft: #C4B5FD;
      --primary-extra-light: #EDE9FE;
      --white: rgb(235, 235, 235);
      --light-bg: rgb(221, 222, 222);
      --dark-text: #1E293B;
      --gray-text: #64748B;
      --gray-light: rgb(201, 202, 202);
      --gray-medium: rgb(161, 161, 161);
      --border: rgb(189, 192, 196);
      --card-bg: rgba(173, 171, 171, 0.97);
      --success-bg: #D1FAE5;
      --success-text: #065F46;
      --error-bg: #FEE2E2;
      --error-text: #B91C1C;
      --header-bg: #1E293B;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    html, body {
      height: 100%;
      overflow-x: hidden;
      background-color: var(--primary-extra-light);
    }

    .background-frame {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      border: none;
      z-index: 0;
      filter: blur(14px) brightness(1.2);
      opacity: 1;
      pointer-events: none;
    }

    header {
      background: var(--header-bg);
      color: var(--white);
      padding: 0.8rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 3;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .header-content {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo-icon {
      font-size: 1.8rem;
      color: var(--primary-soft);
    }

    .header-title {
      font-size: 1.4rem;
      font-weight: 600;
      color: var(--white);
    }

    .header-subtitle {
      font-size: 0.9rem;
      color: var(--gray-medium);
      margin-left: 0.5rem;
      font-weight: 400;
    }

    .user-menu {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background-color: var(--primary-soft);
      color: var(--header-bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    main {
      position: relative;
      z-index: 1;
      max-width: 1200px;
      margin: 2rem auto;
      padding: 2rem;
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      background-color: var(--light-bg);
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light);
    }

    .form-section, .calendar-section {
      background: var(--white);
      border-radius: 12px;
      padding: 2rem;
      width: 100%;
      max-width: 550px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-section:hover, .calendar-section:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(124, 58, 237, 0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: var(--dark-text);
      font-weight: 600;
      font-size: 1.4rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid var(--gray-light);
    }

    form {
      display: grid;
      gap: 1.25rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    label {
      font-weight: 500;
      color: var(--dark-text);
      font-size: 0.95rem;
    }

    input, textarea, select, button {
      padding: 0.85rem 1.25rem;
      border: 1px solid var(--gray-medium);
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: var(--white);
    }

    input:focus, textarea:focus, select:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
      outline: none;
    }

    button {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: var(--white);
      font-weight: 600;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      margin-top: 0.5rem;
    }

    button:hover {
      background: linear-gradient(135deg, var(--primary-light), var(--primary));
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    }

    .success {
      background-color: var(--success-bg);
      color: var(--success-text);
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
      text-align: center;
      font-weight: 500;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      border: 1px solid rgba(5, 95, 70, 0.1);
    }

    /* Styles FullCalendar */
    .fc {
      background: var(--white);
      border-radius: 8px;
      border: 1px solid var(--gray-light);
    }

    .fc-toolbar-title {
      font-weight: 600;
      color: var(--dark-text);
    }

    .fc-button {
      background: var(--white);
      border: 1px solid var(--gray-medium);
      color: var(--dark-text);
    }

    .fc-button-primary {
      background: var(--primary);
      border: none;
      color: var(--white);
    }

    .fc-event {
      background: var(--primary-light);
      border: none;
    }

    .fc-day-today {
      background: var(--primary-extra-light) !important;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1.5rem;
      background: var(--gray-medium);
      color: var(--dark-text);
      padding: 0.8rem 1.5rem;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      border: 1px solid var(--gray-medium);
    }

    .back-btn:hover {
      background: var(--gray-text);
      color: var(--white);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .empty-state {
      text-align: center;
      color: var(--gray-text);
      padding: 2rem;
      font-size: 1.1rem;
      background-color: var(--light-bg);
      border-radius: 10px;
      border: 1px dashed var(--gray-medium);
    }

    @media (max-width: 900px) {
      main {
        padding: 1.5rem;
      }
      
      .form-section, .calendar-section {
        max-width: 100%;
      }
    }

    @media (max-width: 768px) {
      header {
        padding: 0.7rem 1.5rem;
      }
      
      .header-title {
        font-size: 1.2rem;
      }
      
      .header-subtitle {
        display: none;
      }
      
      main {
        margin: 1rem;
        padding: 1rem;
        border-radius: 12px;
      }
      
      .form-section, .calendar-section {
        padding: 1.5rem;
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    main {
      animation: fadeIn 0.6s ease-out forwards;
    }
  </style>
</head>
<body>

  <!-- Arrière-plan flou avec dashboard.html -->
  <iframe src="dashboard.html" class="background-frame"></iframe>

  <header>
    <div class="header-content">
      <div class="logo-icon">
        <i class="fas fa-calendar-alt"></i>
      </div>
      <div>
        <div class="header-title">MediAssist</div>
        <div class="header-subtitle">Gestion des rendez-vous</div>
      </div>
    </div>
  </header>

  <main>
    <div class="form-section">
      <h2><i class="fas fa-plus-circle"></i> Nouveau rendez-vous</h2>
      
       <?php if (isset($_GET['success'])): ?>
      <div class="success">✅ Rendez-vous ajouté avec succès !</div>
    <?php endif; ?>

      
      <form method="POST">
      <input type="date" name="date" required>
      <input type="time" name="heure" required>
      <input type="text" name="medecin" placeholder="Nom du médecin" required>
      <textarea name="motif" placeholder="Motif du rendez-vous" required></textarea>
      <button type="submit">Ajouter</button>
    </form>
    </div>

    <div class="calendar-section">
      <h2><i class="fas fa-calendar-days"></i> Calendrier</h2>
      <div id="calendar"></div>
      
      <div style="text-align: center;">
        <a href="dashboard.html" class="back-btn">
          <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
      </div>
    </div>
  </main>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: 'fr',
      headerToolbar: {
        start: 'prev,next today',
        center: 'title',
        end: 'dayGridMonth,timeGridWeek'
      },
      events: <?php echo json_encode($events); ?>
    });

    calendar.render();
  });
</script>

</body>
</html>