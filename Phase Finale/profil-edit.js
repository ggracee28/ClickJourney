document.addEventListener('DOMContentLoaded', () => {
    let modifiedFields = {};
    let originalValues = {};
    let currentlyEditing = null;
    let userId = getUserIdFromPage();

    const style = document.createElement('style');
    style.textContent = `
        .edit-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        .save-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .submit-container {
            margin-top: 20px;
            text-align: center;
        }
        #submit-all-changes {
            padding: 10px 20px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
    `;
    document.head.appendChild(style);

    const editButtons = document.querySelectorAll('.edit-btn');
    const profileContainer = document.querySelector('.profile-container');

    // Ajout du conteneur pour les messages
    const messageContainer = document.createElement('div');
    messageContainer.id = 'message-container';
    messageContainer.style.marginTop = '10px';
    if (profileContainer) {
        profileContainer.insertBefore(messageContainer, profileContainer.firstChild);
    } else {
        console.error("Le conteneur de profil ('profile-container') est introuvable. Le conteneur de messages ne peut pas être ajouté.");
    }

    const submitContainer = document.createElement('div');
    submitContainer.className = 'submit-container';
    submitContainer.style.display = 'none';
    submitContainer.innerHTML = `<button id="save-all-changes-btn">Enregistrer toutes les modifications</button>`;
    profileContainer.appendChild(submitContainer);

    editButtons.forEach(button => {
        button.addEventListener('click', handleEditClick);
    });

    function getUserIdFromPage() {
        // Essayer de récupérer l'ID de l'utilisateur depuis l'URL ou depuis un attribut
        const idFromUrl = new URLSearchParams(window.location.search).get('id');
        if (idFromUrl) return idFromUrl;
        
        // Sinon, essayer de le récupérer depuis un attribut data ou un élément caché
        const userIdElement = document.querySelector('[data-user-id]');
        if (userIdElement) return userIdElement.getAttribute('data-user-id');
        
        // Si aucun ID spécifique n'est trouvé, retourner un ID par défaut
        return 'current-user';
    }

    function handleEditClick(event) {
        const button = event.target;
        const fieldContainer = button.closest('.profile-field');
        const fieldLabelElement = fieldContainer.querySelector('.field-label');
        // S'assurer que le label est bien nettoyé des espaces avant/après et du ':'
        const fieldLabel = fieldLabelElement.textContent.trim().replace(':', '').trim();
        const fieldValueElement = fieldContainer.querySelector('.field-value');

        // Si on clique sur "Modifier" alors qu'un autre champ est en édition, annuler cette édition.
        if (currentlyEditing && currentlyEditing.container !== fieldContainer) {
            cancelIndividualEditing(currentlyEditing.container, currentlyEditing.originalValue, currentlyEditing.fieldLabel);
        }

        if (fieldContainer.classList.contains('editing')) return;

        // Sauvegarder la valeur originale
        const originalValue = fieldValueElement.textContent.trim();
        originalValues[fieldLabel] = originalValue;

        fieldValueElement.style.display = 'none'; // Cacher la valeur actuelle
        button.style.display = 'none'; // Cacher le bouton "Modifier"

        let inputElement;
        const inputId = `input-${fieldLabel.toLowerCase().replace(/\s+/g, '-')}`;

        if (fieldLabel === 'Mot de passe') {
            inputElement = document.createElement('input');
            inputElement.type = 'password';
            inputElement.placeholder = 'Nouveau mot de passe';
            inputElement.className = 'edit-input';
            inputElement.id = inputId;
        } else if (fieldLabel === 'Date de naissance') {
            inputElement = document.createElement('input');
            inputElement.type = 'date';
            // Convertir la date jj/mm/aaaa en aaaa-mm-jj pour l'input date
            const parts = originalValue.split('/');
            if (parts.length === 3) {
                inputElement.value = `${parts[2]}-${parts[1]}-${parts[0]}`;
            } else {
                inputElement.value = originalValue; // Fallback si le format n'est pas jj/mm/aaaa
            }
            inputElement.className = 'edit-input';
            inputElement.id = inputId;
        } else {
            inputElement = document.createElement('input');
            inputElement.type = 'text';
            inputElement.value = originalValue;
            inputElement.className = 'edit-input';
            inputElement.id = inputId;
        }

        // Créer les boutons Sauvegarder et Annuler pour ce champ
        const saveBtn = document.createElement('button');
        saveBtn.textContent = 'Sauvegarder';
        saveBtn.className = 'save-btn';
        saveBtn.onclick = () => saveIndividualChange(fieldContainer, fieldLabel, inputElement, fieldValueElement, button);

        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Annuler';
        cancelBtn.className = 'cancel-btn';
        cancelBtn.onclick = () => cancelIndividualEditing(fieldContainer, originalValue, fieldLabel, fieldValueElement, button, inputElement, saveBtn, cancelBtn);

        // Insérer l'input et les boutons
        fieldContainer.appendChild(inputElement);
        fieldContainer.appendChild(saveBtn);
        fieldContainer.appendChild(cancelBtn);

        fieldContainer.classList.add('editing');
        currentlyEditing = { container: fieldContainer, originalValue: originalValue, fieldLabel: fieldLabel, inputElement, saveBtn, cancelBtn };
    }

    function cancelIndividualEditing(fieldContainer, originalValue, fieldLabel, fieldValueElement, editButton, inputElement, saveBtn, cancelBtn) {
        fieldValueElement.textContent = originalValue;
        fieldValueElement.style.display = 'inline';
        editButton.style.display = 'inline-block';

        if (inputElement) inputElement.remove();
        if (saveBtn) saveBtn.remove();
        if (cancelBtn) cancelBtn.remove();
        
        fieldContainer.classList.remove('editing');
        if (currentlyEditing && currentlyEditing.container === fieldContainer) {
            currentlyEditing = null;
        }
        clearMessage();
    }

    async function saveIndividualChange(fieldContainer, fieldLabel, inputElement, fieldValueElement, editButton) {
        const newValue = inputElement.value;
        const fieldName = mapLabelToFieldName(fieldLabel);
        const originalValue = originalValues[fieldLabel]; // Récupérer la valeur originale stockée

        // Log de débogage pour voir le nom du champ envoyé
        console.log('Nom du champ envoyé au serveur:', fieldName);

        // Afficher un indicateur de chargement (simple pour l'exemple)
        displayMessage('Mise à jour en cours...', 'info');


        const formData = new FormData();
        formData.append(fieldName, newValue);
        // Ajouter l'ID de l'utilisateur si nécessaire (surtout pour l'admin)
        if (userId !== 'current-user') {
            formData.append('id', userId);
        }


        try {
            const response = await fetch('update_profile.php' + (userId !== 'current-user' ? `?id=${userId}` : ''), {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                fieldValueElement.textContent = (fieldLabel === 'Date de naissance' && newValue) ? new Date(newValue).toLocaleDateString('fr-FR') : newValue;
                if (fieldLabel === 'Mot de passe') {
                     fieldValueElement.textContent = '********'; // Ne pas afficher le nouveau mot de passe
                }
                originalValues[fieldLabel] = newValue; // Mettre à jour la valeur originale pour la prochaine édition
                displayMessage(result.message || 'Mise à jour réussie !', 'success');
            } else {
                fieldValueElement.textContent = originalValue; // Rétablir la valeur originale
                displayMessage(result.message || 'Erreur lors de la mise à jour.', 'error');
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
            fieldValueElement.textContent = originalValue; // Rétablir la valeur originale en cas d'erreur réseau
            displayMessage('Une erreur réseau est survenue.', 'error');
        } finally {
            // Nettoyer l'interface d'édition pour ce champ
            cancelIndividualEditing(fieldContainer, fieldValueElement.textContent, fieldLabel, fieldValueElement, editButton, inputElement, currentlyEditing.saveBtn, currentlyEditing.cancelBtn);
        }
    }

    function mapLabelToFieldName(label) {
        const map = {
            'Nom': 'nom',
            'Prénom': 'prenom',
            'Email': 'email',
            'Mot de passe': 'password',
            'Date de naissance': 'date_naissance',
            'Pays d\'origine': 'pays_origine'
        };
        return map[label] || label.toLowerCase();
    }

    function displayMessage(message, type) {
        messageContainer.textContent = message;
        messageContainer.className = `message ${type}`; // Appliquer une classe pour le style
        // Cacher le message après quelques secondes
        setTimeout(clearMessage, 5000);
    }

    function clearMessage() {
        messageContainer.textContent = '';
        messageContainer.className = 'message';
    }

    // Supprimer la logique de "submit-all-changes" si chaque champ est sauvegardé individuellement
    const submitAllButton = document.getElementById('save-all-changes-btn'); // Correction de l'ID ici
    if (submitAllButton) {
        submitAllButton.remove(); // Ou le cacher si une sauvegarde globale est toujours voulue plus tard
    }
     // Style pour les messages
    const messageStyle = document.createElement('style');
    messageStyle.textContent = `
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 1em;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    `;
    document.head.appendChild(messageStyle);

});

// La fonction notifyUserBeingUpdated et la logique de gestion de l'état d'édition global (currentlyEditing)
// peuvent être simplifiées ou supprimées si chaque champ est géré indépendamment.
// La fonction getUserIdFromPage reste utile.
// La logique de `submit-all-changes` est supprimée car chaque champ est sauvegardé individuellement.