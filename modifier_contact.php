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
$success = isset($_GET['success']) && $_GET['success'] === '1';

if (isset($_GET['id'])) {
    $contact_id = $_GET['id'];
    
    // Récupérer les informations du contact à modifier
    $stmt = $mysqli->prepare("SELECT * FROM contacts_urgence WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $contact_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $contact = $result->fetch_assoc();
    
    if (!$contact) {
        die("Contact non trouvé.");
    }
} else {
    die("Aucun identifiant de contact fourni.");
}

// Modifier le contact
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"] ?? '';
    $lien = $_POST["lien"] ?? '';
    $telephone = $_POST["telephone"] ?? '';
    
    if ($nom && $lien && $telephone) {
        $stmt = $mysqli->prepare("UPDATE contacts_urgence SET nom = ?, lien = ?, telephone = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $nom, $lien, $telephone, $contact_id, $user_id);
        $stmt->execute();
        header("Location: modifier_contact.php?id=$contact_id&success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier Contact - MediAssist</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #7C3AED;
      --primary-light: #8B5CF6;
      --primary-soft: #C4B5FD;
      --primary-extra-light: #EDE9FE;
      --white: #ffffff;
      --light-bg: #f3f4f6;
      --dark-text: #1E293B;
      --gray-text: #64748B;
      --gray-light: #e2e8f0;
      --gray-medium: #94a3b8;
      --border: #cbd5e1;
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
      max-width: 600px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light);
      animation: fadeIn 0.6s ease-out forwards;
    }

    .form-section {
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

    input, select, button {
      padding: 0.85rem 1.25rem;
      border: 1px solid var(--gray-medium);
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: var(--white);
    }

    input:focus, select:focus {
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
      margin-top: 1rem;
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
        <i class="fas fa-user-circle"></i>
      </div>
      <div>
        <div class="header-title">MediAssist</div>
        <div class="header-subtitle">Modification de contact</div>
      </div>
    </div>
  </header>

  <main>
    <div class="form-section">
      <h2><i class="fas fa-user-edit"></i> Modifier un contact d'urgence</h2>

      <?php if ($success): ?>
        <div class="success">
          <i class="fas fa-check-circle"></i> Contact modifié avec succès !
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="nom">Nom du contact</label>
          <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($contact['nom']) ?>" required>
        </div>

        <div class="form-group">
          <label for="lien">Lien de parenté</label>
          <input type="text" id="lien" name="lien" value="<?= htmlspecialchars($contact['lien']) ?>" required>
        </div>

        <div class="form-group">
          <label for="telephone">Téléphone</label>
          <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($contact['telephone']) ?>" required>
        </div>

        <button type="submit">
          <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
      </form>

      <a href="contacts.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Retour aux contacts
      </a>
    </div>
  </main>

  <script>
    // Masquer le message de succès après 5 secondes
    const successMessage = document.querySelector('.success');
    if (successMessage) {
      setTimeout(() => {
        successMessage.style.display = 'none';
      }, 5000);
    }
  </script>
</body>
</html>