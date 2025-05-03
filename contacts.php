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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"] ?? '';
    $lien = $_POST["lien"] ?? '';
    $telephone = $_POST["telephone"] ?? '';

    if ($nom && $lien && $telephone) {
        $stmt = $mysqli->prepare("INSERT INTO contacts_urgence (user_id, nom, lien, telephone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $nom, $lien, $telephone);
        $stmt->execute();
        $success = true;
    }
}

$stmt = $mysqli->prepare("SELECT * FROM contacts_urgence WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$contacts = $result->fetch_all(MYSQLI_ASSOC);
?>
contact deepseek

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Contacts d'urgence | MediAssist</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #7C3AED;
      --primary-light: #8B5CF6;
      --primary-soft: #C4B5FD;
      --primary-extra-light:rgb(192, 191, 196);
      --white: rgba(234, 232, 232, 0.95);
      --light-bg: rgba(189, 191, 192, 0.9);
      --dark-text: #1E293B;
      --gray-text: #64748B;
      --header-bg: rgba(30, 41, 59, 0.85);
      --success-bg: rgba(209, 250, 229, 0.9);
      --success-text: #065F46;
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

    /* Header transparent et fixe */
    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: var(--header-bg);
      color: white;
      padding: 0.8rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      backdrop-filter: blur(8px);
      z-index: 1000;
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
      color: white;
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
    }

    main {
      position: relative;
      z-index: 1;
      max-width: 1200px;
      margin: 6rem auto 2rem;
      padding: 2rem;
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      justify-content: center;
      background-color: var(--white);
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(4px);
    }

    .form-section, .list-section {
      background: var(--white);
      border-radius: 12px;
      padding: 2rem;
      width: 100%;
      max-width: 550px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(124, 58, 237, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-section:hover, .list-section:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(124, 58, 237, 0.15);
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
      border-bottom: 1px solid rgba(100, 116, 139, 0.1);
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

    input, button {
      padding: 0.85rem 1.25rem;
      border: 1px solid rgba(203, 213, 225, 0.7);
      border-radius: 10px;
      font-size: 1rem;
      transition: all 0.3s ease;
      background-color: var(--white);
    }

    input:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
      outline: none;
    }

    button {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
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

    .contact {
      background: var(--white);
      padding: 1.5rem;
      border-radius: 10px;
      margin-bottom: 1.25rem;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      border: 1px solid rgba(124, 58, 237, 0.1);
      transition: transform 0.3s ease;
    }

    .contact:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .contact strong {
      color: var(--primary);
      font-weight: 600;
    }

    .contact div {
      margin-bottom: 0.5rem;
      line-height: 1.6;
      color: var(--gray-text);
    }

    .phone-link {
      color: var(--primary-light);
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .phone-link:hover {
      color: var(--primary);
      text-decoration: underline;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1.5rem;
      background: rgba(100, 116, 139, 0.1);
      color: var(--dark-text);
      padding: 0.8rem 1.5rem;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      border: 1px solid rgba(100, 116, 139, 0.2);
    }

    .back-btn:hover {
      background: rgba(100, 116, 139, 0.2);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    .empty-state {
      text-align: center;
      color: var(--gray-text);
      padding: 2rem;
      font-size: 1.1rem;
      background-color: var(--light-bg);
      border-radius: 10px;
      border: 1px dashed rgba(100, 116, 139, 0.3);
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
        margin: 5rem 1rem 1rem;
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

  <!-- Header transparent et fixe -->
  <header>
    <div class="header-content">
      <div class="logo-icon">
        <i class="fas fa-phone-alt"></i>
      </div>
      <div>
        <div class="header-title">MediAssist</div>
        <div class="header-subtitle">Contacts d'urgence</div>
      </div>
    </div>
  </header>

  <main>
    <div class="form-section">
      <h2><i class="fas fa-plus-circle"></i> Ajouter un contact</h2>
      
      <?php if ($success): ?>
        <div class="success">
          <i class="fas fa-check-circle"></i> Contact ajouté avec succès !
        </div>
      <?php endif; ?>
      
      <form method="POST" >
        <div class="form-group">
          <label for="nom">Nom du contact</label>
          <input type="text" id="nom" name="nom" placeholder="Dr. Dupont" required>
        </div>
        
        <div class="form-group">
          <label for="lien">Lien de parenté</label>
          <input type="text" id="lien" name="lien" placeholder="ex: médecin traitant, père, ami..." required>
        </div>
        
        <div class="form-group">
          <label for="telephone">Téléphone</label>
          <input type="tel" id="telephone" name="telephone" placeholder="06 12 34 56 78" required>
        </div>
        
        <button type="submit">
          <i class="fas fa-save"></i> Enregistrer
        </button>
      </form>
    </div>

    <div class="list-section">
      <h2><i class="fas fa-list-ul"></i> Contacts enregistrés</h2>
      
      
        <?php foreach ($contacts as $c): ?>
        <div class="contact">
          <div><strong>Nom :</strong> <?= htmlspecialchars($c['nom']) ?></div>
          <div><strong>Lien :</strong> <?= htmlspecialchars($c['lien']) ?></div>
          <div><strong>Téléphone :</strong> 
            <a href="tel:<?= htmlspecialchars($c['telephone']) ?>" class="phone-link">
              <?= htmlspecialchars($c['telephone']) ?>
            </a>
          </div>
          <div>
            <a href="modifier_contact.php?id=<?= $c['id'] ?>" class="btn btn-edit"> ✏️Modifier</a>
            <a href="supprimer_contact.php?id=<?= $c['id'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?');"> 🗑️Supprimer</a>
          </div>
        </div>
  <?php endforeach; ?>

      

      <a href="dashboard.html" class="back-btn">
        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
      </a>
    </div>
  </main>

</body>
</html>