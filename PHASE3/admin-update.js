document.addEventListener('DOMContentLoaded', function() {
  // Vérifier si un utilisateur est en cours de modification
  checkUserBeingUpdated();
  
  // Vérifier périodiquement si un utilisateur est en cours de modification
  setInterval(checkUserBeingUpdated, 1000);
  
  // Gestion des boutons de suppression
  const deleteButtons = document.querySelectorAll('.delete-btn');
  deleteButtons.forEach(button => {
    button.addEventListener('click', function() {
      const userId = this.getAttribute('data-id');
      if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        // Simuler une action de suppression
        simulateProcessing(userId, 'delete', 3000);
      }
    });
  });

  // Gestion des sélecteurs de rôle
  const roleSelects = document.querySelectorAll('.role-select');
  roleSelects.forEach(select => {
    select.addEventListener('change', function() {
      const userId = this.getAttribute('data-user-id');
      const currentRole = this.value;
      
      // Simuler le traitement du changement de rôle
      simulateProcessing(userId, 'role', 3000, { newRole: currentRole });
    });
  });
  
  // Fonction pour simuler un traitement (mise à jour, suppression, etc.)
  function simulateProcessing(userId, action, duration, data = {}) {
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!row) return;
    
    // Désactiver les contrôles interactifs dans la ligne
    disableControls(row);
    
    // Ajouter la classe updating pour griser la ligne
    row.classList.add('updating');
    
    // Afficher l'indicateur de chargement
    let targetCell;
    if (action === 'role') {
      targetCell = row.querySelector('td[data-label="Rôle"]') || row.querySelector('td:nth-child(4)');
    } else {
      targetCell = row.querySelector('td:last-child');
    }
    
    if (targetCell) {
      const loadingIndicator = document.createElement('span');
      loadingIndicator.className = 'loading-indicator';
      loadingIndicator.innerHTML = ' <i class="fas fa-spinner fa-spin"></i>';
      targetCell.appendChild(loadingIndicator);
    }
    
    // Stocker dans le localStorage que cet utilisateur est en cours de modification
    const userBeingUpdated = {
      id: userId,
      field: action,
      timestamp: Date.now(),
      expiresAt: Date.now() + duration,
      ...data
    };
    localStorage.setItem('user_being_updated', JSON.stringify(userBeingUpdated));
    
    // Simuler un délai de traitement
    setTimeout(() => {
      // Supprimer l'indicateur de chargement
      const loadingIndicator = row.querySelector('.loading-indicator');
      if (loadingIndicator) {
        loadingIndicator.remove();
      }
      
      // Réactiver les contrôles et restaurer l'apparence de la ligne
      enableControls(row);
      row.classList.remove('updating');
      
      // Supprimer du localStorage après le délai
      localStorage.removeItem('user_being_updated');
      
      // Envoyer une requête AJAX pour la mise à jour réelle
      sendAjaxUpdate(userId, action, data);
    }, duration);
  }
  
  // Fonction pour envoyer les mises à jour via AJAX
  function sendAjaxUpdate(userId, action, data) {
    // Utiliser fetch API pour envoyer les données au serveur
    fetch('update_user.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        userId: userId,
        action: action,
        data: data
      })
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        console.log(`Mise à jour réussie: ${action}`);
      } else {
        console.error(`Erreur lors de la mise à jour: ${result.message}`);
        alert(`Erreur: ${result.message}`);
      }
    })
    .catch(error => {
      console.error('Erreur:', error);
    });
  }
  
  // Désactiver tous les contrôles interactifs d'une ligne
  function disableControls(row) {
    // Désactiver les sélecteurs
    row.querySelectorAll('select').forEach(select => {
      select.disabled = true;
    });
    
    // Désactiver les boutons
    row.querySelectorAll('button').forEach(button => {
      button.disabled = true;
    });
    
    // Désactiver les liens (en ajoutant une classe)
    row.querySelectorAll('a').forEach(link => {
      link.classList.add('disabled-link');
      link.addEventListener('click', preventClick);
    });
  }
  
  // Réactiver tous les contrôles interactifs d'une ligne
  function enableControls(row) {
    // Réactiver les sélecteurs
    row.querySelectorAll('select').forEach(select => {
      select.disabled = false;
    });
    
    // Réactiver les boutons
    row.querySelectorAll('button').forEach(button => {
      button.disabled = false;
    });
    
    // Réactiver les liens
    row.querySelectorAll('a').forEach(link => {
      link.classList.remove('disabled-link');
      link.removeEventListener('click', preventClick);
    });
  }
  
  // Empêcher le clic pendant le traitement
  function preventClick(e) {
    e.preventDefault();
    return false;
  }
  
  // Fonction pour vérifier si un utilisateur est en cours de modification
  function checkUserBeingUpdated() {
    const userBeingUpdatedStr = localStorage.getItem('user_being_updated');
    
    // Réinitialiser toutes les lignes d'abord
    document.querySelectorAll('tr.updating').forEach(row => {
      row.classList.remove('updating');
      enableControls(row);
      const loadingIndicator = row.querySelector('.loading-indicator');
      if (loadingIndicator) {
        loadingIndicator.remove();
      }
    });
    
    if (userBeingUpdatedStr) {
      try {
        const userBeingUpdated = JSON.parse(userBeingUpdatedStr);
        
        // Vérifier si l'expiration est dépassée
        if (userBeingUpdated.expiresAt < Date.now()) {
          localStorage.removeItem('user_being_updated');
          return;
        }
        
        // Trouver la ligne de l'utilisateur et la griser
        const userRow = document.querySelector(`tr[data-user-id="${userBeingUpdated.id}"]`);
        if (userRow) {
          userRow.classList.add('updating');
          disableControls(userRow);
          
          // Ajouter un indicateur de chargement dans la cellule correspondante
          let targetCell;
          if (userBeingUpdated.field === 'role') {
            targetCell = userRow.querySelector('td[data-label="Rôle"]') || userRow.querySelector('td:nth-child(4)');
            
            // Si c'est une mise à jour de rôle, on peut mettre à jour la valeur sélectionnée
            if (userBeingUpdated.newRole) {
              const roleSelect = userRow.querySelector('.role-select');
              if (roleSelect) {
                roleSelect.value = userBeingUpdated.newRole;
              }
            }
          } else {
            targetCell = userRow.querySelector('td:last-child');
          }
          
          if (targetCell && !targetCell.querySelector('.loading-indicator')) {
            const loadingIndicator = document.createElement('span');
            loadingIndicator.className = 'loading-indicator';
            loadingIndicator.innerHTML = ' <i class="fas fa-spinner fa-spin"></i>';
            targetCell.appendChild(loadingIndicator);
          }
        }
      } catch (e) {
        console.error('Erreur lors de la vérification de l\'utilisateur en cours de modification', e);
        localStorage.removeItem('user_being_updated');
      }
    }
  }
});