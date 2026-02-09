// Formulaire de demande de reservation (modulaire)

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

    // Gestion de la soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // Ajouter le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Afficher le chargement
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
            alert('Une erreur est survenue. Veuillez réessayer.');
            loadingMessage.classList.add('hidden');
            form.classList.remove('hidden');
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
                alert('Réservation confirmée! Vous allez être redirigé vers votre profil.');
                // Rediriger vers le profil ou la page de confirmation
                window.location.href = '/profile';
            } else {
                throw new Error(result.message || 'Erreur lors de la réservation');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la réservation. Veuillez réessayer.');
        }
    }

    // Retour au formulaire
    backToFormBtn.addEventListener('click', function() {
        proposalsSection.classList.add('hidden');
        form.classList.remove('hidden');
        form.reset();
        handleCategoryChange();
    });
});
