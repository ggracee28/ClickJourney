<?php
require_once 'config.php';
require_once 'voyage_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('connexion.php');
}

// Récupérer l'ID du voyage
$id = $_GET['id'] ?? 0;
$voyage = getVoyageById($id);

if ($voyage && isLoggedIn()) {
    addConsultedVoyage($_SESSION['user_id'], $voyage['id']);
}

// Si le voyage n'existe pas, rediriger vers la liste des voyages
if (!$voyage) {
    redirect('destination.php');
}

// Ajouter ce voyage à l'historique de l'utilisateur
addConsultedVoyage($_SESSION['user_id'], $id);

// Récupérer les étapes du voyage
$etapes = $voyage['etapes'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="voyage_detail.css">
    <title>INFLYENCE - <?php echo $voyage['titre']; ?></title>
    <script src="https://kit.fontawesome.com/d669ac8659.js" crossorigin="anonymous"></script>
    <style>
        .voyage-detail {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .voyage-header {
            display: flex;
            margin-bottom: 30px;
        }
        .voyage-image {
            flex: 0 0 400px;
            margin-right: 30px;
        }
        .voyage-image img {
            width: 100%;
            border-radius: 8px;
        }
        .voyage-info {
            flex: 1;
        }
        .etapes-list {
            margin-top: 30px;
        }
        .etape-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .etape-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .etape-title {
            font-size: 1.2em;
            font-weight: bold;
        }
        .etape-dates {
            color: #666;
        }
        .options-section {
            margin-top: 15px;
        }
        .option-group {
            margin-bottom: 15px;
        }
        .option-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .option-select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .submit-container {
            text-align: center;
            margin-top: 30px;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
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
                <?php if (isAdmin()): ?>
                <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Déconnexion</a>
            </nav>
        </div>
    </header>

    <section class="section voyage-detail">
        <div class="voyage-header">
            <div class="voyage-image">
                <img src="<?php echo $voyage['image']; ?>" alt="<?php echo $voyage['titre']; ?>">
            </div>
            <div class="voyage-info">
                <h1><?php echo $voyage['titre']; ?></h1>
                <p><?php echo $voyage['description']; ?></p>
                <p><strong>Durée:</strong> <?php echo $voyage['duree']; ?> jours</p>
                <p><strong>Prix de base:</strong> <?php echo $voyage['prix']; ?>£</p>
                <p><strong>Date de départ:</strong> <?php echo $voyage['date_depart']; ?></p>
            </div>
        </div>

        <form action="voyage_recap.php" method="POST">
            <input type="hidden" name="voyage_id" value="<?php echo $voyage['id']; ?>">
            
            <div class="etapes-list">
                <h2>Itinéraire et options</h2>
                
                <?php foreach ($etapes as $index => $etape): ?>
                <div class="etape-item">
                    <div class="etape-header">
                        <div class="etape-title"><?php echo $etape['titre']; ?></div>
                        <div class="etape-dates">
                            Jour <?php echo $etape['jour_debut']; ?> - Jour <?php echo $etape['jour_fin']; ?>
                        </div>
                    </div>
                    
                    <p><?php echo $etape['description']; ?></p>
                    
                    <div class="options-section">
                        <div class="option-group">
                            <div class="option-title">Hébergement:</div>
                            <select name="hebergement_<?php echo $etape['id']; ?>" class="option-select">
                                <?php foreach ($etape['options_hebergement'] as $option): ?>
                                <option value="<?php echo $option['id']; ?>" <?php echo $option['default'] ? 'selected' : ''; ?>>
                                    <?php echo $option['titre']; ?> (<?php echo $option['prix']; ?>£ par personne)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="option-group">
                            <div class="option-title">Restauration:</div>
                            <select name="restauration_<?php echo $etape['id']; ?>" class="option-select">
                                <?php foreach ($etape['options_restauration'] as $option): ?>
                                <option value="<?php echo $option['id']; ?>" <?php echo $option['default'] ? 'selected' : ''; ?>>
                                    <?php echo $option['titre']; ?> (<?php echo $option['prix']; ?>£ par personne)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="option-group">
                            <div class="option-title">Activités:</div>
                            <?php foreach ($etape['options_activites'] as $option): ?>
                            <div>
                                <label>
                                    <input type="checkbox" name="activite_<?php echo $etape['id']; ?>[]" value="<?php echo $option['id']; ?>" <?php echo $option['default'] ? 'checked' : ''; ?>>
                                    <?php echo $option['titre']; ?> (<?php echo $option['prix']; ?>£ par personne)
                                </label>
                                <input type="number" name="activite_<?php echo $etape['id']; ?>_<?php echo $option['id']; ?>_nb" min="1" max="10" value="1" style="width: 50px; margin-left: 10px;">
                                personnes
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (isset($etape['options_transport']) && !empty($etape['options_transport'])): ?>
                        <div class="option-group">
                            <div class="option-title">Transport vers l'étape suivante:</div>
                            <select name="transport_<?php echo $etape['id']; ?>" class="option-select">
                                <?php foreach ($etape['options_transport'] as $option): ?>
                                <option value="<?php echo $option['id']; ?>" <?php echo $option['default'] ? 'selected' : ''; ?>>
                                    <?php echo $option['titre']; ?> (<?php echo $option['prix']; ?>£ par personne)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="submit-container">
                <button type="submit" class="submit-btn">Voir le récapitulatif</button>
            </div>
        </form>
    </section>
</body>
</html>