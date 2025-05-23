<?php
session_start();
require_once 'config.php';
require_once 'user_functions.php';

// Vérifier le résultat du paiement
if (isset($_GET['status']) && $_GET['status'] === 'accepted') {
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

        // Ajouter à l'historique des paiements (paiements.json)
        $paiements = file_exists(PAIEMENT_DATA) ? json_decode(file_get_contents(PAIEMENT_DATA), true) : [];
        $paiements[] = $paiement;
        file_put_contents(PAIEMENT_DATA, json_encode($paiements, JSON_PRETTY_PRINT));

        // Ajouter le voyage acheté dans le fichier de l'utilisateur
        $userId = $_SESSION['user_id'];
        $voyageId = $commande['voyage_id'];
        $montant = $transaction['montant'];
        $lienVoyage = $commande['lien_voyage']; // Assurez-vous que ce champ existe

        // Appeler la fonction pour enregistrer le voyage acheté
        $result = addVoyageAchete($userId, $voyageId, $montant, $lienVoyage, $commande['options_details']);

        if ($result) {
            // Affichage du message de réussite
            echo "<h1>Paiement réussi !</h1>";
            echo "<p>Votre paiement a été validé avec succès.</p>";
            echo "<p><strong>Transaction ID :</strong> " . htmlspecialchars($transaction['id']) . "</p>";
            echo "<p><strong>Montant :</strong> " . htmlspecialchars($transaction['montant']) . " €</p>";
            echo "<p><strong>Voyage acheté :</strong> <a href='" . htmlspecialchars($commande['lien_voyage']) . "' target='_blank'>Voir votre voyage</a></p>";

            // Afficher le récapitulatif des options
            if (!empty($commande['options_details'])) {
                echo "<h3>Détails de votre voyage :</h3>";
                foreach ($commande['options_details'] as $etape_id => $etape) {
                    echo "<h4>" . htmlspecialchars($etape['titre']) . "</h4>";
                    if (isset($etape['options']['hebergement'])) {
                        echo "<p>Hébergement : " . htmlspecialchars($etape['options']['hebergement']['description']) . "</p>";
                    }
                    if (isset($etape['options']['restauration'])) {
                        echo "<p>Restauration : " . htmlspecialchars($etape['options']['restauration']['description']) . "</p>";
                    }
                    if (isset($etape['options']['activites'])) {
                        echo "<p>Activités : ";
                        $activites_descriptions = [];
                        foreach ($etape['options']['activites'] as $activite) {
                            $activites_descriptions[] = htmlspecialchars($activite['description']);
                        }
                        echo implode(', ', $activites_descriptions) . "</p>";
                    }
                    if (isset($etape['options']['transport'])) {
                        echo "<p>Transport : " . htmlspecialchars($etape['options']['transport']['description']) . "</p>";
                    }
                }
            }

            echo "<p><a href='index.php' class='button'>Retour à l'accueil</a></p>";
        } else {
            echo "<h1>Erreur d'enregistrement</h1>";
            echo "<p>Il y a eu un problème lors de l'enregistrement du voyage acheté. Veuillez réessayer.</p>";
            echo "<p><a href='index.php' class='button'>Retour à l'accueil</a></p>";
        }

    } else {
        // Si transaction ou commande manquante
        echo "<h1>Erreur de Paiement</h1>";
        echo "<p>Des informations nécessaires à la transaction sont manquantes. Veuillez réessayer plus tard.</p>";
        echo "<p><a href='index.php' class='button'>Retour à l'accueil</a></p>";
    }
} else {
    // Si statut de paiement non accepté ou paramètres manquants
    echo "<h1>Erreur de Paiement</h1>";
    echo "<p>Le paiement a échoué. Veuillez vérifier vos informations et réessayer.</p>";
    echo "<p><a href='index.php' class='button'>Retour à l'accueil</a></p>";
}
?>
