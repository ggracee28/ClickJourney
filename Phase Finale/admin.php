<?php
require_once 'config.php';
require_once 'user_functions.php';


// Vérifier l'accès utilisateur ET si c'est un admin
checkUserAccess(); // Vérifier que l'utilisateur n'est pas bloqué
if (!isAdmin()) {
    redirect('connexion.php');
}

// Traitement des actions AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'block_user':
            $user_id = (int)$_POST['user_id'];
            $result = blockUser($user_id);
            echo json_encode(['success' => $result]);
            exit;
            
        case 'unblock_user':
            $user_id = (int)$_POST['user_id'];
            $result = unblockUser($user_id);
            echo json_encode(['success' => $result]);
            exit;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Action non reconnue']);
            exit;
    }
}

// Récupérer tous les utilisateurs
$filter = $_GET['filter'] ?? 'all';
$users = ($filter === 'all') ? getAllUsers() : getUsersByStatus($filter);

// Pagination
$usersPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalUsers = count($users);
$totalPages = ceil($totalUsers / $usersPerPage);
$page = max(1, min($page, $totalPages));
$start = ($page - 1) * $usersPerPage;
$displayUsers = array_slice($users, $start, $usersPerPage);

// Définir les rôles disponibles
$availableRoles = ['Normal', 'Utilisateur', 'VIP', 'Administrateur'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="admin-update.css">
  <link rel="stylesheet" href="admin-dark.css">
 
  <title>INFLYENCE - Administration</title>
  <script src="https://kit.fontawesome.com/d669ac8659.js" crossorigin="anonymous"></script>
  <style>
    .pagination {
      margin-top: 20px;
      text-align: center;
    }
    .pagination a {
      display: inline-block;
      padding: 8px 16px;
      margin: 0 4px;
      background-color: #f8f9fa;
      color: #333;
      border-radius: 4px;
      text-decoration: none;
    }
    .pagination a.active {
      background-color: #007bff;
      color: white;
    }
    .pagination a:hover:not(.active) {
      background-color: #ddd;
    }
    .status-filter {
      margin-bottom: 20px;
      text-align: center;
    }
    .status-filter a {
      display: inline-block;
      padding: 8px 16px;
      margin: 0 4px;
      background-color: #f8f9fa;
      color: #333;
      border-radius: 4px;
      text-decoration: none;
    }
    .status-filter a.active {
      background-color: #007bff;
      color: white;
    }
    .user-blocked {
      background-color: #f8d7da !important;
    }
    .block-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
      margin-right: 5px;
    }
    .unblock-btn {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
      margin-right: 5px;
    }
    .status-badge {
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
    }
    .status-actif {
      background-color: #d4edda;
      color: #155724;
    }
    .status-bloque {
      background-color: #f8d7da;
      color: #721c24;
    }
  </style>
