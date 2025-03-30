<?php 
require_once 'config.php';
require_once 'voyage_functions.php';

$randomVoyages = getRandomVoyages(3);
$topRatedVoyages = getTopRatedVoyages(3);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>INFLUENCE-Accueil</title>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">
               <img src="logo1.png" alt="Logo INFLYENCE" class="logo-img">INFLYENCE</div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav class="nav-links">
    <a href="index.php">Accueil</a>
    <a href="destination.php">Destinations</a>
    <a href="<?php echo isLoggedIn() ? 'profil.php' : 'connexion.php?redirect=' . urlencode('profil.php'); ?>">Profil</a>
    <a href="<?php echo isAdmin() ? 'admin.php' : (isLoggedIn() ? 'profil.php' : 'connexion.php?redirect=' . urlencode('admin.php')); ?>">Admin</a>
    <?php if (isLoggedIn()): ?>
    <a href="logout.php">Déconnexion</a>
    <?php else: ?>
    <a href="connexion.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">Connexion</a>
    <?php endif; ?>
</nav>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <h1>DEVENEZ UNE ICONE, VIVEZ LA VIE DUNE LEGENDE</h1>
            <p>Avec INFLYENCE, plongez dans la vie des personnalités qui ont marqué le monde.</p>    
            <h2>Choisissez votre légende. Vivez son histoire. Écrivez la vôtre.</h2>
        </div>
    </section>

    <!-- SECTIONS VOYAGES -->
    <div class="home-sections">
        <!-- Section Aléatoire -->
        <section class="voyage-section">
            <div class="section-header">
                <h2>Nos suggestions du moment</h2>
                <p>Découvrez une sélection aléatoire</p>
            </div>
            <div class="voyage-cards">
                <?php foreach ($randomVoyages as $voyage): ?>
                <div class="voyage-card">
                    <img src="<?= htmlspecialchars($voyage['image']) ?>" alt="<?= htmlspecialchars($voyage['titre']) ?>">
                    <div class="voyage-content">
                        <h3><?= htmlspecialchars($voyage['titre']) ?></h3>
                        <p><?= substr(htmlspecialchars($voyage['description']), 0, 100) ?>...</p>
                        <div class="voyage-price">À partir de <?= htmlspecialchars($voyage['prix']) ?>€</div>
                        <a href="<?php echo isLoggedIn() ? 'voyage_detail.php?id='.$voyage['id'] : 'connexion.php'; ?>" class="discover-btn">Découvrir</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Section Mieux Notés -->
        <section class="voyage-section">
            <div class="section-header">
                <h2>Nos voyages les mieux notés</h2>
                <p>Ce que nos voyageurs préfèrent</p>
            </div>
            <div class="voyage-cards">
                <?php foreach ($topRatedVoyages as $voyage): ?>
                <div class="voyage-card">
                    <img src="<?= htmlspecialchars($voyage['image']) ?>" alt="<?= htmlspecialchars($voyage['titre']) ?>">
                    <div class="voyage-content">
                        <h3><?= htmlspecialchars($voyage['titre']) ?></h3>
                        <div class="voyage-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= ($voyage['note_moyenne'] ?? 0) ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                            <span>(<?= $voyage['note_moyenne'] ?? '0' ?>/5)</span>
                        </div>
                        <p><?= substr(htmlspecialchars($voyage['description']), 0, 100) ?>...</p>
                        <div class="voyage-price">À partir de <?= htmlspecialchars($voyage['prix']) ?>€</div>
                        <a href="<?php echo isLoggedIn() ? 'voyage_detail.php?id='.$voyage['id'] : 'connexion.php'; ?>" class="discover-btn">Découvrir</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>

    <!-- FOOTER -->
    <?php if (file_exists('footer.php')) include 'footer.php'; ?>
</body>
</html>