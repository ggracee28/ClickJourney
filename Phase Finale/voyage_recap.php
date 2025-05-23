<?php
require_once 'config.php';
require_once 'voyage_functions.php';

// Vérifier si un formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['voyage_id'])) {
    redirect('destination.php');
}

// Récupérer l'ID du voyage
$voyage_id = $_POST['voyage_id'];
$voyage = getVoyageById($voyage_id);

if (!$voyage) {
    redirect('destination.php');
}

// Initialiser le prix total et le tableau des options
$prix_total = $voyage['prix'];
$options_details = [];

// Parcourir toutes les étapes pour calculer le prix total
foreach ($voyage['etapes'] as $etape) {
    $etape_id = $etape['id'];
    $etape_options = [];

    // Hébergement
    if (isset($_POST['hebergement_'.$etape_id])) {
        $option_id = $_POST['hebergement_'.$etape_id];
        foreach ($etape['options_hebergement'] as $option) {
            if ($option['id'] == $option_id) {
                $prix_total += $option['prix'];
                $etape_options['hebergement'] = $option;
                break;
            }
        }
    }

    // Restauration
    if (isset($_POST['restauration_'.$etape_id])) {
        $option_id = $_POST['restauration_'.$etape_id];
        foreach ($etape['options_restauration'] as $option) {
            if ($option['id'] == $option_id) {
                $prix_total += $option['prix'];
                $etape_options['restauration'] = $option;
                break;
            }
        }
    }

    // Activités
    if (isset($_POST['activite_'.$etape_id]) && is_array($_POST['activite_'.$etape_id])) {
        foreach ($_POST['activite_'.$etape_id] as $activite_id) {
            foreach ($etape['options_activites'] as $option) {
                if ($option['id'] == $activite_id) {
                    $nb_persons = $_POST['activite_'.$etape_id.'_'.$activite_id.'_nb'] ?? 1;
                    $prix_activite = $option['prix'] * $nb_persons;
                    $prix_total += $prix_activite;
                    $etape_options['activites'][] = [
                        'option' => $option,
                        'nb_persons' => $nb_persons,
                        'prix_total' => $prix_activite
                    ];
                    break;
                }
            }
        }
    }

    // Transport
    if (isset($_POST['transport_'.$etape_id]) && isset($etape['options_transport'])) {
        $option_id = $_POST['transport_'.$etape_id];
        foreach ($etape['options_transport'] as $option) {
            if ($option['id'] == $option_id) {
                $prix_total += $option['prix'];
                $etape_options['transport'] = $option;
                break;
            }
        }
    }

    if (!empty($etape_options)) {
        $options_details[$etape_id] = [
            'titre' => $etape['titre'],
            'options' => $etape_options
        ];
    }
}

// Ajouter le voyage au panier avec le prix total calculé
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    addVoyageToPanier($userId, $voyage_id, $prix_total);
}

