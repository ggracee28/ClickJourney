<?php
require_once 'config.php';

// Fonction pour vérifier les identifiants
function verifyCredentials($login, $password) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    foreach ($users as $user) {
        if ($user['login'] === $login && $user['password'] === $password) {
            return $user;
        }
    }
    return false;
}

// Fonction pour ajouter un nouvel utilisateur
function addUser($userData) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    
    // Vérifier si le login existe déjà
    foreach ($users as $user) {
        if ($user['login'] === $userData['login']) {
            return false; // Login déjà utilisé
        }
    }
    
    // Générer un nouvel ID
    $maxId = 0;
    foreach ($users as $user) {
        if ($user['id'] > $maxId) {
            $maxId = $user['id'];
        }
    }
    
    // Ajouter le nouvel utilisateur
    $newUser = [
        'id' => $maxId + 1,
        'login' => $userData['login'],
        'password' => $userData['password'],
        'role' => 'normal',
        'nom' => $userData['nom'],
        'prenom' => $userData['prenom'],
        'email' => $userData['email'],
        'date_naissance' => $userData['date_naissance'],
        'pays_origine' => $userData['pays_origine'],
        'date_inscription' => date('Y-m-d'),
        'derniere_connexion' => date('Y-m-d'),
        'voyages_consultes' => [],
        'voyages_achetes' => []
    ];
    
    // Ajouter à la liste et enregistrer
    $users[] = $newUser;
    file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
    
    return $newUser;
}

// Fonction pour récupérer tous les utilisateurs
function getAllUsers() {
    return json_decode(file_get_contents(USER_DATA), true);
}

// Fonction pour mettre à jour la dernière connexion
function updateLastLogin($userId) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            $user['derniere_connexion'] = date('Y-m-d');
            break;
        }
    }
    file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
}
?>