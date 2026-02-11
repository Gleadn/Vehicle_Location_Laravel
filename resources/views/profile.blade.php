@extends('layouts.app')

@section('title', 'Profil')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('content')
    <div class="profile-container">
        <div class="profile-box">
            <h1>Mon Profil</h1>
            
            @if (session('success') || request()->get('reservation') === 'success')
                <div class="alert alert-success">
                    @if(request()->get('reservation') === 'success')
                        ✅ Réservation confirmée avec succès ! Vous pouvez consulter vos réservations ci-dessous.
                    @else
                        {{ session('success') }}
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Onglets de navigation -->
            <div class="tabs-navigation">
                <button class="tab-button active" data-tab="account">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Mon compte
                </button>
                <button class="tab-button" data-tab="reservations">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    Mes réservations
                    <span class="badge">{{ $locations->count() }}</span>
                </button>
            </div>

            <!-- Contenu des onglets -->
            <div class="tabs-content">
                <!-- Onglet Mon compte -->
                <div class="tab-pane active" id="tab-account">
                    <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                        @csrf
                        @method('PUT')

                        <div class="profile-content">
                            <!-- Colonne gauche : Informations en lecture seule -->
                            <div class="profile-info">
                                <div class="info-item">
                                    <label>Nom :</label>
                                    <p>{{ Auth::user()->name }}</p>
                                </div>

                                <div class="info-item">
                                    <label>Email :</label>
                                    <p>{{ Auth::user()->email }}</p>
                                </div>

                                <div class="info-item">
                                    <label>Membre depuis :</label>
                                    <p>{{ Auth::user()->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            <!-- Colonne droite : Formulaire de modification -->
                            <div class="profile-edit">
                                <div class="form-group">
                                    <label for="current_password">Ancien mot de passe</label>
                                    <input 
                                        type="password" 
                                        id="current_password" 
                                        name="current_password"
                                        placeholder="Optionnel pour changer le mot de passe"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="password">Nouveau mot de passe</label>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password"
                                        placeholder="Laissez vide pour ne pas changer"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Confirmer le mot de passe</label>
                                    <input 
                                        type="password" 
                                        id="password_confirmation" 
                                        name="password_confirmation"
                                        placeholder="Confirmation du nouveau mot de passe"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Boutons en bas -->
                        <div class="profile-actions">
                            <button type="submit" class="btn btn-primary">
                                Mettre à jour mon profil
                            </button>
                    </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-secondary">
                                    Se déconnecter
                                </button>
                            </form>

                            <form method="POST" action="{{ route('account.delete') }}" 
                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    Supprimer mon compte
                                </button>
                            </form>
                        </div>
                </div>

                <!-- Onglet Mes réservations -->
                <div class="tab-pane" id="tab-reservations">
                    <div class="reservations-container">
                        @forelse($locations as $location)
                            <div class="reservation-card">
                                <div class="reservation-header">
                                    <div class="reservation-vehicle">
                                        <h3>{{ $location->vehicle->brand }} {{ $location->vehicle->model }}</h3>
                                        <span class="vehicle-registration">{{ $location->vehicle->registration_number }}</span>
                                    </div>
                                    <div class="reservation-status status-{{ $location->status }}">
                                        {{ ucfirst($location->status) }}
                                    </div>
                                </div>

                                <div class="reservation-details">
                                    <div class="detail-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <div>
                                            <span class="detail-label">Période</span>
                                            <span class="detail-value">
                                                Du {{ \Carbon\Carbon::parse($location->start_date)->format('d/m/Y') }}
                                                au {{ \Carbon\Carbon::parse($location->end_date)->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="1" x2="12" y2="23"></line>
                                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                        </svg>
                                        <div>
                                            <span class="detail-label">Prix total</span>
                                            <span class="detail-value price">{{ number_format($location->total_price, 2) }} €</span>
                                        </div>
                                    </div>

                                    <div class="detail-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <div>
                                            <span class="detail-label">Réservé le</span>
                                            <span class="detail-value">{{ $location->created_at->format('d/m/Y à H:i') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="reservation-vehicle-info">
                                    <span class="info-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                        </svg>
                                        {{ $location->vehicle->seats }} places
                                    </span>
                                    <span class="info-badge">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="2" y1="12" x2="22" y2="12"></line>
                                        </svg>
                                        {{ ucfirst($location->vehicle->fuel_type) }}
                                    </span>
                                    <span class="info-badge">
                                        {{ ucfirst($location->vehicle->type) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="no-reservations">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <h3>Aucune réservation</h3>
                                <p>Vous n'avez pas encore effectué de réservation.</p>
                                <a href="{{ route('locationDemand') }}" class="btn btn-primary">Faire une demande de location</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Gestion des onglets
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');

        // Si réservation réussie, afficher l'onglet réservations
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('reservation') === 'success') {
            // Retirer le paramètre de l'URL sans recharger
            window.history.replaceState({}, '', '/profile');
            
            // Activer l'onglet réservations
            setTimeout(() => {
                document.querySelector('[data-tab="reservations"]')?.click();
            }, 500);
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.dataset.tab;

                // Retirer la classe active de tous les boutons et onglets
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));

                // Ajouter la classe active au bouton cliqué et à l'onglet correspondant
                this.classList.add('active');
                document.getElementById(`tab-${targetTab}`).classList.add('active');
            });
        });
    });
</script>
@endpush