<?php
require_once 'config.php';
require_once 'user_functions.php';

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$username = ''; // Variable pour stocker le nom d'utilisateur

// Gérer les messages d'erreur depuis l'URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'account_blocked':
            $error = 'Votre compte a été bloqué par un administrateur. Contactez le support pour plus d\'informations.';
            break;
        case 'not_logged_in':
            $error = 'Vous devez être connecté pour accéder à cette page.';
            break;
        case 'blocked_after_login':
            $error = 'Votre compte a été bloqué. Vous avez été déconnecté automatiquement.';
            break;
    }
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $login = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Stocker le nom d'utilisateur pour le pré-remplissage
    $username = $login;
    
    // Vérifier que les champs ne sont pas vides
    if (empty($login) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Vérifier les identifiants
        $user = verifyCredentials($login, $password);
        
        if ($user === 'blocked') {
            $error = 'Votre compte a été bloqué par un administrateur. Contactez le support pour plus d\'informations.';
        } elseif ($user === false) {
            $error = 'Identifiants incorrects. Vérifiez votre nom d\'utilisateur et mot de passe.';
        } else {
            // Double vérification du statut utilisateur avant de créer la session
            $userStatus = getUserStatus($user['id']);
            if ($userStatus === 'bloque' || (isset($user['role']) && $user['role'] === 'bloque')) {
                $error = 'Votre compte a été bloqué par un administrateur. Contactez le support pour plus d\'informations.';
            } else {
                // Enregistrer les informations dans la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_role'] = $user['role'] === 'bloque' ? 'normal' : $user['role']; // S'assurer que le rôle n'est pas 'bloque'
                $_SESSION['user_nom'] = $user['nom'] ?? '';
                $_SESSION['user_prenom'] = $user['prenom'] ?? '';
                
                // Mettre à jour la date de dernière connexion
                updateLastLogin($user['id']);
                
                // Rediriger vers la page demandée ou l'accueil par défaut
                $redirect_url = $_GET['redirect'] ?? 'index.php';
                redirect($redirect_url);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="connexion.css">
    
    <title>INFLYENCE - Connexion</title>
    <script src="https://kit.fontawesome.com/d669ac8659.js" crossorigin="anonymous"></script>
    <style>
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            font-weight: 500;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
            font-weight: 500;
            text-align: center;
        }
        .login-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        .input-box input:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0,123,255,0.3);
        }
    </style>
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

       <!--CONNEXION-->
       <div class="container">
    <section class="section" id="connexion">
        <h1>Connexion</h1>
        
        <?php if ($error): ?>
        <div class="error">
            <i class="fa-solid fa-exclamation-triangle" style="margin-right: 8px;"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['message']) && $_GET['message'] === 'unblocked'): ?>
        <div class="success">
            <i class="fa-solid fa-check-circle" style="margin-right: 8px;"></i>
            Votre compte a été débloqué. Vous pouvez maintenant vous connecter.
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="login-form">
            <div class="input-box">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Nom utilisateur" required maxlength="50" 
                       value="<?php echo htmlspecialchars($username); ?>" autocomplete="username">
            </div>
            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
            </div>
            
            <div class="remenber-forgot">
                <label><input type="checkbox" name="remember">Se souvenir de moi</label>
                <a href="#">Mot de Passe oublié ?</a>
            </div>
            <button type="submit" class="login-btn">
                <i class="fa-solid fa-sign-in-alt" style="margin-right: 8px;"></i>
                Se connecter
            </button>

            <div class="register-link">
                <p>Pas de compte ? <a href="inscription.php">Inscription</a></p>
            </div>
        </form>
    </section>
</div>

<!-- Script de validation des formulaires -->
<script src="form-validation.js"></script>
<script src="script.js"></script>

<script>
// Amélioration de l'UX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('login-form');
    const submitBtn = form.querySelector('.login-btn');
    const originalBtnText = submitBtn.innerHTML;
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i>Connexion...';
        submitBtn.disabled = true;
        
        // Réactiver le bouton après 5 secondes au cas où
        setTimeout(function() {
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }, 5000);
    });
    
    // Effacer les messages d'erreur quand l'utilisateur commence à taper
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const errorDiv = document.querySelector('.error');
            if (errorDiv) {
                errorDiv.style.opacity = '0.5';
            }
        });
    });
});
</script>

</body>
</html>