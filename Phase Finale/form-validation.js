// Fonction pour valider les formulaires
document.addEventListener("DOMContentLoaded", function() {
    // Gestionnaire pour le formulaire de connexion
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        setupPasswordToggle(loginForm);
        setupCharCounters(loginForm);
        
        loginForm.addEventListener("submit", function(event) {
            event.preventDefault(); // Empêcher l'envoi du formulaire par défaut
            
            // Récupérer les valeurs des champs
            const username = loginForm.querySelector('input[name="username"]').value.trim();
            const password = loginForm.querySelector('input[name="password"]').value.trim();
            
            // Réinitialiser les messages d'erreur
            clearErrors(loginForm);
            
            // Valider les champs
            let isValid = true;
            if (username === "") {
                displayError(loginForm.querySelector('input[name="username"]'), "Le nom d'utilisateur est requis");
                isValid = false;
            }
            
            if (password === "") {
                displayError(loginForm.querySelector('input[name="password"]'), "Le mot de passe est requis");
                isValid = false;
            } else if (password.length < 8) {
                displayError(loginForm.querySelector('input[name="password"]'), "Le mot de passe doit contenir au moins 8 caractères");
                isValid = false;
            }
            
            // Si tout est valide, soumettre le formulaire
            if (isValid) {
                loginForm.submit();
            }
        });
    }
    
    // Gestionnaire pour le formulaire d'inscription
    const registerForm = document.getElementById("register-form");
    if (registerForm) {
        setupPasswordToggle(registerForm);
        setupCharCounters(registerForm);
        
        registerForm.addEventListener("submit", function(event) {
            event.preventDefault(); // Empêcher l'envoi du formulaire par défaut
            
            // Récupérer les valeurs des champs
            const prenom = registerForm.querySelector('input[name="prenom"]').value.trim();
            const nom = registerForm.querySelector('input[name="nom"]').value.trim();
            const email = registerForm.querySelector('input[name="email"]').value.trim();
            const password = registerForm.querySelector('input[name="password"]').value.trim();
            const confirmPassword = registerForm.querySelector('input[name="confirm_password"]').value.trim();
            const termsCheckbox = registerForm.querySelector('input[type="checkbox"]');
            
            // Réinitialiser les messages d'erreur
            clearErrors(registerForm);
            
            // Valider les champs
            let isValid = true;
            
            if (prenom === "") {
                displayError(registerForm.querySelector('input[name="prenom"]'), "Le prénom est requis");
                isValid = false;
            } else if (prenom.length > 50) {
                displayError(registerForm.querySelector('input[name="prenom"]'), "Le prénom ne doit pas dépasser 50 caractères");
                isValid = false;
            }
            
            if (nom === "") {
                displayError(registerForm.querySelector('input[name="nom"]'), "Le nom est requis");
                isValid = false;
            } else if (nom.length > 50) {
                displayError(registerForm.querySelector('input[name="nom"]'), "Le nom ne doit pas dépasser 50 caractères");
                isValid = false;
            }
            
            if (email === "") {
                displayError(registerForm.querySelector('input[name="email"]'), "L'email est requis");
                isValid = false;
            } else {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email)) {
                    displayError(registerForm.querySelector('input[name="email"]'), "Veuillez entrer une adresse email valide");
                    isValid = false;
                } else if (email.length > 100) {
                    displayError(registerForm.querySelector('input[name="email"]'), "L'email ne doit pas dépasser 100 caractères");
                    isValid = false;
                }
            }
            
            if (password === "") {
                displayError(registerForm.querySelector('input[name="password"]'), "Le mot de passe est requis");
                isValid = false;
            } else if (password.length < 8) {
                displayError(registerForm.querySelector('input[name="password"]'), "Le mot de passe doit contenir au moins 8 caractères");
                isValid = false;
            } else if (password.length > 50) {
                displayError(registerForm.querySelector('input[name="password"]'), "Le mot de passe ne doit pas dépasser 50 caractères");
                isValid = false;
            }
            
            if (confirmPassword === "") {
                displayError(registerForm.querySelector('input[name="confirm_password"]'), "La confirmation du mot de passe est requise");
                isValid = false;
            } else if (password !== confirmPassword) {
                displayError(registerForm.querySelector('input[name="confirm_password"]'), "Les mots de passe ne correspondent pas");
                isValid = false;
            }
            
            if (!termsCheckbox.checked) {
                displayError(termsCheckbox, "Vous devez accepter les termes et conditions");
                isValid = false;
            }
            
            // Si tout est valide, soumettre le formulaire
            if (isValid) {
                registerForm.submit();
            }
        });
    }
    
    // Validation en temps réel pour tous les champs de formulaire
    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('input', validateInput);
        input.addEventListener('blur', validateInput);
    });
});

