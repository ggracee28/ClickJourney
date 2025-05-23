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
      const newRole = this.value; // Changé pour correspondre à ce qui est attendu par le backend
      // Utiliser sendAjaxUpdate directement pour le changement de rôle
      sendAjaxUpdate(userId, 'role', { role: newRole }, this.closest('td'));
    });
  });

  // Rendre les champs éditables directement dans le tableau
  makeTableCellsEditable();

  function makeTableCellsEditable() {
    const editableCells = document.querySelectorAll('td[data-editable="true"]');
    editableCells.forEach(cell => {
      cell.addEventListener('dblclick', function() {
        if (this.querySelector('input')) return; // Déjà en mode édition

        const originalValue = this.textContent.trim();
        const field = this.getAttribute('data-field');
        const userId = this.closest('tr').getAttribute('data-user-id');

        this.innerHTML = `<input type="text" value="${originalValue}" class="editable-input">`;
        const input = this.querySelector('input');
        input.focus();

        input.addEventListener('blur', () => {
          const newValue = input.value.trim();
          if (newValue !== originalValue) {
            sendAjaxUpdate(userId, field, { [field]: newValue }, cell, originalValue);
          } else {
            this.textContent = originalValue; // Restaurer si pas de changement
          }
        });

        input.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            input.blur();
          } else if (e.key === 'Escape') {
            this.textContent = originalValue; // Annuler avec Echap
            // Pas besoin de blur ici car on restaure directement
          }
        });
      });
    });
  }


  // Fonction pour simuler un traitement (mise à jour, suppression, etc.)
  // Cette fonction est conservée pour la suppression, mais les mises à jour de champ se font via sendAjaxUpdate
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
  async function sendAjaxUpdate(userId, field, data, cellElement, originalValue = null) {
    const row = document.querySelector(`tr[data-user-id="${userId}"]`);
    if (!row) return;

    let loadingIndicatorTarget = cellElement || row.querySelector('td:last-child'); // Cible pour l'icône de chargement

    // Afficher l'indicateur de chargement
    const loadingIndicator = document.createElement('span');
    loadingIndicator.className = 'loading-indicator';
    loadingIndicator.innerHTML = ' <i class="fas fa-spinner fa-spin"></i>';
    loadingIndicatorTarget.appendChild(loadingIndicator);
    if (cellElement && field !== 'role') cellElement.classList.add('cell-loading');


    try {
      const response = await fetch('update_user.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          userId: userId,
          action: 'update_field', // Action générique pour la mise à jour de champ
          field: field, // Nom du champ (ex: 'nom', 'email', 'role')
          value: data[field] // La nouvelle valeur
        })
      });

      const result = await response.json();

      if (result.success) {
        if (cellElement && field !== 'role') {
          cellElement.textContent = result.newValue || data[field];
        } else if (field === 'role') {
          // Le selecteur de rôle est déjà mis à jour par l'utilisateur,
          // on pourrait afficher un message de succès à côté ou globalement.
          console.log('Rôle mis à jour avec succès pour ', userId);
        }
        // Afficher un message de succès temporaire si nécessaire
        displayAdminMessage('Mise à jour réussie!', 'success', row);

      } else {
        // En cas d'échec, restaurer la valeur originale si fournie
        if (cellElement && originalValue !== null && field !== 'role') {
          cellElement.textContent = originalValue;
        } else if (field === 'role' && cellElement) {
            // Pour le rôle, on pourrait essayer de réinitialiser le selecteur à sa valeur précédente
            // Cela nécessite de stocker la valeur précédente avant l'appel AJAX.
            // Pour l'instant, on logue une erreur.
            console.error('Erreur lors de la mise à jour du rôle pour ', userId);
        }
        displayAdminMessage(result.message || 'Erreur lors de la mise à jour.', 'error', row);
      }

    } catch (error) {
      console.error('Erreur AJAX:', error);
      if (cellElement && originalValue !== null && field !== 'role') {
        cellElement.textContent = originalValue;
      }
      displayAdminMessage('Erreur réseau.', 'error', row);
    } finally {
      if (loadingIndicator) loadingIndicator.remove();
      if (cellElement && field !== 'role') cellElement.classList.remove('cell-loading');
    }
  }

  function displayAdminMessage(message, type, row) {
    let messageCell = row.querySelector('.action-message-cell');
    if (!messageCell) {
        // Si la cellule pour les messages n'existe pas, on la crée (ou on utilise une cellule existante)
        // Pour cet exemple, nous allons supposer que la dernière cellule (Actions) peut afficher des messages.
        messageCell = row.querySelector('td:last-child');
    }
    
    const messageElement = document.createElement('span');
    messageElement.className = `admin-message ${type}`;
    messageElement.textContent = message;
    
    // Vider les messages précédents dans cette cellule avant d'en ajouter un nouveau
    const existingMessages = messageCell.querySelectorAll('.admin-message');
    existingMessages.forEach(msg => msg.remove());

    messageCell.appendChild(messageElement);
    
    setTimeout(() => {
        messageElement.remove();
    }, 3000); // Le message disparaît après 3 secondes
}


  // S\'assurer que le style pour .cell-loading et .admin-message est présent
  const adminUpdateStyle = document.createElement('style');
  adminUpdateStyle.textContent = `
    .editable-input {
      width: calc(100% - 10px); /* Ajuster pour padding/border */
      padding: 5px;
      box-sizing: border-box;
    }
    .cell-loading {
      position: relative;
      /* Optionnel: style pour indiquer le chargement directement sur la cellule */
    }
    .loading-indicator {
      margin-left: 5px;
    }
    .admin-message {
        display: block; /* Pour qu'il prenne sa propre ligne ou s'intègre bien */
        margin-top: 5px;
        padding: 5px;
        border-radius: 3px;
        font-size: 0.9em;
    }
    .admin-message.success {
        background-color: #d4edda;
        color: #155724;
    }
    .admin-message.error {
        background-color: #f8d7da;
        color: #721c24;
    }
  `;
  document.head.appendChild(adminUpdateStyle);

  // La fonction checkUserBeingUpdated et simulateProcessing pour la suppression sont conservées telles quelles.
  // La fonction disableControls et enableControls est également conservée.
});

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