<?php
// Vérifier si les paramètres nécessaires sont passés dans l'URL
if (isset($_GET['status']) && $_GET['status'] == 'denied') {
    // L'affichage d'un message d'erreur si le paiement a été refusé
    echo "<h1>Erreur de Paiement</h1>";
    echo "<p>Votre paiement a été refusé. Veuillez vérifier les informations de votre carte bancaire et réessayer.</p>";
    
    // Afficher le message d'erreur avec les détails si disponibles
    if (isset($_GET['transaction'])) {
        echo "<p>Transaction ID: " . htmlspecialchars($_GET['transaction']) . "</p>";
    }
    if (isset($_GET['montant'])) {
        echo "<p>Montant de la transaction: " . htmlspecialchars($_GET['montant']) . "€</p>";
    }

    // Enregistrer l'erreur dans un fichier journal pour une analyse ultérieure
    $logFile = 'paiement_erreurs.log'; // Log file path
    $logMessage = "Erreur: Paiement refusé, Transaction ID: " . $_GET['transaction'] . " Montant: " . $_GET['montant'] . " Statut: " . $_GET['status'] . " Date: " . date('Y-m-d H:i:s') . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Rediriger vers la page d'accueil ou une autre page pertinente
    echo "<p><a href='index.php'>Retour à la boutique</a></p>";
} else {
    // Si les paramètres ne sont pas présents ou sont incorrects
    echo "<h1>Erreur</h1>";
    echo "<p>Une erreur inconnue s'est produite lors du paiement. Veuillez réessayer.</p>";
    echo "<p><a href='index.php'>Retour à la boutique</a></p>";
}
?>
