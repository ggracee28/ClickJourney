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

// Si le voyage n'existe pas, rediriger vers la liste des voyages
if (!$voyage) {
    redirect('destination.php');
}

// Ne plus ajouter le voyage au panier ici

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
                <a href="<?php echo isLoggedIn() ? 'panier.php' : 'connexion.php'; ?>">Mon Panier</a>
                <a href="profil.php">Profil</a>
                <?php if (isAdmin()): ?>
                <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Déconnexion</a>
                <div class="trigger-btn" onclick="changeDarkMode()" id="btn">
    <span>Mode sombre</span>
    <img src="mode.png">
</div>
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
                <p><strong>Prix de base:</strong> <span id="base-price" data-price="<?php echo $voyage['prix']; ?>"><?php echo $voyage['prix']; ?>£</span></p>
                <p><strong>Date de départ:</strong> <?php echo $voyage['date_depart']; ?></p>
            </div>
        </div>

        <form action="voyage_recap.php" method="POST">
            <input type="hidden" name="voyage_id" value="<?php echo $voyage['id']; ?>">
            
            <div class="etapes-list">
                <h2>Itinéraire et options</h2>
                
                <?php foreach ($etapes as $index => $etape): ?>
                <div class="etape-item" data-etape-id="<?php echo $etape['id']; ?>">
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
                            <select name="hebergement_<?php echo $etape['id']; ?>" class="option-select" data-option-type="hebergement">
                                <?php foreach ($etape['options_hebergement'] as $option): ?>
                                <option value="<?php echo $option['id']; ?>" data-price="<?php echo $option['prix']; ?>" <?php echo $option['default'] ? 'selected' : ''; ?>>
                                    <?php echo $option['titre']; ?> (<?php echo $option['prix']; ?>£ par personne)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="option-group">
                            <div class="option-title">Restauration:</div>
                            <select name="restauration_<?php echo $etape['id']; ?>" class="option-select" data-option-type="restauration">
                                <?php foreach ($etape['options_restauration'] as $option): ?>
                                <option value="<?php echo $option['id']; ?>" data-price="<?php echo $option['prix']; ?>" <?php echo $option['default'] ? 'selected' : ''; ?>>
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
                                    <input type="checkbox" name="activite_<?php echo $etape['id']; ?>[]" value="<?php echo $option['id']; ?>" data-price="<?php echo $option['prix']; ?>" class="activite-checkbox" <?php echo $option['default'] ? 'checked' : ''; ?>>
                                    <?php echo $option['titre']; ?> (<?php echo $option['prix']; ?>£ par personne)
                                </label>
                                <input type="number" name="activite_<?php echo $etape['id']; ?>_<?php echo $option['id']; ?>_nb" min="1" max="10" value="1" class="activite-quantity" style="width: 50px; margin-left: 10px;">
                                personnes
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (isset($etape['options_transport']) && !empty($etape['options_transport'])): ?>
                        <div class="option-group">
                            <div class="option-title">Transport vers l'étape suivante:</div>
                            <select name="transport_<?php echo $etape['id']; ?>" class="option-select" data-option-type="transport">
                                <?php foreach ($etape['options_transport'] as $option): ?>
                                <option value="<?php echo $option['id']; ?>" data-price="<?php echo $option['prix']; ?>" <?php echo $option['default'] ? 'selected' : ''; ?>>
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

        <div class="price-total">
            Prix estimé total: <span id="total-price"><?php echo $voyage['prix']; ?>£</span>
        </div>
    </section>

    <script src="voyage_detail.js"></script>
</body>
<script src="script.js"></script>
</html>