<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$mysqli = new mysqli("localhost", "root", "", "medicament");
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

// Suppression d'un médicament
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $stmt = $mysqli->prepare("DELETE FROM rappels WHERE id = ? AND user_id = ?");
  $stmt->bind_param("ii", $id, $user_id);
  $stmt->execute();
  header("Location: medicament.php?success=1");
  exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM rappels WHERE user_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$medicaments = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Médicaments - MediAssist</title>
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
      --header-bg: #1E293B; /* Nouvelle couleur de header */
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

    /* Nouveau style de header */
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
      background-color: var(--light-bg); /* Fond gris clair */
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light); /* Bordure grise */
    }

    .form-section, .list-section {
      background: var(--white);
      border-radius: 12px;
      padding: 2rem;
      width: 100%;
      max-width: 550px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light); /* Bordure grise */
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
      border-bottom: 1px solid var(--gray-light); /* Ligne de séparation grise */
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
      border: 1px solid var(--gray-medium); /* Bordure grise moyenne */
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

    .medicament {
      background: var(--white);
      padding: 1.5rem;
      border-radius: 10px;
      margin-bottom: 1.25rem;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--gray-light); /* Bordure grise */
      transition: transform 0.3s ease;
    }

    .medicament:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .medicament strong {
      color: var(--primary);
      font-weight: 600;
    }

    .medicament div {
      margin-bottom: 0.5rem;
      line-height: 1.6;
      color: var(--gray-text); /* Texte gris */
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 1.5rem;
      background: var(--gray-medium); /* Bouton gris */
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
      background-color: var(--light-bg); /* Fond gris clair */
      border-radius: 10px;
      border: 1px dashed var(--gray-medium); /* Bordure en pointillés gris */
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
        <i class="fas fa-pills"></i>
      </div>
      <div>
        <div class="header-title">MediAssist</div>
        <div class="header-subtitle">Gestion des médicaments</div>
      </div>
    </div>
  </header>

  <main>
    <div class="form-section">
      <h2><i class="fas fa-plus-circle"></i> Ajouter un médicament</h2>
      
      <div id="successMessage" class="success" style="display: none;">
        <i class="fas fa-check-circle"></i> Médicament ajouté avec succès !
      </div>
      
      <form action="ajouter.php" method="POST">
        <div class="form-group">
          <label for="nom">Nom du médicament</label>
          <input type="text" id="nom" name="nom" placeholder="Paracétamol, Ibuprofène..." required>
        </div>
        
        <div class="form-group">
          <label for="posologie">Posologie</label>
          <input type="text" id="posologie" name="posologie" placeholder="Ex: 1 comprimé 3 fois par jour" required>
        </div>
        
        <div class="form-group">
          <label for="heure">Heure de prise</label>
          <input type="time" id="heure" name="heure" required>
        </div>
        
        <div class="form-group">
          <label for="frequence">Fréquence</label>
          <select id="frequence" name="frequence" required>
            <option value="">-- Sélectionnez --</option>
            <option value="Quotidien">Quotidien</option>
            <option value="Hebdomadaire">Hebdomadaire</option>
            <option value="Mensuel">Mensuel</option>
            <option value="Ponctuel">Ponctuel</option>
          </select>
        </div>
        
        <button type="submit">
          <i class="fas fa-save"></i> Enregistrer
        </button>
      </form>
    </div>

    <div class="list-section">
      <h2><i class="fas fa-list-ul"></i> Médicaments enregistrés</h2>
      
      <?php if (empty($medicaments)): ?>
        <div class="empty-state">
          <i class="fas fa-pills" style="font-size: 2rem; margin-bottom: 1rem; color: var(--gray-text);"></i>
          <p>Aucun médicament enregistré pour le moment</p>
        </div>
      <?php else: ?>
        <?php foreach ($medicaments as $med): ?>
          <div class="medicament">
            <div><strong>Nom :</strong> <?= htmlspecialchars($med['nom']) ?></div>
            <div><strong>Posologie :</strong> <?= htmlspecialchars($med['posologie']) ?></div>
            <div><strong>Heure :</strong> <?= htmlspecialchars($med['heure']) ?></div>
            <div><strong>Fréquence :</strong> <?= htmlspecialchars($med['frequence']) ?></div>
            <div> 
            <a href="modifier_medicament.php?id=<?= $med['id'] ?>" style="color:blue; margin-right: 10px;">✏️ Modifier</a>
            <a href="supprimer_medicament.php?id=<?= $med['id'] ?>" onclick="return confirm('Supprimer ce médicament ?')" style="color:red;">🗑️ Supprimer</a>

            </td>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <a href="dashboard.html" class="back-btn">
        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
      </a>
    </div>

  </main>

  <script>
    const params = new URLSearchParams(window.location.search);
    if (params.get("success") === "1") {
      const successMessage = document.getElementById("successMessage");
      successMessage.style.display = "flex";
      
      // Masquer le message après 5 secondes
      setTimeout(() => {
        successMessage.style.display = "none";
      }, 5000);
      
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  </script>

</body>
</html>