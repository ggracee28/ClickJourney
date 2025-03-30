<?php
require_once 'config.php';
require_once 'user_functions.php';

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Vérifier les identifiants
    $user = verifyCredentials($login, $password);
    if ($user) {
        // Enregistrer les informations dans la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_role'] = $user['role'];
        
        // Mettre à jour la date de dernière connexion
        updateLastLogin($user['id']);
        
        // Rediriger vers la page demandée ou l'accueil par défaut
        $redirect_url = $_GET['redirect'] ?? 'index.php';
        redirect($redirect_url);
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="connexion.css">
    <title>INFLYENCE-Connexion</title>
    <script src="https://kit.fontawesome.com/d669ac8659.js" crossorigin="anonymous"></script>
</head>
<body>
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
                <a href="profil.php">Profil</a>
                <a href="admin.php">Admin</a>
                <a href="connexion.php">Connexion</a>
            </nav>
        </div>
    </header>
       <!--CONNEXION-->
       <div class="container">
    <section class="section" id="connexion">
        <h1>Connexion</h1>
        <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-box">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Nom utilisateur" required>
            </div>
            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            
            <div class="remenber-forgot">
                <label><input type="checkbox">Se souvenir de moi</label>
                <a href="#">Mot de Passe oublié ?</a>
            </div>
            <button type="submit" class="login-btn">Se connecter</button>

            <div class="register-link">
                <p>Pas de compte ? <a href="inscription.php">Inscription</a></p>
            </div>
        </form>
    </section>
</div>
</body>
</html>