</head>
<body>
  <!-- HEADER IDENTIQUE -->
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
        <a href="profil.php">Profil</a>
        <a href="admin.php">Admin</a>
        <a href="logout.php">Déconnexion</a>
        <div class="trigger-btn" onclick="changeDarkMode()" id="btn">
          <span>Mode sombre</span>
          <img src="mode.png">
        </div>
      </nav>
    </div>
  </header>

  <!-- SECTION ADMINISTRATION -->
  <section class="section admin-section">
    <div class="admin-container">
      <h2>Administration - Liste des utilisateurs</h2>
      
      <!-- Filtres par statut -->
      <div class="status-filter">
        <a href="?filter=all" <?php echo $filter === 'all' ? 'class="active"' : ''; ?>>Tous</a>
        <a href="?filter=actif" <?php echo $filter === 'actif' ? 'class="active"' : ''; ?>>Actifs</a>
        <a href="?filter=bloque" <?php echo $filter === 'bloque' ? 'class="active"' : ''; ?>>Bloqués</a>
      </div>
      
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>Date d'inscription</th>
            <th>Dernière connexion</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($displayUsers as $user): ?>
            <?php 
            // Déterminer le statut réel de l'utilisateur
            $userStatus = getUserStatus($user['id']) ?: 'actif';
            $isBlocked = $userStatus === 'bloque';
            ?>
            <tr data-user-id="<?php echo $user['id']; ?>" <?php echo $isBlocked ? 'class="user-blocked"' : ''; ?>>
              <td data-editable="true" data-field="nom"><?php echo htmlspecialchars($user['nom']); ?></td>
              <td data-editable="true" data-field="prenom"><?php echo htmlspecialchars($user['prenom']); ?></td>
              <td data-editable="true" data-field="email"><?php echo htmlspecialchars($user['email']); ?></td>
              <td>
                <select class="role-select" data-user-id="<?php echo $user['id']; ?>">
                  <?php 
                  // Afficher le rôle réel (en excluant 'bloque' qui n'est qu'un statut)
                  $currentRole = ($user['role'] === 'bloque' || $user['role'] === 'bloqué') ? 'normal' : $user['role'];
                  ?>
                  <?php foreach ($availableRoles as $role): ?>
                    <option value="<?php echo strtolower($role); ?>" <?php echo ($currentRole === strtolower($role)) ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($role); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <span class="status-badge status-<?php echo $userStatus; ?>">
                  <?php echo ucfirst($userStatus); ?>
                </span>
                <?php if ($isBlocked && isset($user['date_blocage'])): ?>
                  <br><small>Bloqué le: <?php echo date('d/m/Y H:i', strtotime($user['date_blocage'])); ?></small>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($user['date_inscription']); ?></td>
              <td><?php echo htmlspecialchars($user['derniere_connexion']); ?></td>
              <td>
                <?php if ($isBlocked): ?>
                  <button class="unblock-btn" data-id="<?php echo $user['id']; ?>">Débloquer</button>
                <?php else: ?>
                  <button class="block-btn" data-id="<?php echo $user['id']; ?>">Bloquer</button>
                <?php endif; ?>
                <a href="profil.php?id=<?php echo $user['id']; ?>" class="edit-btn">Voir/Modifier Profil</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">&laquo;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>" <?php echo $i === $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">&raquo;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <script src="script.js"></script>
  <script src="admin-update.js"></script>
  <script>
    // Gestion du blocage/déblocage des utilisateurs
    document.addEventListener('DOMContentLoaded', function() {
      // Gestionnaire pour les boutons de blocage
      document.querySelectorAll('.block-btn').forEach(button => {
        button.addEventListener('click', function() {
          const userId = this.dataset.id;
          const userName = this.closest('tr').querySelector('[data-field="nom"]').textContent + ' ' + 
                          this.closest('tr').querySelector('[data-field="prenom"]').textContent;
          
          if (confirm(`Êtes-vous sûr de vouloir bloquer l'utilisateur ${userName} ?`)) {
            blockUser(userId);
          }
        });
      });
      
      // Gestionnaire pour les boutons de déblocage
      document.querySelectorAll('.unblock-btn').forEach(button => {
        button.addEventListener('click', function() {
          const userId = this.dataset.id;
          const userName = this.closest('tr').querySelector('[data-field="nom"]').textContent + ' ' + 
                          this.closest('tr').querySelector('[data-field="prenom"]').textContent;
          
          if (confirm(`Êtes-vous sûr de vouloir débloquer l'utilisateur ${userName} ?`)) {
            unblockUser(userId);
          }
        });
      });
    });
    
    function blockUser(userId) {
      fetch('admin.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=block_user&user_id=${userId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Utilisateur bloqué avec succès');
          location.reload();
        } else {
          alert('Erreur lors du blocage de l\'utilisateur');
        }
      })
      .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du blocage de l\'utilisateur');
      });
    }
    
    function unblockUser(userId) {
      fetch('admin.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=unblock_user&user_id=${userId}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Utilisateur débloqué avec succès');
          location.reload();
        } else {
          alert('Erreur lors du déblocage de l\'utilisateur');
        }
      })
      .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du déblocage de l\'utilisateur');
      });
    }
  </script>
</body>
</html>