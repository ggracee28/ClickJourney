<?php
// Configuration globale
define('DATA_PATH', __DIR__ . '/data/');
define('USER_DATA', DATA_PATH . 'users/users.json');
define('VOYAGE_DATA', DATA_PATH . 'voyages/voyages.json');
define('ETAPE_DATA', DATA_PATH . 'etapes/etapes.json');
define('OPTION_DATA', DATA_PATH . 'options/options.json');
define('PAIEMENT_DATA', DATA_PATH . 'paiements/paiements.json');

// Démarrer la session
session_start();

// Fonction pour rediriger vers une page
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour vérifier si l'utilisateur est administrateur
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'administrateur';
}

// Fonction pour obtenir les données utilisateur
function getUserData($user_id) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            return $user;
        }
    }
    return null;
}
?>