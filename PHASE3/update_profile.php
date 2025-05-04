<?php
require_once 'config.php';
require_once 'user_functions.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    header('Location: connexion.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user_id = $_SESSION['user_id'];
    $target_user_id = $current_user_id;
    
    // Si l'utilisateur est un admin et modifie un autre profil
    if (isAdmin() && isset($_GET['id'])) {
        $target_user_id = (int)$_GET['id'];
    }
    
    // Récupérer les données actuelles de l'utilisateur
    $userData = getUserData($target_user_id);
    
    if (!$userData) {
        $_SESSION['error'] = "Utilisateur introuvable.";
        header('Location: profil.php');
        exit;
    }
    
    // Préparer un tableau pour les données mises à jour
    $updatedData = [];
    
    // Vérifier et traiter chaque champ possible
    if (isset($_POST['nom']) && !empty($_POST['nom'])) {
        $updatedData['nom'] = $_POST['nom'];
    }
    
    if (isset($_POST['prenom']) && !empty($_POST['prenom'])) {
        $updatedData['prenom'] = $_POST['prenom'];
    }
    
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        // Validation de l'email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Format d'email invalide.";
            header('Location: profil.php');
            exit;
        }
        $updatedData['email'] = $_POST['email'];
    }
    
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        // Hachage du mot de passe
        $updatedData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    
    if (isset($_POST['date_naissance']) && !empty($_POST['date_naissance'])) {
        // Validation de la date
        $date = DateTime::createFromFormat('Y-m-d', $_POST['date_naissance']);
        if (!$date || $date->format('Y-m-d') !== $_POST['date_naissance']) {
            $_SESSION['error'] = "Format de date invalide.";
            header('Location: profil.php');
            exit;
        }
        $updatedData['date_naissance'] = $_POST['date_naissance'];
    }
    
    if (isset($_POST['pays_origine']) && !empty($_POST['pays_origine'])) {
        $updatedData['pays_origine'] = $_POST['pays_origine'];
    }
    
    // Si des données ont été modifiées, mettre à jour le profil
    if (!empty($updatedData)) {
        if (updateUserProfile($target_user_id, $updatedData)) {
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
        }
    } else {
        $_SESSION['info'] = "Aucune modification n'a été effectuée.";
    }
    
    // Rediriger vers la page de profil
    header('Location: profil.php' . (isset($_GET['id']) ? '?id=' . $_GET['id'] : ''));
    exit;
} else {
    // Si la page est accédée directement sans soumission de formulaire
    header('Location: profil.php');
    exit;
}

/**
 * Met à jour le profil d'un utilisateur dans la base de données
 * 
 * @param int $userId ID de l'utilisateur
 * @param array $data Données à mettre à jour
 * @return bool Succès ou échec de la mise à jour
 */
function updateUserProfile($userId, $data) {
    global $db;
    
    try {
        // Construire la requête SQL dynamiquement
        $sql = "UPDATE users SET ";
        $params = [];
        $updateFields = [];
        
        foreach ($data as $field => $value) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $value;
        }
        
        $sql .= implode(', ', $updateFields);
        $sql .= " WHERE id = :user_id";
        $params[':user_id'] = $userId;
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        // Gérer l'erreur (log, etc.)
        error_log("Erreur de mise à jour du profil: " . $e->getMessage());
        return false;
    }
}