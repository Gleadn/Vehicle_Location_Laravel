@extends('layouts.app')

@section('title', 'Demande de réservation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/location-demand.css') }}">
@endpush

@section('content')
    <div class="location-demand-page">
        <div class="page-header">
            <h1>Demande de réservation</h1>
            <p>Choisissez votre véhicule et faites une demande de location</p>
        </div>

        @guest
            <div class="alert alert-info" style="max-width: 800px; margin: 0 auto 2rem; padding: 1rem; background: #cfe2ff; border: 1px solid #9ec5fe; border-radius: 8px; color: #084298;">
                <strong>ℹ️ Information :</strong> Vous devez être connecté pour effectuer une réservation. 
                <a href="{{ route('login') }}" style="color: #084298; font-weight: 600; text-decoration: underline;">Se connecter</a> ou 
                <a href="{{ route('register') }}" style="color: #084298; font-weight: 600; text-decoration: underline;">créer un compte</a>.
            </div>
        @endguest

        <div class="location-demand-container">
            <!-- Formulaire de demande -->
            <form id="locationDemandForm" class="demand-form">
                @csrf
                <div class="form-section" id="section-vehicle-category">
                    <h2>Quel type de vehicule souhaitez-vous ?</h2>
                    <div class="options-grid">
                        <label class="option-card">
                            <input type="radio" name="vehicle_category" value="motorcycle">
                            <span class="option-title">Moto</span>
                            <span class="option-desc">2 roues, agile en ville</span>
                        </label>
                        <label class="option-card">
                            <input type="radio" name="vehicle_category" value="four_wheels">
                            <span class="option-title">Vehicule 4 roues</span>
                            <span class="option-desc">Voiture, van ou sportive</span>
                        </label>
                    </div>
                </div>

                <div class="form-section hidden" id="section-seats">
                    <h2>Combien de places voulez-vous ?</h2>
                    <div class="form-row">
                        <select id="seatsSelect" name="seats_required" class="form-select">
                            <option value="">Selectionner</option>
                        </select>
                    </div>
                </div>

                <div class="form-section hidden" id="section-sporty">
                    <h2>Souhaitez-vous une voiture sportive ?</h2>
                    <div class="options-grid">
                        <label class="option-card">
                            <input type="radio" name="sporty_preference" value="yes">
                            <span class="option-title">Oui</span>
                            <span class="option-desc">Priorite aux sportives</span>
                        </label>
                        <label class="option-card">
                            <input type="radio" name="sporty_preference" value="no">
                            <span class="option-title">Non</span>
                            <span class="option-desc">Peu importe</span>
                        </label>
                    </div>
                </div>

                <div class="form-section hidden" id="section-trip">
                    <h2>Quel type de trajet prevoyez-vous ?</h2>
                    <p class="help-text">Le type de trajet influence le carburant propose.</p>
                    <div class="options-grid">
                        <label class="option-card">
                            <input type="radio" name="trip_type" value="city">
                            <span class="option-title">Ville</span>
                            <span class="option-desc">Trajets courts et frequents</span>
                        </label>
                        <label class="option-card">
                            <input type="radio" name="trip_type" value="mixed">
                            <span class="option-title">Mixte</span>
                            <span class="option-desc">Ville + periurbain</span>
                        </label>
                        <label class="option-card">
                            <input type="radio" name="trip_type" value="road_trip">
                            <span class="option-title">Road trip</span>
                            <span class="option-desc">Longues distances (pas d'electrique)</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit" id="submitBtn">Proposer 3 vehicules</button>
                </div>
            </form>

            <!-- Section des propositions de véhicules (cachée par défaut) -->
            <div id="proposalsSection" class="proposals-section hidden">
                <div class="proposals-header">
                    <h2>Nos propositions pour vous</h2>
                    <p>Voici 3 véhicules qui correspondent à vos critères</p>
                </div>

                <div id="proposalsGrid" class="proposals-grid">
                    <!-- Les cartes de véhicules seront insérées ici dynamiquement -->
                </div>

                <div class="proposals-actions">
                    <button type="button" class="btn-secondary" id="backToFormBtn">Nouvelle recherche</button>
                </div>
            </div>

            <!-- Message de chargement -->
            <div id="loadingMessage" class="loading-message hidden">
                <div class="spinner"></div>
                <p>Recherche des meilleurs véhicules pour vous...</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/location-demand.js') }}"></script>
@endpush
