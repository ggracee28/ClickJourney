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
    const submitContainer = document.createElement('div');
    submitContainer.className = 'submit-container';
    submitContainer.style.display = 'none';
    submitContainer.innerHTML = `<button id="submit-all-changes">Enregistrer toutes les modifications</button>`;
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
        const fieldLabel = fieldContainer.querySelector('.field-label').textContent.trim().replace(':', '');
        const fieldValue = fieldContainer.querySelector('.field-value');

        if (currentlyEditing && currentlyEditing !== fieldContainer) {
            cancelEditing(currentlyEditing);
        }

        if (fieldContainer.classList.contains('editing')) return;

        // Notifier que l'utilisateur est en cours de modification
        notifyUserBeingUpdated(userId, fieldLabel);

        currentlyEditing = fieldContainer;
        const originalValue = fieldValue.textContent.trim();
        originalValues[fieldLabel] = originalValue;

        let inputElement;
        
        if (fieldLabel === 'Mot de passe') {
            inputElement = document.createElement('input');
            inputElement.type = 'password';
            inputElement.placeholder = 'Nouveau mot de passe';
            inputElement.value = '';
        } else if (fieldLabel === 'Date de naissance') {
            inputElement = document.createElement('input');
            inputElement.type = 'date';
            inputElement.value = originalValue; // Utilisation directe de la valeur
        } else if (fieldLabel === 'Pays d\'origine') {
            inputElement = document.createElement('input');
            inputElement.type = 'text';
            inputElement.value = originalValue;
        } else {
            inputElement = document.createElement('input');
            inputElement.type = 'text';
            inputElement.value = originalValue;
        }

        inputElement.className = 'edit-input';
        fieldValue.innerHTML = '';
        fieldValue.appendChild(inputElement);

        button.textContent = 'Annuler';
        button.classList.add('cancel-btn');
        button.classList.remove('edit-btn');

        button.removeEventListener('click', handleEditClick);
        button.addEventListener('click', () => cancelEditing(fieldContainer));

        const saveButton = document.createElement('button');
        saveButton.textContent = 'Valider';
        saveButton.className = 'save-btn';
        saveButton.addEventListener('click', () => saveField(fieldContainer, fieldLabel));

        button.after(saveButton);
        fieldContainer.classList.add('editing');
        inputElement.focus();
    }

    function saveField(fieldContainer, fieldLabel) {
        const inputElement = fieldContainer.querySelector('.edit-input');
        const fieldValue = fieldContainer.querySelector('.field-value');
        const newValue = inputElement.value.trim();

        if (newValue === '') {
            alert('Ce champ ne peut pas être vide');
            return;
        }

        if (fieldLabel === 'Email' && !isValidEmail(newValue)) {
            alert('Veuillez entrer une adresse email valide');
            return;
        }

        if (fieldLabel === 'Mot de passe' && newValue === '') return;

        // Notifier que la mise à jour est en cours
        simulateFieldUpdate(userId, fieldLabel, newValue);

        modifiedFields[fieldLabel] = newValue;
        fieldValue.innerHTML = fieldLabel === 'Mot de passe' ? '********' : newValue;

        restoreEditButton(fieldContainer);

        if (Object.keys(modifiedFields).length > 0) {
            submitContainer.style.display = 'block';
        }

        currentlyEditing = null;
    }

    function cancelEditing(fieldContainer) {
        const fieldLabel = fieldContainer.querySelector('.field-label').textContent.trim().replace(':', '');
        const fieldValue = fieldContainer.querySelector('.field-value');
        fieldValue.textContent = originalValues[fieldLabel] || '';
        restoreEditButton(fieldContainer);
        
        // Enlever la notification de mise à jour
        localStorage.removeItem('user_being_updated');
        
        currentlyEditing = null;
    }

    function restoreEditButton(fieldContainer) {
        const saveButton = fieldContainer.querySelector('.save-btn');
        if (saveButton) saveButton.remove();

        const cancelButton = fieldContainer.querySelector('.cancel-btn');
        if (cancelButton) {
            cancelButton.textContent = 'Modifier';
            cancelButton.classList.add('edit-btn');
            cancelButton.classList.remove('cancel-btn');
            cancelButton.removeEventListener('click', () => cancelEditing(fieldContainer));
            cancelButton.addEventListener('click', handleEditClick);
        }

        fieldContainer.classList.remove('editing');
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function notifyUserBeingUpdated(userId, fieldName) {
        const userBeingUpdated = {
            id: userId,
            field: fieldNameToDbField(fieldName),
            timestamp: Date.now(),
            expiresAt: Date.now() + 60000 // 1 minute
        };
        localStorage.setItem('user_being_updated', JSON.stringify(userBeingUpdated));
    }

    function simulateFieldUpdate(userId, fieldName, newValue) {
        const userBeingUpdated = {
            id: userId,
            field: fieldNameToDbField(fieldName),
            timestamp: Date.now(),
            expiresAt: Date.now() + 3000, // 3 secondes pour la simulation
            newValue: newValue
        };
        localStorage.setItem('user_being_updated', JSON.stringify(userBeingUpdated));
        
        // Simuler un délai de traitement avant d'enlever la notification
        setTimeout(() => {
            localStorage.removeItem('user_being_updated');
        }, 3000);
    }

    document.getElementById('submit-all-changes').addEventListener('click', () => {
        // Notifier que l'utilisateur est en cours de modification globale
        const userBeingUpdated = {
            id: userId,
            field: 'multiple',
            timestamp: Date.now(),
            expiresAt: Date.now() + 5000, // 5 secondes
            fields: Object.keys(modifiedFields).map(fieldNameToDbField)
        };
        localStorage.setItem('user_being_updated', JSON.stringify(userBeingUpdated));
        
        // Simuler un délai avant la soumission du formulaire
        setTimeout(() => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'update_profile.php'; 
            form.style.display = 'none';

            for (const [fieldName, value] of Object.entries(modifiedFields)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = fieldNameToDbField(fieldName);
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            
            // Enlever la notification après soumission
            localStorage.removeItem('user_being_updated');
        }, 3000);
    });

    function fieldNameToDbField(fieldName) {
        const mapping = {
            'Nom': 'nom',
            'Prénom': 'prenom',
            'Email': 'email',
            'Mot de passe': 'password',
            'Date de naissance': 'date_naissance',
            'Pays d\'origine': 'pays_origine'
        };
        return mapping[fieldName] || fieldName.toLowerCase().replace(/\s+/g, '_');
    }
});