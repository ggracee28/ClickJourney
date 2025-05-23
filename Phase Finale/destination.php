<?php
require_once 'config.php';
require_once 'voyage_functions.php';

// Traitement de la recherche
$keyword = $_GET['search'] ?? '';
if (!empty($keyword)) {
    $voyages = searchVoyages($keyword);
} else {
    $voyages = getAllVoyages();
}

// Pagination
$voyagesPerPage = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalVoyages = count($voyages);
$totalPages = ceil($totalVoyages / $voyagesPerPage);
$page = max(1, min($page, $totalPages));
$start = ($page - 1) * $voyagesPerPage;
$displayVoyages = array_slice($voyages, $start, $voyagesPerPage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="destination.css">
    
    <title>INFLYENCE-Destination</title>
    <script src="https://kit.fontawesome.com/d669ac8659.js" crossorigin="anonymous"></script>
</head>
<body>
<!-- HEADER MODIFIÉ -->
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
            <a href="<?php echo isLoggedIn() ? 'profil.php' : 'connexion.php?redirect=' . urlencode('profil.php'); ?>">Profil</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php">Admin</a>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <a href="logout.php">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Connexion</a>
            <?php endif; ?>
            <div class="trigger-btn" onclick="changeDarkMode()" id="btn">
                <span>Mode sombre</span>
                <img src="mode.png">
            </div>
        </nav>
    </div>
</header>
  
    <section class="section" id="destinations">
        <div class="section-title">
            <h2>Choisissez votre légende. Vivez son histoire.</h2>
            <p>Découvrez nos voyages</p>
            <div class="search-box">
                <form action="destination.php" method="POST">
                    <input type="text" name="search" placeholder="Recherchez une destination..." class="search-input" value="<?php echo htmlspecialchars($keyword); ?>">
                    <button type="submit" class="search-btn"><i class="fa fa-search"></i></button>
                </form>
                
                <div class="filter-row">
                    <select id="price-filter">
                        <option value="all">Tous les prix</option>
                        <option value="low">Moins de 4000£</option>
                        <option value="mid">4000£ - 5000£</option>
                        <option value="high">Plus de 5000£</option>
                    </select>

                    <select id="duration-filter">
                        <option value="all">Toutes les durées</option>
                        <option value="short">Moins de 7 jours</option>
                        <option value="medium">7 à 14 jours</option>
                        <option value="long">Plus de 14 jours</option>
                    </select>

                    <select id="continent-filter">
                        <option value="all">Tous les continents</option>
                        <option value="europe">Europe</option>
                        <option value="asie">Asie</option>
                        <option value="afrique">Afrique</option>
                        <option value="amerique">Amérique</option>
                        <option value="oceanie">Océanie</option>
                    </select>
                </div>
                
                <div class="sort-row">
                    <select id="sort-by">
                        <option value="none">Sans tri</option>
                        <option value="prix-asc">Prix croissant</option>
                        <option value="prix-desc">Prix décroissant</option>
                        <option value="duree-asc">Durée croissante</option>
                        <option value="duree-desc">Durée décroissante</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="destination-grid">
            <?php foreach ($displayVoyages as $voyage): ?>
            <?php 
                $pays = '';
                if (isset($voyage['titre'])) {
                    $parts = explode(',', $voyage['titre']);
                    if (count($parts) > 1) {
                        $pays = trim($parts[1]);
                    }
                }
            ?>
            <div class="destination-card" 
                 data-price="<?php echo $voyage['prix']; ?>" 
                 data-duree="<?php echo $voyage['duree']; ?>" 
                 data-pays="<?php echo strtolower($pays); ?>"
                 data-etapes="<?php echo isset($voyage['etapes']) ? count($voyage['etapes']) : 0; ?>">
                <div class="destination-img">
                    <img src="<?php echo $voyage['image']; ?>" alt="<?php echo $voyage['titre']; ?>">
                </div>
                <div class="destination-info">
                    <h3><?php echo $voyage['titre']; ?></h3>
                    <p><?php echo $voyage['description']; ?></p>
                    <div class="price">À partir de <?php echo $voyage['prix']; ?>£</div>
                    <a href="<?php echo isLoggedIn() ? 'voyage_detail.php?id='.$voyage['id'] : 'connexion.php?redirect=' . urlencode('destination.php'); ?>" class="view-btn">Voir détails</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($keyword); ?>">&laquo;</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($keyword); ?>" <?php echo $i === $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($keyword); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
    
    <script src="voyage_filter.js"></script>
    <script src="script.js"></script>
</body>
</html>