<?php
require_once 'config.php';

// Fonction pour vérifier les identifiants - MODIFIÉE
function verifyCredentials($login, $password) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    foreach ($users as $user) {
        if ($user['login'] === $login && $user['password'] === $password) {
            // Vérifier si l'utilisateur est bloqué (status = 'bloque' OU role = 'bloque')
            $status = $user['status'] ?? 'actif';
            $role = $user['role'] ?? 'normal';
            
            if ($status === 'bloque' || $role === 'bloque') {
                return 'blocked'; // Retourner un code spécial pour compte bloqué
            }
            return $user;
        }
    }
    return false;
}

// FONCTION CORRIGÉE : Bloquer un utilisateur
function blockUser($user_id) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    $updated = false;
    
    foreach ($users as &$user) {
        if ($user['id'] == $user_id) {
            // Mettre à jour le statut ET le rôle pour cohérence
            $user['status'] = 'bloque';
            $user['role'] = 'bloque'; // Pour cohérence avec vos données existantes
            $user['date_blocage'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        return file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
    }
    return false;
}

// FONCTION CORRIGÉE : Débloquer un utilisateur
function unblockUser($user_id) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    $updated = false;
    
    foreach ($users as &$user) {
        if ($user['id'] == $user_id) {
            // Remettre le statut à actif et le rôle à normal
            $user['status'] = 'actif';
            $user['role'] = 'normal'; // Ou garder le rôle précédent si vous le stockez
            unset($user['date_blocage']); // Supprimer la date de blocage
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        return file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
    }
    return false;
}

// FONCTION CORRIGÉE : Obtenir le statut d'un utilisateur
function getUserStatus($user_id) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    foreach ($users as $user) {
        if ($user['id'] == $user_id) {
            // Vérifier d'abord le status, puis le role pour rétrocompatibilité
            if (isset($user['status'])) {
                return $user['status'];
            } elseif (isset($user['role']) && ($user['role'] === 'bloque' || $user['role'] === 'bloqué')) {
                return 'bloque';
            } else {
                return 'actif';
            }
        }
    }
    return null;
}

// Fonction pour ajouter un nouvel utilisateur - MODIFIÉE
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
        'status' => 'actif',
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

// Fonction pour enregistrer un voyage acheté par un utilisateur
function addVoyageAchete($userId, $voyageId, $montant, $lienVoyage, $optionsDetails = []) {
    // Récupérer les utilisateurs
    $users = json_decode(file_get_contents(USER_DATA), true);
    
    // Trouver l'utilisateur par ID
    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            // Ajouter le voyage dans les voyages achetés
            $voyage = [
                'voyage_id' => $voyageId,
                'montant' => $montant,
                'date_achat' => date('Y-m-d H:i:s'),
                'lien_voyage' => $lienVoyage,
                'options_details' => $optionsDetails
            ];
            
            // Ajouter à l'historique des voyages achetés
            $user['voyages_achetes'][] = $voyage;
            
            // Sauvegarder les utilisateurs avec les informations mises à jour
            file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
            return true;
        }
    }
    
    return false;
}

// Fonction pour récupérer tous les utilisateurs
function getAllUsers() {
    return json_decode(file_get_contents(USER_DATA), true);
}

// FONCTION CORRIGÉE : Récupérer les utilisateurs par statut
function getUsersByStatus($status = 'all') {
    $users = json_decode(file_get_contents(USER_DATA), true);
    
    if ($status === 'all') {
        return $users;
    }
    
    return array_filter($users, function($user) use ($status) {
        // Vérifier d'abord le champ status, puis le role pour rétrocompatibilité
        if (isset($user['status'])) {
            return $user['status'] === $status;
        } elseif ($status === 'bloque' && isset($user['role'])) {
            return $user['role'] === 'bloque' || $user['role'] === 'bloqué';
        } else {
            return $status === 'actif'; // Par défaut considérer comme actif
        }
    });
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

// Fonction pour mettre à jour le profil d'un utilisateur
function updateUserProfile($userId, $dataToUpdate) {
    $users = json_decode(file_get_contents(USER_DATA), true);
    $userFound = false;

    foreach ($users as $key => $user) {
        if ($user['id'] == $userId) {
            foreach ($dataToUpdate as $field => $value) {
                if (array_key_exists($field, $users[$key])) {
                    $users[$key][$field] = $value;
                }
            }
            $userFound = true;
            break;
        }
    }

    if ($userFound) {
        if (file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT))) {
            return true;
        } else {
            error_log("Erreur lors de l'écriture dans users.json pour l'utilisateur ID: " . $userId);
            return false;
        }
    }
    error_log("Utilisateur non trouvé pour la mise à jour. ID: " . $userId);
    return false;
}
?>