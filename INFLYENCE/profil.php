<?php
require_once 'config.php';
require_once 'user_functions.php';
require_once 'voyage_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: connexion.php');
    exit;
}

// Récupérer l'utilisateur courant ou cible (pour admin)
$current_user_id = $_SESSION['user_id'];
$target_user_id = $current_user_id;

if (isAdmin() && isset($_GET['id'])) {
    $target_user_id = (int)$_GET['id'];
}

$user = getUserData($target_user_id);

if (!$user) {
    header('Location: profil.php');
    exit;
}

$consulted_voyages = [];
if (!empty($user['voyages_consultes']) && is_array($user['voyages_consultes'])) {
    // Garder l'ordre chronologique (du plus récent au plus ancien)
    foreach ($user['voyages_consultes'] as $voyage_id) {
        $voyage = getVoyageById((int)$voyage_id);
        if ($voyage && !in_array($voyage, $consulted_voyages)) {
            $consulted_voyages[] = $voyage;
        }
    }
    // Limiter à 10 affichages maximum
    $consulted_voyages = array_slice($consulted_voyages, 0, 10);
}


$purchased_voyages = [];
if (!empty($user['voyages_achetes']) && is_array($user['voyages_achetes'])) {
    foreach (array_unique($user['voyages_achetes']) as $voyage_id) {
        $voyage = getVoyageById((int)$voyage_id);
        if ($voyage) {
            $purchased_voyages[] = $voyage;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - INFLYENCE</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profil.css">
    <script src="https://kit.fontawesome.com/d669ac8659.js" crossorigin="anonymous"></script>
</head>
<body>
    
      <!-- HEADER IDENTIQUE -->
  <header class="header">
    <div class="nav-container">
      <div class="logo">
        <img src="logo1.png" alt="Logo INFLYENCE" class="logo-img">INFLYENCE
      </div>
      <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>
      <nav class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="destination.php">Destinations</a>
        <a href="profil.php">Profil</a>
        <a href="admin.php">Admin</a>
        <a href="logout.php">Déconnexion</a>
      </nav>
    </div>
  </header> 
    <section class="section profile-section">
        <div class="profile-container">
            <h2>Profil de <?php echo htmlspecialchars($user['prenom'].' '.$user['nom']); ?></h2>

            <div class="profile-field">
                <span class="field-label">Nom :</span>
                <span class="field-value"><?php echo htmlspecialchars($user['nom']); ?></span>
                <button class="edit-btn">Modifier</button>
            </div>

            <div class="profile-field">
                <span class="field-label">Prénom :</span>
                <span class="field-value"><?php echo htmlspecialchars($user['prenom']); ?></span>
                <button class="edit-btn">Modifier</button>
            </div>

            <div class="profile-field">
                <span class="field-label">Email :</span>
                <span class="field-value"><?php echo htmlspecialchars($user['email']); ?></span>
                <button class="edit-btn">Modifier</button>
            </div>

            <div class="profile-field">
                <span class="field-label">Mot de passe :</span>
                <span class="field-value">********</span>
                <button class="edit-btn">Modifier</button>
            </div>

            <div class="profile-field">
                <span class="field-label">Date de naissance :</span>
                <span class="field-value"><?php echo htmlspecialchars($user['date_naissance']); ?></span>
                <button class="edit-btn">Modifier</button>
            </div>

            <div class="profile-field">
                <span class="field-label">Pays d'origine :</span>
                <span class="field-value"><?php echo htmlspecialchars($user['pays_origine']); ?></span>
                <button class="edit-btn">Modifier</button>
            </div>

            <!-- Voyages consultés -->
            <div class="profile-field" style="flex-direction: column; align-items: flex-start;">
                <div style="display: flex; width: 100%;">
                    <span class="field-label">Voyages consultés :</span>
                    <div class="field-value" style="flex: 2;">
                        <?php if (!empty($consulted_voyages)): ?>
                            <ul class="voyages-list">
                                <?php foreach ($consulted_voyages as $voyage): ?>
                                    <li>
                                        <a href="voyage_detail.php?id=<?php echo $voyage['id']; ?>">
                                            <?php echo htmlspecialchars($voyage['titre']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="margin: 0; color: #666;">Aucun voyage consulté récemment</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Voyages achetés -->
            <div class="profile-field" style="flex-direction: column; align-items: flex-start; border-bottom: none;">
                <div style="display: flex; width: 100%;">
                    <span class="field-label">Voyages achetés :</span>
                    <div class="field-value" style="flex: 2;">
                        <?php if (!empty($purchased_voyages)): ?>
                            <ul class="voyages-list">
                                <?php foreach ($purchased_voyages as $voyage): ?>
                                    <li>
                                        <a href="voyage_detail.php?id=<?php echo $voyage['id']; ?>">
                                            <?php echo htmlspecialchars($voyage['titre']); ?>
                                            (<?php echo number_format($voyage['prix'], 2); ?>€)
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p style="margin: 0; color: #666;">Aucun voyage acheté</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
</body>
</html>