document.addEventListener('DOMContentLoaded', function() {
    const categoryRadios = document.querySelectorAll('input[name="vehicle_category"]');
    const seatsSection = document.getElementById('section-seats');
    const seatsSelect = document.getElementById('seatsSelect');
    const sportySection = document.getElementById('section-sporty');
    const tripSection = document.getElementById('section-trip');
    const form = document.getElementById('locationDemandForm');
    const proposalsSection = document.getElementById('proposalsSection');
    const proposalsGrid = document.getElementById('proposalsGrid');
    const loadingMessage = document.getElementById('loadingMessage');
    const backToFormBtn = document.getElementById('backToFormBtn');

    if (!categoryRadios.length || !seatsSelect) {
        return;
    }

    const seatOptions = {
        motorcycle: [1, 2],
        four_wheels: [2, 5, 7, 8, 11]
    };

    function resetSelectOptions() {
        seatsSelect.innerHTML = '<option value="">Selectionner</option>';
    }

    function populateSeats(category) {
        resetSelectOptions();
        const options = seatOptions[category] || [];
        options.forEach(value => {
            const option = document.createElement('option');
            option.value = String(value);
            option.textContent = String(value);
            seatsSelect.appendChild(option);
        });
    }

    function handleCategoryChange() {
        const selected = document.querySelector('input[name="vehicle_category"]:checked');
        if (!selected) {
            seatsSection.classList.add('hidden');
            tripSection.classList.add('hidden');
            sportySection.classList.add('hidden');
            resetSelectOptions();
            return;
        }

        populateSeats(selected.value);
        seatsSection.classList.remove('hidden');
        tripSection.classList.add('hidden');
        sportySection.classList.add('hidden');
        seatsSelect.value = '';
    }

    function handleSeatsChange() {
        const category = document.querySelector('input[name="vehicle_category"]:checked');
        const seatsValue = seatsSelect.value;

        if (!category || !seatsValue) {
            tripSection.classList.add('hidden');
            sportySection.classList.add('hidden');
            return;
        }

        tripSection.classList.remove('hidden');

        if (category.value === 'four_wheels' && seatsValue === '2') {
            sportySection.classList.remove('hidden');
        } else {
            sportySection.classList.add('hidden');
        }
    }

    categoryRadios.forEach(radio => {
        radio.addEventListener('change', handleCategoryChange);
    });

    seatsSelect.addEventListener('change', handleSeatsChange);

    handleCategoryChange();
    handleSeatsChange();

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        form.classList.add('hidden');
        loadingMessage.classList.remove('hidden');

        try {
            const response = await fetch('/location-demands', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success && result.proposals) {
                displayProposals(result.proposals);
                loadingMessage.classList.add('hidden');
                proposalsSection.classList.remove('hidden');
            } else {
                throw new Error(result.message || 'Erreur lors de la récupération des propositions');
            }
        } catch (error) {
            console.error('Erreur:', error);
            
            loadingMessage.innerHTML = `
                <div class="error-message">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <p style="color: #dc3545; font-weight: 600; margin-top: 1rem;">Une erreur est survenue</p>
                    <p style="color: #666;">Veuillez vous connecter ou réessayer.</p>
                    <button onclick="location.reload()" style="margin-top: 1rem; padding: 0.5rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 6px; cursor: pointer;">Réessayer</button>
                </div>
            `;
        }
    });

    // Afficher les propositions de véhicules
    function displayProposals(proposals) {
        proposalsGrid.innerHTML = '';

        proposals.forEach((proposal, index) => {
            const card = createVehicleCard(proposal, index + 1);
            proposalsGrid.appendChild(card);
        });
    }

    // Créer une carte de véhicule
    function createVehicleCard(proposal, position) {
        const card = document.createElement('div');
        card.className = 'vehicle-proposal-card';
        
        const rankBadge = position === 1 ? '<span class="rank-badge best">Meilleur choix</span>' : '';
        
        const typeLabels = {
            'car': 'Voiture',
            'motorcycle': 'Moto',
            'van': 'Van',
            'sport': 'Sportive'
        };

        const fuelLabels = {
            'gasoline': 'Essence',
            'diesel': 'Diesel',
            'electric': 'Électrique',
            'hybrid': 'Hybride'
        };

        card.innerHTML = `
            ${rankBadge}
            <div class="vehicle-header">
                <h3>${proposal.vehicle.brand} ${proposal.vehicle.model}</h3>
                <div class="vehicle-type">${typeLabels[proposal.vehicle.type] || proposal.vehicle.type}</div>
            </div>
            <div class="vehicle-details">
                <div class="detail-row">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value">${typeLabels[proposal.vehicle.type] || proposal.vehicle.type}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Carburant:</span>
                    <span class="detail-value">${fuelLabels[proposal.vehicle.fuel_type] || proposal.vehicle.fuel_type}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Places:</span>
                    <span class="detail-value">${proposal.vehicle.seats}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Tarif journalier:</span>
                    <span class="detail-value">${proposal.vehicle.daily_rate} €</span>
                </div>
            </div>
            <div class="vehicle-price">
                <span class="price-label">Prix total:</span>
                <span class="price-value">${proposal.price} €</span>
            </div>
            <button class="btn-select-vehicle" data-proposal-id="${proposal.id}">
                Réserver ce véhicule
            </button>
        `;

        // Ajouter l'événement de clic sur le bouton
        const selectBtn = card.querySelector('.btn-select-vehicle');
        selectBtn.addEventListener('click', function() {
            handleVehicleSelection(proposal.id);
        });

        return card;
    }

    // Gérer la sélection d'un véhicule
    async function handleVehicleSelection(proposalId) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(`/location-proposals/${proposalId}/accept`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = '/profile?reservation=success';
            } else {
                throw new Error(result.message || 'Erreur lors de la réservation');
            }
        } catch (error) {
            console.error('Erreur:', error);
            
            // Afficher un message d'erreur dans la page
            errorDiv.className = 'alert alert-error';
            errorDiv.style.margin = '1rem 0';
            errorDiv.innerHTML = `
                <p style="margin: 0; font-weight: 600;">❌ Erreur lors de la réservation</p>
                <p style="margin: 0.5rem 0 0 0;">${error.message}</p>
            `;
            proposalsGrid.insertAdjacentElement('beforebegin', errorDiv);
            
            // Supprimer le message après 5 secondes
            
    }

    // Retour au formulaire
    backToFormBtn.addEventListener('click', function() {
        form.classList.remove('hidden');
        form.reset();
        handleCategoryChange();
    });
});