// Fonction pour valider un champ en temps réel
function validateInput(event) {
    const input = event.target;
    const value = input.value.trim();
    
    clearError(input);
    
    if (input.hasAttribute('required') && value === "") {
        displayError(input, "Ce champ est requis");
    } else if (input.name === "email" && value !== "") {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(value)) {
            displayError(input, "Veuillez entrer une adresse email valide");
        }
    } else if ((input.name === "password" || input.name === "confirm_password") && value !== "" && value.length < 8) {
        displayError(input, "Le mot de passe doit contenir au moins 8 caractères");
    }
    
    // Vérifier les mots de passe correspondants lors de la saisie
    if (input.name === "confirm_password" || input.name === "password") {
        const form = input.closest('form');
        const password = form.querySelector('input[name="password"]').value;
        const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
        
        if (confirmPassword !== "" && password !== confirmPassword) {
            displayError(form.querySelector('input[name="confirm_password"]'), "Les mots de passe ne correspondent pas");
        }
    }
    
    // Mettre à jour le compteur de caractères s'il existe
    updateCharCounter(input);
}

// Fonction pour afficher les erreurs
function displayError(input, message) {
    clearError(input);
    
    const errorElement = document.createElement("div");
    errorElement.className = "field-error";
    errorElement.textContent = message;
    errorElement.style.color = "#ff3333";
    errorElement.style.fontSize = "12px";
    errorElement.style.marginTop = "5px";
    
    const inputBox = input.closest('.input-box') || input.parentElement;
    inputBox.appendChild(errorElement);
    
    input.style.borderColor = "#ff3333";
}

// Fonction pour supprimer les messages d'erreur
function clearError(input) {
    const inputBox = input.closest('.input-box') || input.parentElement;
    const errorElement = inputBox.querySelector('.field-error');
    if (errorElement) {
        inputBox.removeChild(errorElement);
    }
    input.style.borderColor = "";
}

// Fonction pour supprimer tous les messages d'erreur d'un formulaire
function clearErrors(form) {
    form.querySelectorAll('.field-error').forEach(error => error.remove());
    form.querySelectorAll('input').forEach(input => input.style.borderColor = "");
}

// Fonction pour ajouter la fonctionnalité d'affichage/masquage des mots de passe
function setupPasswordToggle(form) {
    const passwordFields = form.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(passwordField => {
        const inputBox = passwordField.closest('.input-box');
        
        // Créer l'icône pour afficher/masquer le mot de passe
        const toggleIcon = document.createElement("i");
        toggleIcon.className = "fa-solid fa-eye-slash password-toggle";
        toggleIcon.style.position = "absolute";
        toggleIcon.style.right = "35px";
        toggleIcon.style.top = "50%";
        toggleIcon.style.transform = "translateY(-50%)";
        toggleIcon.style.cursor = "pointer";
        
        // Insérer l'icône dans la boîte d'entrée
        inputBox.style.position = "relative";
        inputBox.appendChild(toggleIcon);
        
        // Ajouter l'événement de clic pour basculer la visibilité du mot de passe
        toggleIcon.addEventListener("click", function() {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.className = "fa-solid fa-eye password-toggle";
            } else {
                passwordField.type = "password";
                toggleIcon.className = "fa-solid fa-eye-slash password-toggle";
            }
        });
    });
}

