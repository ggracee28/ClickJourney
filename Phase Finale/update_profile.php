<?php
require_once 'config.php';
require_once 'user_functions.php';

// Simuler une latence pour la démonstration (uniquement pour l\'admin)
if (isset($_POST['id']) && isAdmin()) { // Supposons que l\'admin envoie un \'id\' pour l\'utilisateur cible
    sleep(2); // Pause de 2 secondes
}

header('Content-Type: application/json'); // Toujours retourner du JSON

// Vérifier si l\'utilisateur est connecté
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_user_id = $_SESSION['user_id'];
    $target_user_id = $current_user_id;
    $isAdminEditing = false;

    // Si l\'utilisateur est un admin et modifie un autre profil (via `id` dans POST ou GET)
    if (isAdmin()) {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $target_user_id = (int)$_POST['id'];
            $isAdminEditing = true;
        } elseif (isset($_GET['id']) && !empty($_GET['id'])) { // Fallback pour GET si l\'ID est dans l\'URL
            $target_user_id = (int)$_GET['id'];
            $isAdminEditing = true;
        }
    }

    $userData = getUserData($target_user_id);

    if (!$userData) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit;
    }

    $updatedData = [];
    // $responseMessage = \'\'; // Cette ligne n'est plus nécessaire

    // Récupérer le nom du champ à mettre à jour
    // Cela suppose que le JavaScript envoie une seule paire clé/valeur à la fois
    $fieldToUpdate = null;
    $newValue = null;

    foreach ($_POST as $key => $value) {
        if ($key !== 'id') { // Exclure l\'identifiant de l\'utilisateur cible des champs à mettre à jour
            $fieldToUpdate = $key;
            $newValue = $value;
            break; // On ne traite qu\'un seul champ à la fois pour cette logique
        }
    }

    if (!$fieldToUpdate) {
        echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour spécifié.']);
        exit;
    }

    // Valider et préparer la donnée pour le champ spécifique
    switch ($fieldToUpdate) {
        case 'nom':
        case 'prenom':
        case 'pays_origine':
            if (!empty($newValue)) {
                $updatedData[$fieldToUpdate] = $newValue;
            } else {
                echo json_encode(['success' => false, 'message' => ucfirst($fieldToUpdate) . ' ne peut pas être vide.']);
                exit;
            }
            break;
        case 'email':
            if (!empty($newValue)) {
                if (!filter_var($newValue, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(['success' => false, 'message' => 'Format d\'email invalide.']);
                    exit;
                }
                $updatedData['email'] = $newValue;
            } else {
                echo json_encode(['success' => false, 'message' => 'Email ne peut pas être vide.']);
                exit;
            }
            break;
        case 'password':
            if (!empty($newValue)) {
                $updatedData['password'] = password_hash($newValue, PASSWORD_DEFAULT);
            } else {
                // Si le champ mot de passe est envoyé vide, on considère que l\'utilisateur ne veut pas le changer.
                // On renvoie un succès pour ne pas bloquer l\'UI, mais sans faire de mise à jour.
                 echo json_encode(['success' => true, 'message' => 'Mot de passe non modifié.', 'field' => $fieldToUpdate, 'newValue' => '********', 'isAdminEditing' => $isAdminEditing]);
                 exit;
            }
            break;
        case 'date_naissance':
            if (!empty($newValue)) {
                $date = DateTime::createFromFormat('Y-m-d', $newValue);
                if (!$date || $date->format('Y-m-d') !== $newValue) {
                    echo json_encode(['success' => false, 'message' => 'Format de date invalide. Utilisez AAAA-MM-JJ.']);
                    exit;
                }
                $updatedData['date_naissance'] = $newValue;
            } else {
                echo json_encode(['success' => false, 'message' => 'La date de naissance ne peut pas être vide.']);
                exit;
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Champ inconnu: ' . htmlspecialchars($fieldToUpdate)]);
            exit;
    }

    if (!empty($updatedData)) {
        if (updateUserProfile($target_user_id, $updatedData)) {
            $displayValue = $newValue;
            if ($fieldToUpdate === 'date_naissance') {
                $dateObj = DateTime::createFromFormat('Y-m-d', $newValue);
                if ($dateObj) {
                    $displayValue = $dateObj->format('d/m/Y');
                }
            }
            echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès.', 'field' => $fieldToUpdate, 'newValue' => $displayValue, 'isAdminEditing' => $isAdminEditing]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour du profil.', 'isAdminEditing' => $isAdminEditing]);
        }
    } else {
        // Ce cas ne devrait plus être atteint si la logique du mot de passe vide est gérée ci-dessus.
        // Mais par sécurité, on garde un message.
        echo json_encode(['success' => false, 'message' => 'Aucune modification valide n\'a été fournie.', 'isAdminEditing' => $isAdminEditing]);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

?>