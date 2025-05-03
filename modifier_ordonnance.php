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
$id = $_GET['id'] ?? null;

if (!$id) {
    exit("Ordonnance introuvable.");
}

// Traitement de la mise à jour
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $date = $_POST["date"] ?? '';
    $medecin = $_POST["medecin"] ?? '';
    $description = $_POST["description"] ?? '';
    $fichier_nom = $_POST["ancien_fichier"] ?? null;

    // Si un nouveau fichier est envoyé
    if (isset($_FILES["fichier"]) && $_FILES["fichier"]["error"] === UPLOAD_ERR_OK) {
        $tmp = $_FILES["fichier"]["tmp_name"];
        $nom = basename($_FILES["fichier"]["name"]);
        $chemin = "uploads/" . uniqid() . "_" . $nom;
        if (move_uploaded_file($tmp, $chemin)) {
            $fichier_nom = $chemin;
        }
    }

    $stmt = $mysqli->prepare("UPDATE ordonnances SET date = ?, medecin = ?, description = ?, fichier = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $date, $medecin, $description, $fichier_nom, $id, $user_id);
    $stmt->execute();
    header("Location: ordonnances.php?success=update");
    exit;
}

// Récupérer l'ordonnance à modifier
$stmt = $mysqli->prepare("SELECT * FROM ordonnances WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ordonnance = $result->fetch_assoc();

if (!$ordonnance) {
    exit("Ordonnance non trouvée.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier Ordonnance - MediAssist</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #7C3AED;
      --primary-light: #8B5CF6;
      --primary-soft: #C4B5FD;
      --primary-extra-light: #EDE9FE;
      --white:rgb(235, 235, 235);
      --light-bg:rgb(221, 222, 222);
      --dark-text: #1E293B;
      --gray-text: #64748B;
      --gray-light:rgb(201, 202, 202);
      --gray-medium:rgb(161, 161, 161);
      --border:rgb(189, 192, 196);
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
      max-width: 800px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light);
      animation: fadeIn 0.6s ease-out forwards;
    }

    .form-container {
      width: 100%;
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

    input, select, textarea, button {
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

    input:focus, select:focus, textarea:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
      outline: none;
    }

    .file-input-container {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .file-preview {
      margin-top: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .file-preview a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }

    .file-preview a:hover {
      text-decoration: underline;
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
        padding: 1.5rem;
        border-radius: 12px;
      }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
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
        <div class="header-subtitle">Modification d'ordonnance</div>
      </div>
    </div>
  </header>

  <main>
    <div class="form-container">
      <h2><i class="fas fa-edit"></i> Modifier l'ordonnance</h2>
      
      <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
          <label for="date">Date</label>
          <input type="date" id="date" name="date" value="<?= htmlspecialchars($ordonnance['date']) ?>" required>
        </div>

        <div class="form-group">
          <label for="medecin">Médecin</label>
          <input type="text" id="medecin" name="medecin" value="<?= htmlspecialchars($ordonnance['medecin']) ?>" required>
        </div>

        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" required><?= htmlspecialchars($ordonnance['description']) ?></textarea>
        </div>

        <div class="form-group file-input-container">
          <label for="fichier">Fichier</label>
          <input type="file" id="fichier" name="fichier">
          
          <?php if ($ordonnance['fichier']): ?>
            <div class="file-preview">
              <i class="fas fa-file-pdf" style="color: var(--primary);"></i>
              <a href="<?= $ordonnance['fichier'] ?>" target="_blank">Voir le fichier actuel</a>
            </div>
          <?php endif; ?>
          
          <input type="hidden" name="ancien_fichier" value="<?= $ordonnance['fichier'] ?>">
        </div>

        <button type="submit">
          <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
      </form>

      <a href="ordonnances.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Retour aux ordonnances
      </a>
    </div>
  </main>

</body>
</html>