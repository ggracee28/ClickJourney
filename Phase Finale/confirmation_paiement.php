<?php
// Vérification des paramètres dans l'URL (issus de l'interface de paiement)
if (isset($_GET['status']) && $_GET['status'] == 'accepted') {
    // Les paramètres nécessaires sont présents, affichons le récapitulatif du paiement
    $transactionId = isset($_GET['transaction']) ? htmlspecialchars($_GET['transaction']) : 'Inconnu';
    $montant = isset($_GET['montant']) ? htmlspecialchars($_GET['montant']) : '0.00';
    $vendeur = isset($_GET['vendeur']) ? htmlspecialchars($_GET['vendeur']) : 'Inconnu';

    echo "<h1>Confirmation de Paiement</h1>";
    echo "<p>Votre paiement a été <strong>validé</strong> avec succès !</p>";
    
    // Récapitulatif du paiement
    echo "<h2>Récapitulatif :</h2>";
    echo "<p><strong>Transaction ID :</strong> $transactionId</p>";
    echo "<p><strong>Montant :</strong> $montant €</p>";
    echo "<p><strong>Vendeur :</strong> $vendeur</p>";

    // Enregistrer le paiement dans un fichier JSON (ou une base de données)
    // Par exemple, ajouter au fichier voyages_achetes.json (vous pouvez adapter le chemin et le format)
    $paiement = [
        'transaction' => $transactionId,
        'montant' => $montant,
        'vendeur' => $vendeur,
        'status' => 'accepted',
        'date' => date('Y-m-d H:i:s')
    ];

    // Vérifier si le fichier existe
    $file = 'voyages_achetes.json';
    if (file_exists($file)) {
        // Lire le contenu existant
        $jsonData = file_get_contents($file);
        $paiements = json_decode($jsonData, true);
    } else {
        $paiements = [];
    }

    // Ajouter la nouvelle transaction au tableau
    $paiements[] = $paiement;

    // Sauvegarder les données mises à jour dans le fichier JSON
    file_put_contents($file, json_encode($paiements, JSON_PRETTY_PRINT));

    // Afficher un message de réussite
    echo "<p><strong>Le paiement a été enregistré avec succès dans notre système.</strong></p>";

    // Bouton pour retourner à l'accueil
    echo "<p><a href='index.php' class='button'>Retour à l'accueil</a></p>";

} else {
    // Si le statut de paiement n'est pas accepté ou si les paramètres sont absents
    echo "<h1>Erreur de Paiement</h1>";
    echo "<p>Le paiement a échoué ou des informations sont manquantes. Veuillez réessayer.</p>";
    echo "<p><a href='index.php' class='button'>Retour à l'accueil</a></p>";
}
?>
