<?php
require_once 'config.php';
require_once 'voyage_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('connexion.php');
}

// Récupérer les voyages consultés par l'utilisateur
$userId = $_SESSION['user_id'];
$users = json_decode(file_get_contents(USER_DATA), true);
$voyagesConsultes = [];

foreach ($users as $user) {
    if ($user['id'] == $userId) {
        $voyagesConsultes = $user['voyages_consultes'] ?? [];
        break;
    }
}

// Récupérer les informations des voyages consultés
$voyages = getConsultedVoyages($voyagesConsultes);

// Gérer la suppression d'un voyage du panier
if (isset($_GET['remove_id'])) {
    $removeId = $_GET['remove_id'];
    
    // Supprimer le voyage du tableau des voyages consultés
    $voyagesConsultes = array_diff($voyagesConsultes, [$removeId]);
    
    // Mettre à jour les données de l'utilisateur
    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            $user['voyages_consultes'] = array_values($voyagesConsultes);
            break;
        }
    }
    
    file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
    
    // Rediriger pour ne pas réafficher le formulaire de suppression
    redirect('panier.php');
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    
    <link rel="stylesheet" href="panier.css"> <!-- Ajouter le CSS personnalisé pour le panier -->
    <title>INFLYENCE - Mon Panier</title>
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
                <a href="<?php echo isLoggedIn() ? 'profil.php' : 'connexion.php'; ?>">Profil</a>
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

    <section class="section">
        <div class="section-title">
            <h2>Votre Panier</h2>
            <p>Voici votre panier de voyages. Vous pouvez les voir en détails ou les retirer de votre panier.</p>
        </div>

        <?php if (empty($voyages)): ?>
            <p class="panier-empty">Votre panier est vide.</p>
        <?php else: ?>
            <div class="panier-list">
                <?php foreach ($voyages as $voyage): ?>
                    <div class="voyage-item">
                        <div class="voyage-image">
                            <img src="<?php echo $voyage['image']; ?>" alt="<?php echo $voyage['titre']; ?>">
                        </div>
                        <div class="voyage-info">
                            <h2><?php echo $voyage['titre']; ?></h2>
                            <p><?php echo $voyage['description']; ?></p>
                            <div class="price">À partir de <?php echo $voyage['prix']; ?>£</div>
                            
                            <div class="voyage-actions">
                                <a href="voyage_detail.php?id=<?php echo $voyage['id']; ?>" class="view-btn">Voir le voyage</a>
                                <a href="panier.php?remove_id=<?php echo $voyage['id']; ?>" class="remove-btn">Enlever du panier</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination si nécessaire -->
            <?php if (count($voyages) > 9): ?>
                <div class="pagination">
                    <!-- Logique de pagination ici si nécessaire -->
                    <a href="?page=<?php echo $page - 1; ?>" class="page-link">&laquo; Précédent</a>
                    <a href="?page=<?php echo $page + 1; ?>" class="page-link">Suivant &raquo;</a>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </section>

</body>
<script src="script.js"></script>
</html>