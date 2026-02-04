// Gestion du modal de réservation

let currentVehicleData = null;

/**
 * Ouvrir le modal de réservation avec les données du véhicule
 */
function openReservationModal(vehicleId, vehicleName, vehicleRegistration, dailyRate, seats, fuelType, available, status) {
    // Stocker les données du véhicule
    currentVehicleData = {
        id: vehicleId,
        name: vehicleName,
        registration: vehicleRegistration,
        dailyRate: dailyRate,
        seats: seats,
        fuelType: fuelType,
        available: available,
        status: status
    };

    // Remplir les informations du véhicule dans le modal
    document.getElementById('modal-vehicle-id').value = vehicleId;
    document.getElementById('modal-vehicle-name').textContent = vehicleName;
    document.getElementById('modal-vehicle-registration').textContent = vehicleRegistration;
    
    // Afficher l'indicateur de disponibilité
    const availabilityIndicator = document.getElementById('modal-vehicle-availability');
    availabilityIndicator.className = 'vehicle-availability-indicator';
    
    if (available && status === 'active') {
        availabilityIndicator.classList.add('available');
        availabilityIndicator.textContent = 'Disponible';
    } else if (status === 'maintenance') {
        availabilityIndicator.classList.add('maintenance');
        availabilityIndicator.textContent = 'En maintenance';
    } else {
        availabilityIndicator.classList.add('unavailable');
        availabilityIndicator.textContent = 'Indisponible';
    }

    // Remplir les valeurs des critères
    document.getElementById('criteria-price-value').textContent = dailyRate + ' €/jour';
    document.getElementById('criteria-seats-value').textContent = seats + ' places';
    document.getElementById('criteria-fuel-value').textContent = capitalizeFirstLetter(fuelType);

    // Afficher le modal
    const modal = document.getElementById('reservationModal');
    modal.classList.add('active');
    
    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden';
}

/**
 * Fermer le modal de réservation
 */
function closeReservationModal() {
    const modal = document.getElementById('reservationModal');
    modal.classList.remove('active');
    
    // Réactiver le scroll du body
    document.body.style.overflow = '';
    
    // Réinitialiser le formulaire
    document.getElementById('reservationForm').reset();
    
    // Réactiver toutes les checkboxes
    updateCheckboxStates();
    
    currentVehicleData = null;
}

/**
 * Mettre à jour l'état des checkboxes (max 2 sélectionnées)
 */
function updateCheckboxStates() {
    const checkboxes = document.querySelectorAll('input[name="criteria[]"]');
    const checkedBoxes = document.querySelectorAll('input[name="criteria[]"]:checked');
    
    // Si 2 cases sont déjà cochées, désactiver les autres
    if (checkedBoxes.length >= 2) {
        checkboxes.forEach(checkbox => {
            if (!checkbox.checked) {
                checkbox.disabled = true;
                checkbox.parentElement.style.opacity = '0.5';
                checkbox.parentElement.style.cursor = 'not-allowed';
            }
        });
    } else {limitation à 2 critères
        const criteriaCheckboxes = modal.querySelectorAll('input[name="criteria[]"]');
        criteriaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCheckboxStates();
            });
        });
        
        // Gestion de la 
        // Réactiver toutes les cases
        checkboxes.forEach(checkbox => {
            checkbox.disabled = false;
            checkbox.parentElement.style.opacity = '1';
            checkbox.parentElement.style.cursor = 'pointer';
        });
    }
}

/**
 * Capitaliser la première lettre
 */
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Fermer le modal en cliquant sur l'overlay
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('reservationModal');
    
    if (modal) {
        const overlay = modal.querySelector('.modal-overlay');
        
        overlay.addEventListener('click', function() {
            closeReservationModal();
        });
        
        // Gestion de la soumission du formulaire
        const form = document.getElementById('reservationForm');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Récupérer les critères sélectionnés
            const selectedCriteria = [];
            const checkboxes = form.querySelectorAll('input[name="criteria[]"]:checked');
            
            checkboxes.forEach(checkbox => {
                selectedCriteria.push(checkbox.value);
            });
            
            // Pour l'instant, juste afficher dans la console
            console.log('Demande de réservation:', {
                vehicleId: currentVehicleData.id,
                vehicleName: currentVehicleData.name,
                selectedCriteria: selectedCriteria
            });
            
            // Afficher un message de confirmation temporaire
            alert('Demande de réservation enregistrée!\n\nVéhicule: ' + currentVehicleData.name + '\nCritères sélectionnés: ' + (selectedCriteria.length > 0 ? selectedCriteria.join(', ') : 'Aucun'));
            
            // Fermer le modal
            closeReservationModal();
        });
    }
});

/**
 * Fermer le modal avec la touche Escape
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('reservationModal');
        if (modal && modal.classList.contains('active')) {
            closeReservationModal();
        }
    }
});
