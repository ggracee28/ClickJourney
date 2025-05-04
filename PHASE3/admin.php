<?php
require_once 'config.php';
require_once 'user_functions.php';

// Vérifier si l'utilisateur est un administrateur
if (!isAdmin()) {
    redirect('connexion.php');
}

// Récupérer tous les utilisateurs
$users = getAllUsers();

// Pagination
$usersPerPage = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$totalUsers = count($users);
$totalPages = ceil($totalUsers / $usersPerPage);
$page = max(1, min($page, $totalPages));
$start = ($page - 1) * $usersPerPage;
$displayUsers = array_slice($users, $start, $usersPerPage);

// Définir les rôles disponibles - MODIFIÉ
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
      <table class="admin-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Date d'inscription</th>
            <th>Dernière connexion</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($displayUsers as $user): ?>
          <tr data-user-id="<?php echo $user['id']; ?>">
            <td data-label="Nom"><?php echo $user['nom']; ?></td>
            <td data-label="Prénom"><?php echo $user['prenom']; ?></td>
            <td data-label="Email"><?php echo $user['email']; ?></td>
            <td data-label="Rôle">
              <div class="role-display">
                <select class="role-select" data-user-id="<?php echo $user['id']; ?>">
                  <?php foreach ($availableRoles as $role): ?>
                    <option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>>
                      <?php echo $role; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </td>
            <td data-label="Date d'inscription"><?php echo $user['date_inscription']; ?></td>
            <td data-label="Dernière connexion"><?php echo $user['derniere_connexion']; ?></td>
            <td data-label="Actions">
              <a href="profil.php?id=<?php echo $user['id']; ?>" class="view-btn">Voir Profil</a>
              <button class="delete-btn" data-id="<?php echo $user['id']; ?>">Supprimer</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      
      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>">&laquo;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" <?php echo $i === $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>">&raquo;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <script src="script.js"></script>
  <script src="admin-update.js"></script>
</body>
</html>