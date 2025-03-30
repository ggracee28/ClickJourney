<?php
require_once 'config.php';

/**
 * Récupère tous les voyages depuis le fichier JSON
 * @return array Tableau des voyages
 */
function getAllVoyages() {
    if (!file_exists(VOYAGE_DATA)) {
        return [];
    }

    $data = file_get_contents(VOYAGE_DATA);
    $voyages = json_decode($data, true);
    
    return is_array($voyages) ? $voyages : [];
}

/**
 * Récupère un voyage spécifique par son ID
 * @param int $id ID du voyage
 * @return array|null Données du voyage ou null si non trouvé
 */
function getVoyageById($id) {
    $voyages = getAllVoyages();
    
    foreach ($voyages as $voyage) {
        if (isset($voyage['id']) && $voyage['id'] == $id) {
            return $voyage;
        }
    }
    
    return null;
}

/**
 * Récupère une sélection aléatoire de voyages
 * @param int $limit Nombre de voyages à retourner (défaut: 3)
 * @return array Tableau des voyages sélectionnés
 */
function getRandomVoyages($limit = 3) {
    $voyages = getAllVoyages();
    
    if (empty($voyages)) {
        return [];
    }

    // Mélange le tableau et retourne le nombre demandé
    shuffle($voyages);
    return array_slice($voyages, 0, $limit);
}

/**
 * Récupère les voyages les mieux notés
 * @param int $limit Nombre de voyages à retourner (défaut: 3)
 * @return array Tableau des voyages triés par note
 */
function getTopRatedVoyages($limit = 3) {
    $voyages = getAllVoyages();
    
    // Filtre seulement les voyages avec une note
    $ratedVoyages = array_filter($voyages, function($voyage) {
        return isset($voyage['note_moyenne']) && is_numeric($voyage['note_moyenne']);
    });
    
    // Trie par note décroissante
    usort($ratedVoyages, function($a, $b) {
        return $b['note_moyenne'] <=> $a['note_moyenne'];
    });
    
    return array_slice($ratedVoyages, 0, $limit);
}

/**
 * Ajoute un voyage à l'historique des consultations de l'utilisateur
 * @param int $userId ID de l'utilisateur
 * @param int $voyageId ID du voyage consulté
 * @return bool True si mis à jour avec succès
 */
function addConsultedVoyage($userId, $voyageId) {
    if (!file_exists(USER_DATA)) return false;

    $users = json_decode(file_get_contents(USER_DATA), true);
    $updated = false;

    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            // Initialiser si non existant
            if (!isset($user['voyages_consultes']) || !is_array($user['voyages_consultes'])) {
                $user['voyages_consultes'] = [];
            }

            // Éviter les doublons
            $voyageIdStr = (string)$voyageId;
            $user['voyages_consultes'] = array_diff($user['voyages_consultes'], [$voyageIdStr]);
            
            // Ajouter au début
            array_unshift($user['voyages_consultes'], $voyageIdStr);
            
            // Garder seulement les 10 derniers
            $user['voyages_consultes'] = array_slice($user['voyages_consultes'], 0, 10);
            $updated = true;
            break;
        }
    }
  
    if ($updated) {
        file_put_contents(USER_DATA, json_encode($users, JSON_PRETTY_PRINT));
    }
    return $updated;
}

/**
 * Récupère les voyages consultés par un utilisateur
 * @param array $voyageIds Tableau des IDs de voyages
 * @return array Tableau des voyages correspondants
 */
function getConsultedVoyages($voyageIds) {
    if (empty($voyageIds) || !is_array($voyageIds)) {
        return [];
    }
    
    $voyages = getAllVoyages();
    $results = [];
    
    foreach ($voyageIds as $id) {
        foreach ($voyages as $voyage) {
            if (isset($voyage['id']) && $voyage['id'] == $id) {
                $results[] = $voyage;
                break;
            }
        }
    }
    
    return $results;
}

/**
 * Récupère les voyages les plus récents
 * @param int $limit Nombre de voyages à retourner (défaut: 3)
 * @return array Tableau des voyages triés par date
 */
function getRecentVoyages($limit = 3) {
    $voyages = getAllVoyages();
    
    usort($voyages, function($a, $b) {
        $dateA = strtotime($a['date_creation'] ?? '1970-01-01');
        $dateB = strtotime($b['date_creation'] ?? '1970-01-01');
        return $dateB - $dateA;
    });
    
    return array_slice($voyages, 0, $limit);
}

/**
 * Recherche des voyages par mot-clé
 * @param string $keyword Mot-clé de recherche
 * @return array Résultats de recherche
 */
function searchVoyages($keyword) {
    if (empty($keyword)) {
        return [];
    }
    
    $voyages = getAllVoyages();
    $results = [];
    
    foreach ($voyages as $voyage) {
        if (stripos($voyage['titre'] ?? '', $keyword) !== false || 
            stripos($voyage['description'] ?? '', $keyword) !== false) {
            $results[] = $voyage;
        }
    }
    
    return $results;
}