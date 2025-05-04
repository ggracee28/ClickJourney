document.addEventListener('DOMContentLoaded', function() {
    // Sélection des éléments
    const searchInput = document.querySelector('.search-input');
    const priceFilter = document.getElementById('price-filter');
    const durationFilter = document.getElementById('duration-filter');
    const continentFilter = document.getElementById('continent-filter');
    const sortBy = document.getElementById('sort-by');
    const destinationCards = document.querySelectorAll('.destination-card');

    // Mapping amélioré des pays et destinations vers leurs continents
    const paysContinent = {
        // Europe
        'madère': 'europe',
        'londres': 'europe',
        'vatican': 'europe',
        'italie': 'europe',
        'édimbourd': 'europe',
        'pablo picasso': 'europe',
        'greta thunberg': 'europe',
        'christine lagarde': 'europe',
        'autriche': 'europe',
        'grèce': 'europe',
        'grece': 'europe',
        'irlande': 'europe',
        'pays-bas': 'europe',
        'belgique': 'europe',
        'danemark': 'europe',
        'suède': 'europe',
        'suede': 'europe',
        'norvège': 'europe',
        'norvege': 'europe',
        'finlande': 'europe',
        'islande': 'europe',
        'pologne': 'europe',
        'république tchèque': 'europe',
        'republique tcheque': 'europe',
        'hongrie': 'europe',
        'croatie': 'europe',
        'londres': 'europe',
        'paris': 'europe',
        'rome': 'europe',
        'barcelone': 'europe',
        'madrid': 'europe',
        'venise': 'europe',
        'florence': 'europe',
        'monaco': 'europe',
        
        // Asie
        'japon': 'asie',
        'chine': 'asie',
        'inde': 'asie',
        'thaïlande': 'asie',
        'thailande': 'asie',
        'vietnam': 'asie',
        'cambodge': 'asie',
        'laos': 'asie',
        'indonésie': 'asie',
        'indonesie': 'asie',
        'malaisie': 'asie',
        'singapour': 'asie',
        'népal': 'asie',
        'nepal': 'asie',
        'sri lanka': 'asie',
        'philippines': 'asie',
        'corée du sud': 'asie',
        'coree du sud': 'asie',
        'émirats arabes unis': 'asie',
        'emirats arabes unis': 'asie',
        'dubaï': 'asie',
        'dubai': 'asie',
        'tokyo': 'asie',
        'kyoto': 'asie',
        'seoul': 'asie',
        'pékin': 'asie',
        'pekin': 'asie',
        'hong kong': 'asie',
        'shanghai': 'asie',
        'bombay': 'asie',
        'mumbai': 'asie',
        'delhi': 'asie',
        'bangkok': 'asie',
        'bali': 'asie',
        
        // Afrique
        'maroc': 'afrique',
        'afrique du sud': 'afrique',
        'egypte': 'afrique',
        'afrique du sud': 'afrique',
        'kenya': 'afrique',
        'tanzanie': 'afrique',
        'sénégal': 'afrique',
        'senegal': 'afrique',
        'namibie': 'afrique',
        'madagascar': 'afrique',
        'maurice': 'afrique',
        'seychelles': 'afrique',
        'tunisie': 'afrique',
        'algérie': 'afrique',
        'algerie': 'afrique',
        'le caire': 'afrique',
        'marrakech': 'afrique',
        'casablanca': 'afrique',
        'cape town': 'afrique',
        'johannesburg': 'afrique',
        'zanzibar': 'afrique',
        
        // Amérique
        'états-unis': 'amerique',
        'etats-unis': 'amerique',
        'usa': 'amerique',
        'états unis': 'amerique',
        'barbade': 'amerique',
        'états-unis d\'amérique': 'amerique',
        'etats-unis d\'amerique': 'amerique',
        'canada': 'amerique',
        'mexique': 'amerique',
        'brésil': 'amerique',
        'bresil': 'amerique',
        'argentine': 'amerique',
        'pérou': 'amerique',
        'perou': 'amerique',
        'colombie': 'amerique',
        'cuba': 'amerique',
        'jamaïque': 'amerique',
        'jamaique': 'amerique',
        'costa rica': 'amerique',
        'chili': 'amerique',
        'new york': 'amerique',
        'los angeles': 'amerique',
        'san francisco': 'amerique',
        'miami': 'amerique',
        'las vegas': 'amerique',
        'chicago': 'amerique',
        'houston': 'amerique',
        'dallas': 'amerique',
        'texas': 'amerique',
        'californie': 'amerique',
        'floride': 'amerique',
        'rio de janeiro': 'amerique',
        'sao paulo': 'amerique',
        
        // Océanie
        'australie': 'oceanie',
        'nouvelle-zélande': 'oceanie',
        'nouvelle zelande': 'oceanie',
        'fidji': 'oceanie',
        'polynésie française': 'oceanie',
        'polynesie francaise': 'oceanie',
        'tahiti': 'oceanie',
        'nouvelle-calédonie': 'oceanie',
        'nouvelle caledonie': 'oceanie',
        'hawaï': 'oceanie',
        'sydney': 'oceanie',
        'melbourne': 'oceanie',
        'brisbane': 'oceanie',
        'auckland': 'oceanie',
        'wellington': 'oceanie'
    };

    // Fonction qui extrait le nombre de la chaîne "A partir de 5000£"
    function extractPrice(priceText) {
        return parseInt(priceText.replace(/\D/g, ''), 10);
    }
    
    // Fonction améliorée pour déterminer le continent en fonction du texte
    function getContinent(text) {
        if (!text) return '';
        
        text = text.toLowerCase().trim();
        
        // Vérifier dans le mapping direct
        if (paysContinent[text]) {
            return paysContinent[text];
        }
        
        // Vérifier si un mot-clé de pays ou ville est présent dans le texte
        for (const [key, continent] of Object.entries(paysContinent)) {
            if (text.includes(key)) {
                return continent;
            }
        }
        
        return '';
    }

    // Fonction qui filtre et trie les cartes
    function updateCards() {
        // Paramètres de filtrage
        const keyword = searchInput.value.toLowerCase();
        const selectedPrice = priceFilter.value;
        const selectedDuration = durationFilter.value;
        const selectedContinent = continentFilter.value.toLowerCase();
        const sortValue = sortBy.value;
        
        // Créer une liste des cartes pour le tri
        let cardsArray = Array.from(destinationCards);
        let visibleCount = 0;
        
        // Filtrer les cartes
        cardsArray.forEach(card => {
            const cardTitle = card.querySelector('h3').textContent.toLowerCase();
            const cardDesc = card.querySelector('p').textContent.toLowerCase();
            const cardText = cardTitle + ' ' + cardDesc;
            const matchesKeyword = keyword === '' || cardText.includes(keyword);
            
            // Extraction du pays et détermination du continent
            const pays = card.dataset.pays;
            
            // Vérifier d'abord dans le titre et la description complète pour le continent
            let continent = getContinent(pays);
            
            // Si pas trouvé, rechercher dans le titre et la description complète
            if (!continent && cardTitle) {
                continent = getContinent(cardTitle);
            }
            
            if (!continent && cardDesc) {
                continent = getContinent(cardDesc);
            }
            
            // Filtrage par prix
            const price = parseInt(card.dataset.price, 10);
            let matchesPrice = false;
            if (selectedPrice === 'all') {
                matchesPrice = true;
            } else if (selectedPrice === 'low') {
                matchesPrice = (price < 4000);
            } else if (selectedPrice === 'mid') {
                matchesPrice = (price >= 4000 && price < 5000);
            } else if (selectedPrice === 'high') {
                matchesPrice = (price >= 5000);
            }
            
            // Filtrage par durée
            const duration = parseInt(card.dataset.duree, 10);
            let matchesDuration = false;
            if (selectedDuration === 'all') {
                matchesDuration = true;
            } else if (selectedDuration === 'short') {
                matchesDuration = (duration < 7);
            } else if (selectedDuration === 'medium') {
                matchesDuration = (duration >= 7 && duration <= 14);
            } else if (selectedDuration === 'long') {
                matchesDuration = (duration > 14);
            }
            
            // Filtrage par continent (en utilisant le mapping amélioré)
            const matchesContinent = (selectedContinent === 'all' || continent === selectedContinent);
            
            // Appliquer tous les filtres
            if (matchesKeyword && matchesPrice && matchesDuration && matchesContinent) {
                card.style.display = '';  // Afficher la carte
                visibleCount++;
            } else {
                card.style.display = 'none';  // Cacher la carte
            }
        });
        
        // Trier les cartes visibles
        if (sortValue !== 'none') {
            // Filtrer uniquement les cartes visibles
            let visibleCards = cardsArray.filter(card => card.style.display !== 'none');
            
            // Trier les cartes selon le critère sélectionné
            visibleCards.sort((a, b) => {
                if (sortValue === 'prix-asc') {
                    return parseInt(a.dataset.price, 10) - parseInt(b.dataset.price, 10);
                } else if (sortValue === 'prix-desc') {
                    return parseInt(b.dataset.price, 10) - parseInt(a.dataset.price, 10);
                } else if (sortValue === 'duree-asc') {
                    return parseInt(a.dataset.duree, 10) - parseInt(b.dataset.duree, 10);
                } else if (sortValue === 'duree-desc') {
                    return parseInt(b.dataset.duree, 10) - parseInt(a.dataset.duree, 10);
                }
                return 0;
            });
            
            // Réorganiser les cartes dans le DOM
            const container = document.querySelector('.destination-grid');
            visibleCards.forEach(card => {
                container.appendChild(card);
            });
        }
        
        // Afficher un message si aucun résultat
        const noResultsMsg = document.getElementById('no-results-message');
        if (noResultsMsg) {
            if (visibleCount === 0) {
                noResultsMsg.style.display = 'block';
            } else {
                noResultsMsg.style.display = 'none';
            }
        }
    }

    // Ajouter les écouteurs d'événements
    searchInput.addEventListener('input', updateCards);
    priceFilter.addEventListener('change', updateCards);
    durationFilter.addEventListener('change', updateCards);
    continentFilter.addEventListener('change', updateCards);
    sortBy.addEventListener('change', updateCards);
    
    // Initialiser avec l'état actuel des filtres
    updateCards();
    
    // Ajouter un élément pour afficher un message quand aucun résultat n'est trouvé
    if (!document.getElementById('no-results-message')) {
        const container = document.querySelector('.destination-grid');
        const noResultsMsg = document.createElement('div');
        noResultsMsg.id = 'no-results-message';
        noResultsMsg.textContent = 'Aucune destination ne correspond à votre recherche.';
        noResultsMsg.style.display = 'none';
        noResultsMsg.style.width = '100%';
        noResultsMsg.style.textAlign = 'center';
        noResultsMsg.style.padding = '2rem';
        noResultsMsg.style.fontSize = '1.2rem';
        container.parentNode.insertBefore(noResultsMsg, container.nextSibling);
    }
});