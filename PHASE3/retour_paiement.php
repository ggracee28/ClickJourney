<?php
session_start();
require_once 'config.php';

// Vérifier le résultat du paiement
if (isset($_GET['resultat']) && $_GET['resultat'] === 'success') {
    // Paiement réussi - enregistrer la transaction
    $transaction = $_SESSION['transaction'] ?? null;
    $commande = $_SESSION['commande'] ?? null;
    
    if ($transaction && $commande) {
        // Enregistrement dans paiements.json
        $paiement = [
            'id' => $transaction['id'],
            'user_id' => $_SESSION['user_id'],
            'voyage_id' => $commande['voyage_id'],
            'montant' => $transaction['montant'],
            'date' => $transaction['date'],
            'status' => 'completed'
        ];
        
        // Ajouter à l'historique des paiements
        $paiements = file_exists(PAIEMENT_DATA) ? json_decode(file_get_contents(PAIEMENT_DATA), true) : [];
        $paiements[] = $paiement;
        file_put_contents(PAIEMENT_DATA, json_encode($paiements, JSON_PRETTY_PRINT));
        
        // Redirection vers confirmation
        header('Location: confirmation_paiement.php');
        exit;
    }
}

// Si échec ou paramètres manquants
header('Location: erreur_paiement.php');
exit;
?>