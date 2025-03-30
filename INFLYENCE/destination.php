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
                <a href="<?php echo isLoggedIn() ? 'profil.php' : 'connexion.php'; ?>">Profil</a>
                <a href="<?php echo isAdmin() ? 'admin.php' : 'connexion.php'; ?>">Admin</a>
                <?php if (isLoggedIn()): ?>
                <a href="logout.php">Déconnexion</a>
                <?php else: ?>
                <a href="connexion.php">Connexion</a>
                <?php endif; ?>
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
                
                <select id="price-filter">
                    <option value="all">Tous les prix</option>
                    <option value="low">Moins de 4000£</option>
                    <option value="mid">4000£ - 5000£</option>
                    <option value="high">Plus de 5000£</option>
                </select>

                <select id="country-filter">
                    <option value="all">Tous les pays</option>
                    <option value="brésil">Brésil</option>
                    <option value="royaume-uni">Royaume-Uni</option>
                    <option value="usa">USA</option>
                    <option value="inde">Inde</option>
                    <option value="portugal">Portugal</option>
                    <option value="italie">Italie</option>
                    <option value="afrique du sud">Afrique du Sud</option>
                    <option value="maroc">Maroc</option>
                    <option value="allemagne">Allemagne</option>
                    <option value="espagne">Espagne</option>
                    <option value="suède">Suède</option>
                    <option value="france">France</option>
                </select>
            </div>
        </div>
        <div class="destination-grid">
            <?php foreach ($displayVoyages as $voyage): ?>
            <div class="destination-card">
                <div class="destination-img">
                    <img src="<?php echo $voyage['image']; ?>" alt="<?php echo $voyage['titre']; ?>">
                </div>
                <div class="destination-info">
                    <h3><?php echo $voyage['titre']; ?></h3>
                    <p><?php echo $voyage['description']; ?></p>
                    <div class="price">A partir de <?php echo $voyage['prix']; ?>£</div>
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
    
    <script>
        // Sélection des éléments
        const searchInput = document.querySelector('.search-input');
        const priceFilter = document.getElementById('price-filter');
        const countryFilter = document.getElementById('country-filter');
        const destinationCards = document.querySelectorAll('.destination-card');

        // Fonction qui extrait le nombre de la chaîne "A partir de 5000£"
        function extractPrice(priceText) {
            return parseInt(priceText.replace(/\D/g, ''), 10);
        }

        // Fonction qui filtre les cartes
        function filterCards() {
            const keyword = searchInput.value.toLowerCase();
            const selectedPrice = priceFilter.value;
            const selectedCountry = countryFilter.value.toLowerCase();

            destinationCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                const matchesKeyword = cardText.includes(keyword);

                const priceElement = card.querySelector('.price');
                const price = extractPrice(priceElement.textContent);

                let matchesPrice = false;
                if (selectedPrice === 'all') {
                    matchesPrice = true;
                } else if (selectedPrice === 'low') {
                    matchesPrice = (price < 4000);
                } else if (selectedPrice === 'mid') {
                    matchesPrice = (price >= 4000 && price < 5000);
                } else if (selectedPrice === 'high') {
                    matchesPrice = (price >= 5000);
                }

                const h3Element = card.querySelector('.destination-info h3');
                let country = "";
                if (h3Element) {
                    const parts = h3Element.textContent.split(',');
                    if (parts.length > 1) {
                        country = parts[1].trim().toLowerCase();
                    }
                }
                const matchesCountry = (selectedCountry === 'all' || country.includes(selectedCountry));

                if (matchesKeyword && matchesPrice && matchesCountry) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Ajout des écouteurs d'événements
        searchInput.addEventListener('input', filterCards);
        priceFilter.addEventListener('change', filterCards);
        countryFilter.addEventListener('change', filterCards);
    </script>
</body>
</html>