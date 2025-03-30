<?php
require_once 'config.php';
require_once 'user_functions.php';

// Si l'utilisateur est déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des données
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = 'Tous les champs sont obligatoires';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } else {
        // Créer le nouvel utilisateur
        $userData = [
            'login' => $email, // Utilisation de l'email comme login
            'password' => $password,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'date_naissance' => '2000-01-01', // Valeur par défaut
            'pays_origine' => 'France', // Valeur par défaut
        ];
        
        $newUser = addUser($userData);
        if ($newUser) {
            $success = 'Compte créé avec succès';
            // Vous pouvez choisir de connecter automatiquement l'utilisateur ici
            // ou le laisser se connecter manuellement
        } else {
            $error = 'Login déjà utilisé';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="inscription.css">
    <title>INFLYENCE-Destination</title>
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
    <!--Inscriptions-->
    <div class="container">
        <section class="section" id="inscription">
            <h1>Inscription</h1>
            <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <div class="register-link">
                <p><a href="connexion.php">Se connecter maintenant</a></p>
            </div>
            <?php else: ?>
            <form method="POST" action="">
                <div class="input-box">
                    <input type="text" name="prenom" placeholder="Prénom" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                    <input type="text" name="nom" placeholder="Nom" required>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Mot de passe" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                    <i class="fa-solid fa-lock"></i>
                </div>
        
                <div class="remenber-forgot">
                    <label><input type="checkbox" required> Accepter les termes et conditions</label>
                </div>
                <button type="submit" class="login-btn">S'inscrire</button>
        
                <div class="register-link">
                    <p>Déjà un compte ? <a href="connexion.php">Connexion</a></p>
                </div>
            </form>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>