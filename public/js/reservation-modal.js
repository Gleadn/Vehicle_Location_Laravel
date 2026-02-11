let currentVehicleData = null;

function openReservationModal(vehicleId, vehicleName, vehicleRegistration, dailyRate, seats, fuelType, available, status) {
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

    document.getElementById('modal-vehicle-id').value = vehicleId;
    document.getElementById('modal-vehicle-name').textContent = vehicleName;
    document.getElementById('modal-vehicle-registration').textContent = vehicleRegistration;
    
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
    document.getElementById('criteria-seats-value').textContent = seats + ' places';
    document.getElementById('criteria-fuel-value').textContent = capitalizeFirstLetter(fuelType);

    // Afficher le modal
    modal.classList.add('active');
    
    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden';
}document.body.style.overflow = 'hidden';
}
 const modal = document.getElementById('reservationModal');
    modal.classList.remove('active');
    
    // Réactiver le scroll du body
    document.body.style.overflow = '';
    
    // Réinitialiser le formulaire
    document.getElementById('reservationForm').reset();
    document.body.style.overflow = '';
    
    document.getElementById('reservationForm').reset();
    
/**
 * Mettre à jour l'état des checkboxes (max 2 sélectionnées)
 */
function updateCheckboxStates() {
    const checkboxes = document.querySelectorAll('input[name="criteria[]"]');
    const checkedBoxes = document.querySelectorAll('input[name="criteria[]"]:checked');
    
    // Si 2 cases sont déjà cochées, désactiver les autres
                checkbox.disabled = true;
                checkbox.parentElement.style.opacity = '0.5';
                checkbox.parentElement.style.cursor = 'not-allowed';
            }
        });
    } else {
        checkboxes.forEach(checkbox => {
            checkbox.disabled = false;
            checkbox.parentElement.style.opacity = '1';
            checkbox.parentElement.style.cursor = 'pointer';
        });
    }
}

/**
 * Capitaliser la première lettre
 */ capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
    const modal = document.getElementById('reservationModal');
    
    if (modal) {
        const overlay = modal.querySelector('.modal-overlay');
        
        overlay.addEventListener('click', function() {
            closeReservationModal();
        const criteriaCheckboxes = modal.querySelectorAll('input[name="criteria[]"]');
        criteriaCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateCheckboxStates();
            });
        });
        updateCheckboxStates();

        // Ouvrir le modal depuis les boutons (sauf sur la page d'accueil qui redirige)
        const shouldRedirect = document.body.hasAttribute('data-redirect-to-vehicles');
        if (!shouldRedirect) {
            const reserveButtons = document.querySelectorAll('.btn-reserve[data-vehicle-id]');
            reserveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    openReservationModal(
                        this.dataset.vehicleId,
                        this.dataset.vehicleName,
                        this.dataset.vehicleRegistration,
                        this.dataset.dailyRate,
                        this.dataset.fuelType,
                        this.dataset.available === 'true',
                        this.dataset.status
                    );
                });
            });
        }
        
        // Gestion de la soumission du formulaire
        const form = document.getElementById('reservationForm');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Récupérer les critères sélectionnés
            const selectedCriteria = [];
            const checkboxes = form.querySelectorAll('input[name="criteria[]"]:checked');
            
                selectedCriteria.push(checkbox.value);
            });

            // Vérifier que le véhicule est disponible
            if (!currentVehicleData.available || currentVehicleData.status !== 'active') {
                alert('Ce véhicule n\'est pas disponible pour le moment.');
            }

            // Désactiver le bouton de soumission
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Réservation en cours...';
try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch('/vehicles/reserve', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        vehicle_id: currentVehicleData.id,
                        criteria: selectedCriteria
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Fermer le modal
                    closeReservationModal();
                    
                    // Rediriger vers le profil avec message de succès
                    window.location.href = '/profile?reservation=success';
                } else {
                    throw new Error(result.message || 'Erreur lors de la réservation');
                }
            } catch (error) {
                console.error('Erreur:', error);
                
                // Réactiver le bouton
                submcloseReservationModal();
                    
                let errorMsg = error.message;
                if (error.message.includes('Unauthenticated') || error.message.includes('401')) {
                    errorMsg = 'Vous devez être connecté pour réserver un véhicule.';
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 2000);
                }

                // Créer et afficher un message d'erreur dans le modal
                if (existingError) existingError.remove();
const errorDiv = document.createElement('div');
                errorDiv.className = 'error-alert';
                errorDiv.style.cssText = 'margin: 1rem 0; padding: 1rem; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px;';
                errorDiv.innerHTML = `<strong>❌ Erreur :</strong> ${errorMsg}`;
                
                form.insertAdjacentElement('afterbegin', errorDiv);
                
                // Supprimer le message après 5 secondes
                setTimeout(() => errorDiv.remove(), 5000);
            }
    }
});

/**
 * Fermer le modal avec la touche Escape
 */
document.addEventListener('keydown', function(e) {
    if (e.key ==dal = document.getElementById('reservationModal');
        if (modal && modal.classList.contains('active')) {
            closeReservationModal();
        }
    }
});
