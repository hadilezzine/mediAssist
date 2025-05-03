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
$success = false;

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"] ?? '';
    $medecin = $_POST["medecin"] ?? '';
    $description = $_POST["description"] ?? '';
    $fichier_nom = null;

    if (isset($_FILES["fichier"]) && $_FILES["fichier"]["error"] === UPLOAD_ERR_OK) {
        $nom_tmp = $_FILES["fichier"]["tmp_name"];
        $nom_fichier = basename($_FILES["fichier"]["name"]);
        $dossier = "uploads/";
        $chemin = $dossier . uniqid() . "_" . $nom_fichier;

        if (move_uploaded_file($nom_tmp, $chemin)) {
            $fichier_nom = $chemin;
        }
    }

    if ($date && $medecin && $description) {
        $stmt = $mysqli->prepare("INSERT INTO ordonnances (user_id, date, medecin, description, fichier) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $date, $medecin, $description, $fichier_nom);
        $stmt->execute();
        $success = true;
    }
}

// Récupération des ordonnances
$stmt = $mysqli->prepare("SELECT * FROM ordonnances WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ordonnances = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ordonnances | MediAssist</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      gap: 0.8rem;
    }

    .logo-icon {
      font-size: 1.5rem;
    }

    .header-title {
      font-size: 1.2rem;
      font-weight: 700;
    }

    .header-subtitle {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.8);
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

    .form-section, .list-section {
      background: var(--white);
      border-radius: 12px;
      padding: 2rem;
      width: 100%;
      max-width: 550px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-section:hover, .list-section:hover {
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

    input, textarea, select, button, .file-input-container {
      padding: 0.85rem 1.25rem;
      border: 1px solid var(--gray-medium);
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: var(--white);
    }

    textarea {
      min-height: 120px;
      resize: vertical;
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

    .ordonnance {
      background: var(--white);
      padding: 1.5rem;
      border-radius: 10px;
      margin-bottom: 1.25rem;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light);
      transition: transform 0.3s ease;
    }

    .ordonnance:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .ordonnance strong {
      color: var(--primary);
      font-weight: 600;
    }

    .ordonnance div {
      margin-bottom: 0.5rem;
      line-height: 1.6;
      color: var(--gray-text);
    }

    .file-link {
      color: var(--primary-light);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      transition: color 0.3s ease;
    }

    .file-link:hover {
      color: var(--primary);
      text-decoration: underline;
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
      
      .form-section, .list-section {
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
      
      .form-section, .list-section {
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
        <i class="fas fa-file-prescription"></i>
      </div>
      <div>
        <div class="header-title">MediAssist</div>
        <div class="header-subtitle">Gestion des ordonnances</div>
      </div>
    </div>
  </header>

  <main>
  <div class="form-section">
  <h2><i class="fas fa-plus-circle"></i> Ajouter une ordonnance</h2>

  <?php if ($success): ?>
    <div class="success">
      <i class="fas fa-check-circle"></i> Ordonnance enregistrée avec succès !
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
    <div class="success">
      <i class="fas fa-check-circle"></i> Ordonnance mise à jour avec succès !
    </div>
  <?php endif; ?>

        
      
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="date">Date</label>
          <input type="date" id="date" name="date" required>
        </div>
        
        <div class="form-group">
          <label for="medecin">Médecin</label>
          <input type="text" id="medecin" name="medecin" placeholder="Nom du médecin" required>
        </div>
        
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Détails de l'ordonnance" required></textarea>
        </div>
        
        <div class="form-group">
          <label for="fichier">Fichier (PDF/JPG/PNG)</label>
          <input type="file" id="fichier" name="fichier" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        
        <button type="submit">
          <i class="fas fa-save"></i> Enregistrer
        </button>
      </form>
    </div>

    <div class="list-section">
      <h2><i class="fas fa-list-ul"></i> Ordonnances enregistrées</h2>
      
      
      <?php if (empty($ordonnances)): ?>
        <div class="empty-state">
          <i class="fas fa-file-prescription" style="font-size: 2rem; margin-bottom: 1rem; color: var(--gray-text);"></i>
          <p>Aucune ordonnance enregistrée pour le moment</p>
        </div>
      <?php else: ?>
        <?php foreach ($ordonnances as $o): ?>
          <div class="ordonnance">
            <div><strong>Date :</strong> <?= htmlspecialchars($o['date']) ?></div>
            <div><strong>Médecin :</strong> <?= htmlspecialchars($o['medecin']) ?></div>
            <div><strong>Description :</strong> <?= nl2br(htmlspecialchars($o['description'])) ?></div>
            <?php if (!empty($o['fichier'])): ?>
              <div>
                <a href="<?= htmlspecialchars($o['fichier']) ?>" target="_blank" class="file-link">
                  <i class="fas fa-paperclip"></i> Voir le fichier joint
                </a>
              </div>
            <?php endif; ?>
            <div style="margin-top: 1rem;">
            <a href="modifier_ordonnance.php?id=<?= $o['id'] ?>" class="file-link">
              ✏️ Modifier
            </a> |
            <a href="supprimer_ordonnance.php?id=<?= $o['id'] ?>" class="file-link" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette ordonnance ?');">
              🗑️ Supprimer
            </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <a href="dashboard.html" class="back-btn">
        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
      </a>
    </div>
  </main>

</body>
</html>