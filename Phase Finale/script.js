// Fonctions pour manipuler les cookies
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Vérifier si le fichier CSS est déjà chargé
function isCSSLoaded(filename) {
    const links = document.getElementsByTagName('link');
    for (let i = 0; i < links.length; i++) {
        if (links[i].href.includes(filename)) {
            return true;
        }
    }
    return false;
}

// Fonction pour charger dynamiquement un fichier CSS
function loadCSS(filename) {
    if (isCSSLoaded(filename)) {
        return; // Le fichier CSS est déjà chargé
    }
    
    // Créer un nouvel élément link
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = filename;
    
    // Ajouter au head
    document.getElementsByTagName('head')[0].appendChild(link);
}

// Vérifier si le mode sombre était activé précédemment dans un cookie
let darkMode = false;
const darkModeCookie = getCookie('darkMode');

// Mode par défaut (clair) si le cookie n'existe pas ou a une valeur incohérente
if (darkModeCookie === 'enabled') {
    darkMode = true;
}

// Appliquer le mode sombre au chargement si nécessaire
document.addEventListener('DOMContentLoaded', function() {
    // Toujours charger dark-mode.css au début
    loadCSS('dark-mode.css');
    
    if (darkMode) {
        document.body.classList.add('dark-mode');
        // Mettre à jour le texte du bouton s'il existe sur cette page
        const darkModeButton = document.querySelector("#btn span");
        if (darkModeButton) {
            darkModeButton.textContent = "Mode clair";
        }
    } else {
        document.body.classList.remove('dark-mode');
    }
});

function changeDarkMode() {
    if (darkMode) {
        // Désactiver le mode sombre
        darkMode = false;
        document.body.classList.remove('dark-mode');
        document.querySelector("#btn span").textContent = "Mode sombre";
        setCookie('darkMode', 'disabled', 30); // Cookie valide pour 30 jours
    } else {
        // Activer le mode sombre
        darkMode = true;
        document.body.classList.add('dark-mode');
        document.querySelector("#btn span").textContent = "Mode clair";
        setCookie('darkMode', 'enabled', 30); // Cookie valide pour 30 jours
    }
}