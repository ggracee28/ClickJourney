<?php
require_once 'config.php';
require_once 'user_functions.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est un administrateur
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé.']);
    exit;
}

// Simuler une latence pour la démonstration
sleep(2); // Pause de 2 secondes pour toutes les actions admin pour voir l'icône

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['userId']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$userId = (int)$input['userId'];
$action = $input['action'];

$success = false;
$message = '';
$updatedValue = null;

if ($action === 'update_field' && isset($input['field']) && isset($input['value'])) {
    $field = $input['field'];
    $value = $input['value'];
    $allowedFields = ['nom', 'prenom', 'email', 'role']; // Champs modifiables par l'admin

    if (in_array($field, $allowedFields)) {
        // Validation spécifique si nécessaire (ex: email)
        if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $message = 'Format d\'email invalide.';
        } else {
            $updateData = [$field => $value];
            if (updateUserProfile($userId, $updateData)) {
                $success = true;
                $message = ucfirst($field) . ' mis à jour avec succès.';
                $updatedValue = $value; // Retourner la nouvelle valeur pour l'affichage
            } else {
                $message = 'Erreur lors de la mise à jour du champ ' . $field . '.';
            }
        }
    } else {
        $message = 'Champ non modifiable.';
    }
} elseif ($action === 'delete') {
    // Logique de suppression (assurez-vous que cette fonction existe et fonctionne)
    // if (deleteUser($userId)) { // Supposons une fonction deleteUser
    //     $success = true;
    //     $message = 'Utilisateur supprimé avec succès.';
    // } else {
    //     $message = 'Erreur lors de la suppression de l\'utilisateur.';
    // }
    // Pour la démo, on simule le succès
    $success = true;
    $message = 'Utilisateur (simulation) supprimé avec succès.';
} else {
    $message = 'Action inconnue.';
}

echo json_encode(['success' => $success, 'message' => $message, 'newValue' => $updatedValue]);
exit;
?>