// Fonction pour créer et mettre à jour le compteur de caractères
function updateCharCounter(input) {
    const inputBox = input.closest('.input-box') || input.parentElement;
    let counter = inputBox.querySelector('.char-counter');
    
    if (!counter) return;
    
    const maxLength = input.getAttribute('maxlength') || 50;
    const currentLength = input.value.length;
    
    // Mise à jour du texte du compteur
    counter.textContent = `${currentLength}/${maxLength}`;
    
    // Changer le style en fonction de la longueur
    if (currentLength > maxLength * 0.8) {
        counter.style.color = "#ff9900";
    } else {
        counter.style.color = "#666";
    }
    
    // Pour les mots de passe, vérifier aussi la longueur minimale
    if ((input.name === "password" || input.name === "confirm_password")) {
        if (currentLength > 0 && currentLength < 8) {
            counter.style.color = "#ff3333";
        } else if (currentLength >= 8) {
            counter.style.color = "#4CAF50";
        }
    }
}

// Fonction pour ajouter des compteurs de caractères plus visibles
function setupCharCounters(form) {
    const fieldsWithMaxLength = {
        "email": 100,
        "username": 50,
        "password": 50,
        "confirm_password": 50,
        "nom": 50,
        "prenom": 50
    };
    
    Object.entries(fieldsWithMaxLength).forEach(([fieldName, maxLength]) => {
        const field = form.querySelector(`input[name="${fieldName}"]`);
        if (field) {
            // Ajouter l'attribut maxlength
            field.setAttribute("maxlength", maxLength);
            
            // Créer le compteur avec un style plus visible
            const counter = document.createElement("div");
            counter.className = "char-counter";
            counter.textContent = `0/${maxLength}`;
            counter.style.fontSize = "12px";
            counter.style.fontWeight = "bold";
            counter.style.padding = "3px 5px";
            counter.style.marginTop = "5px";
            counter.style.borderRadius = "3px";
            counter.style.backgroundColor = "#f8f8f8";
            counter.style.display = "inline-block";
            counter.style.color = "#666";
            
            // Ajouter une légende pour clarifier
            const counterContainer = document.createElement("div");
            counterContainer.style.display = "flex";
            counterContainer.style.justifyContent = "space-between";
            counterContainer.style.alignItems = "center";
            counterContainer.style.width = "100%";
            
            const counterLabel = document.createElement("span");
            counterLabel.textContent = "Caractères:";
            counterLabel.style.fontSize = "12px";
            
            counterContainer.appendChild(counterLabel);
            counterContainer.appendChild(counter);
            
            const inputBox = field.closest('.input-box');
            inputBox.appendChild(counterContainer);
            
            // Mettre à jour le compteur lors de la saisie
            field.addEventListener("input", function() {
                const length = field.value.length;
                counter.textContent = `${length}/${maxLength}`;
                
                // Changer la couleur selon le nombre de caractères
                if (length > maxLength * 0.8) {
                    counter.style.color = "#ff9900";
                    counter.style.backgroundColor = "#fff8e1";
                } else {
                    counter.style.color = "#666";
                    counter.style.backgroundColor = "#f8f8f8";
                }
                
                // Style spécial pour les mots de passe (minimum 8 caractères)
                if ((field.name === "password" || field.name === "confirm_password")) {
                    if (length > 0 && length < 8) {
                        counter.style.color = "#ff3333";
                        counter.style.backgroundColor = "#ffebee";
                    } else if (length >= 8) {
                        counter.style.color = "#4CAF50";
                        counter.style.backgroundColor = "#e8f5e9";
                    }
                }
            });
        }
    });
}