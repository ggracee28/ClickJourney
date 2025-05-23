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

// FONCTION CORRIGÉE : Vérifier si l'utilisateur est bloqué
function isUserBlocked($user_id = null) {
    if ($user_id === null) {
        $user_id = $_SESSION['user_id'] ?? null;
    }
    
    if (!$user_id) return false;
    
    $users = json_decode(file_get_contents(USER_DATA), true);
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            // Vérifier d'abord le status, puis le role pour rétrocompatibilité
            if (isset($user['status'])) {
                return $user['status'] === 'bloque';
            } elseif (isset($user['role'])) {
                return $user['role'] === 'bloque' || $user['role'] === 'bloqué';
            }
        }
    }
    return false;
}

// FONCTION CORRIGÉE : Vérifier l'accès utilisateur (connecté ET non bloqué)
function hasValidAccess() {
    return isLoggedIn() && !isUserBlocked();
}

// FONCTION CORRIGÉE : Middleware de vérification d'accès
function checkUserAccess($redirect_to_login = true) {
    if (!isLoggedIn()) {
        if ($redirect_to_login) {
            redirect('connexion.php');
        }
        return false;
    }
    
    if (isUserBlocked()) {
        // Détruire la session de l'utilisateur bloqué
        session_destroy();
        session_start();
        if ($redirect_to_login) {
            redirect('connexion.php?error=account_blocked');
        }
        return false;
    }
    
    return true;
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