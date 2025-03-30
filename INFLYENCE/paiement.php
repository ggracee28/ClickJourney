<?php
session_start();
require_once 'config.php';
require_once 'getapikey.php';
require_once 'voyage_functions.php';

// Vérifier si une commande est en session
if (!isset($_SESSION['commande'])) {
    header('Location: destination.php');
    exit;
}

$commande = $_SESSION['commande'];
$voyage = getVoyageById($commande['voyage_id']);

// Configuration CyBank avec le prix dynamique
$vendeur = "MEF-1_G";
$api_key = getAPIKey($vendeur);
$transaction = uniqid();
$montant = $commande['prix_total']; // Prix dynamique de la commande
$retour = "http://".$_SERVER['HTTP_HOST']."/retour_paiement.php";
$control = md5($api_key."#".$transaction."#".$montant."#".$vendeur."#".$retour."#");

// Stocker les infos de transaction en session
$_SESSION['transaction'] = [
    'id' => $transaction,
    'montant' => $montant,
    'date' => date('Y-m-d H:i:s')
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redirection vers CyBank</title>
    <style>
        .loading {
            text-align: center;
            padding: 50px;
            font-size: 1.2em;
        }
        .recap-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .recap-title {
            font-size: 1.5em;
            margin-bottom: 15px;
            color: #333;
            text-align: center;
        }
        .recap-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #eee;
        }
        .recap-label {
            font-weight: bold;
        }
        .recap-total {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 2px solid #333;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="recap-container">
        <h3 class="recap-title">Récapitulatif de votre commande</h3>
        
        <div class="recap-item">
            <span class="recap-label">Voyage:</span>
            <span><?php echo htmlspecialchars($commande['titre']); ?></span>
        </div>
        
        <div class="recap-item">
            <span class="recap-label">Date de départ:</span>
            <span><?php echo htmlspecialchars($commande['date_depart']); ?></span>
        </div>
        
        <div class="recap-item">
            <span class="recap-label">Durée:</span>
            <span><?php echo htmlspecialchars($commande['duree']); ?> jours</span>
        </div>
        
        <div class="recap-item">
            <span class="recap-label">Prix total:</span>
            <span><?php echo htmlspecialchars($commande['prix_total']); ?>£</span>
        </div>
    </div>

    <div class="loading">
        <p>Redirection vers le paiement sécurisé...</p>
        <p>Veuillez patienter</p>
    </div>

    <!-- Formulaire invisible qui se soumet automatiquement -->
    <form id="cybankForm" action="https://www.plateforme-smc.fr/cybank/index.php" method="post">
        <input type="hidden" name="transaction" value="<?= $transaction ?>">
        <input type="hidden" name="montant" value="<?= $montant ?>">
        <input type="hidden" name="vendeur" value="<?= $vendeur ?>">
        <input type="hidden" name="retour" value="<?= $retour ?>">
        <input type="hidden" name="control" value="<?= $control ?>">
    </form>

    <script>
        // Soumission automatique du formulaire après 3 secondes pour laisser le temps de voir le récapitulatif
        setTimeout(function() {
            document.getElementById('cybankForm').submit();
        }, 3000);
    </script>
</body>
</html>