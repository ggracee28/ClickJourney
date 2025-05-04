function updateTotalPrice() {
    let totalPrice = parseFloat(document.getElementById('base-price').dataset.price); // Prix de base

    // Parcourir toutes les étapes
    const etapes = document.querySelectorAll('.etape-item');
    
    etapes.forEach(function(etape) {
        const etapeId = etape.dataset.etapeId;
        
        // Hébergement
        const hebergementSelect = etape.querySelector(`[name="hebergement_${etapeId}"]`);
        if (hebergementSelect) {
            totalPrice += parseFloat(hebergementSelect.options[hebergementSelect.selectedIndex].dataset.price);
        }

        // Restauration
        const restaurationSelect = etape.querySelector(`[name="restauration_${etapeId}"]`);
        if (restaurationSelect) {
            totalPrice += parseFloat(restaurationSelect.options[restaurationSelect.selectedIndex].dataset.price);
        }

        // Activités
        const activitesCheckboxes = etape.querySelectorAll(`[name="activite_${etapeId}[]"]`);
        activitesCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                const optionId = checkbox.value;
                const nbPersonsInput = etape.querySelector(`[name="activite_${etapeId}_${optionId}_nb"]`);
                const nbPersons = parseInt(nbPersonsInput.value) || 1;
                const prixUnitaire = parseFloat(checkbox.dataset.price);
                totalPrice += prixUnitaire * nbPersons;
            }
        });

        // Transport
        const transportSelect = etape.querySelector(`[name="transport_${etapeId}"]`);
        if (transportSelect) {
            totalPrice += parseFloat(transportSelect.options[transportSelect.selectedIndex].dataset.price);
        }
    });

    // Mettre à jour le prix total à l'écran
    document.getElementById('total-price').textContent = totalPrice.toFixed(2) + '£';
}

// Initialiser et ajouter des écouteurs d'événements
document.addEventListener('DOMContentLoaded', function() {
    updateTotalPrice();
    
    // Ajouter des écouteurs sur tous les champs qui peuvent modifier le prix
    const form = document.querySelector('form');
    
    // Pour les sélecteurs (hébergement, restauration, transport)
    form.querySelectorAll('select').forEach(function(select) {
        select.addEventListener('change', updateTotalPrice);
    });
    
    // Pour les checkboxes d'activités
    form.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
        checkbox.addEventListener('change', updateTotalPrice);
    });
    
    // Pour les champs de nombre de personnes
    form.querySelectorAll('input[type="number"]').forEach(function(input) {
        input.addEventListener('change', updateTotalPrice);
        input.addEventListener('input', updateTotalPrice);
    });
});