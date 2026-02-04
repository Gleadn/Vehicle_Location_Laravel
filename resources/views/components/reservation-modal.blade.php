<!-- Modal de réservation -->
<div id="reservationModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Réservation de véhicule</h3>
            <button class="modal-close" onclick="closeReservationModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="vehicle-info-summary">
                <div class="vehicle-info-text">
                    <h4 id="modal-vehicle-name"></h4>
                    <p id="modal-vehicle-registration"></p>
                </div>
                <div class="vehicle-availability-indicator" id="modal-vehicle-availability"></div>
            </div>

            <form id="reservationForm">
                <input type="hidden" id="modal-vehicle-id" name="vehicle_id">

                <div class="form-section">
                    <h5>Critères de recherche</h5>
                    <p class="help-text">Sélectionnez jusqu'à 2 critères importants pour votre réservation</p>
                    
                    <div class="criteria-options">
                        <label class="checkbox-option">
                            <input type="checkbox" name="criteria[]" value="price" id="criteria-price">
                            <span class="checkbox-label">
                                <span class="checkbox-title">Prix</span>
                                <span class="checkbox-value" id="criteria-price-value"></span>
                            </span>
                        </label>

                        <label class="checkbox-option">
                            <input type="checkbox" name="criteria[]" value="seats" id="criteria-seats">
                            <span class="checkbox-label">
                                <span class="checkbox-title">Nombre de places</span>
                                <span class="checkbox-value" id="criteria-seats-value"></span>
                            </span>
                        </label>

                        <label class="checkbox-option">
                            <input type="checkbox" name="criteria[]" value="fuel_type" id="criteria-fuel">
                            <span class="checkbox-label">
                                <span class="checkbox-title">Type de carburant</span>
                                <span class="checkbox-value" id="criteria-fuel-value"></span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeReservationModal()">Annuler</button>
                    <button type="submit" class="btn-submit">Valider la demande</button>
                </div>
            </form>
        </div>
    </div>
</div>