// Stocker en session pour le paiement
$_SESSION['commande'] = [
    'voyage_id' => $voyage['id'],
    'titre' => $voyage['titre'],
    'date_depart' => $voyage['date_depart'],
    'duree' => $voyage['duree'],
    'prix_total' => $prix_total,
    'options_details' => $options_details
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif - INFLYENCE</title>
    <link rel="stylesheet" href="style.css">
    
    <style>
        .recap-container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .recap-header { text-align: center; margin-bottom: 30px; }
        .recap-title { font-size: 24px; font-weight: bold; }
        .recap-section { margin-bottom: 30px; border: 1px solid #eee; padding: 15px; border-radius: 5px; }
        .recap-section-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333; }
        .recap-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #eee; }
        .recap-item-label { font-weight: bold; }
        .recap-total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; padding-top: 20px; border-top: 2px solid #333; }
        .action-buttons { display: flex; justify-content: space-between; margin-top: 30px; }
        .action-btn { padding: 10px 20px; border-radius: 4px; font-weight: bold; text-decoration: none; text-align: center; }
        .modify-btn { background-color: #f0f0f0; color: #333; border: 1px solid #ccc; }
        .payment-btn { background-color: #4CAF50; color: white; border: none; }
        .option-detail { margin-left: 20px; font-style: italic; color: #555; }
        .etape-title { font-weight: bold; margin-top: 15px; }
        .panier-notification {
            background-color: #DFF2BF;
            color: #4F8A10;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <img src="logo1.png" alt="Logo INFLYENCE" class="logo-img">INFLYENCE
            </div>
            <nav class="nav-links">
                <a href="index.php">Accueil</a>
                <a href="destination.php">Destinations</a>
                <a href="<?php echo isLoggedIn() ? 'panier.php' : 'connexion.php'; ?>">Mon Panier</a>
                <a href="profil.php">Profil</a>
                <a href="logout.php">Déconnexion</a>
                <div class="trigger-btn" onclick="changeDarkMode()" id="btn">
    <span>Mode sombre</span>
    <img src="mode.png">
</div>
            </nav>
        </div>
    </header>

    <section class="section recap-container">
        <div class="recap-header">
            <h1 class="recap-title">Récapitulatif de votre voyage</h1>
            <h2><?php echo $voyage['titre']; ?></h2>
        </div>

        <div class="panier-notification">
            Ce voyage a été ajouté à votre panier
        </div>

        <div class="recap-section">
            <h3 class="recap-section-title">Informations générales</h3>
            <div class="recap-item">
                <div class="recap-item-label">Destination:</div>
                <div><?php echo $voyage['destination']; ?></div>
            </div>
            <div class="recap-item">
                <div class="recap-item-label">Durée:</div>
                <div><?php echo $voyage['duree']; ?> jours</div>
            </div>
            <div class="recap-item">
                <div class="recap-item-label">Date de départ:</div>
                <div><?php echo $voyage['date_depart']; ?></div>
            </div>
            <div class="recap-item">
                <div class="recap-item-label">Prix de base:</div>
                <div><?php echo $voyage['prix']; ?>£</div>
            </div>
        </div>

        <div class="recap-section">
            <h3 class="recap-section-title">Options sélectionnées</h3>
            
            <?php foreach ($options_details as $etape_id => $etape): ?>
            <div class="etape-title"><?php echo $etape['titre']; ?></div>
            
            <?php if (isset($etape['options']['hebergement'])): ?>
                <div class="recap-item">
                    <div>Hébergement: <?php echo $etape['options']['hebergement']['titre']; ?></div>
                    <div>+<?php echo $etape['options']['hebergement']['prix']; ?>£</div>
                </div>
                <div class="option-detail"><?php echo $etape['options']['hebergement']['description']; ?></div>
            <?php endif; ?>
            
            <?php if (isset($etape['options']['restauration'])): ?>
                <div class="recap-item">
                    <div>Restauration: <?php echo $etape['options']['restauration']['titre']; ?></div>
                    <div>+<?php echo $etape['options']['restauration']['prix']; ?>£</div>
                </div>
                <div class="option-detail"><?php echo $etape['options']['restauration']['description']; ?></div>
            <?php endif; ?>
            
            <?php if (isset($etape['options']['activites'])): ?>
                <?php foreach ($etape['options']['activites'] as $activite): ?>
                <div class="recap-item">
                    <div>Activité: <?php echo $activite['option']['titre']; ?> (<?php echo $activite['nb_persons']; ?> pers.)</div>
                    <div>+<?php echo $activite['prix_total']; ?>£</div>
                </div>
                <div class="option-detail"><?php echo $activite['option']['description']; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if (isset($etape['options']['transport'])): ?>
                <div class="recap-item">
                    <div>Transport: <?php echo $etape['options']['transport']['titre']; ?></div>
                    <div>+<?php echo $etape['options']['transport']['prix']; ?>£</div>
                </div>
                <div class="option-detail"><?php echo $etape['options']['transport']['description']; ?></div>
            <?php endif; ?>
            
            <?php endforeach; ?>
        </div>

        <div class="recap-total">
            Total: <?php echo $prix_total; ?>£
        </div>

        <div class="action-buttons">
            <a href="voyage_detail.php?id=<?php echo $voyage_id; ?>" class="action-btn modify-btn">Modifier les options</a>
            <a href="paiement.php?voyage_id=<?php echo $voyage_id; ?>" class="action-btn payment-btn">Procéder au paiement</a>
        </div>
    </section>
</body>
<script src="script.js"></script>
</html>