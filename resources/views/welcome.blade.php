@extends('layouts.app')

@section('title', 'Accueil')

@push('scripts')
    <script>
        // Marquer la page pour activer la redirection
        document.body.setAttribute('data-redirect-to-vehicles', 'true');
        
        // Sur la page d'accueil, rediriger vers la page véhicules au lieu d'ouvrir le modal
        document.addEventListener('DOMContentLoaded', function() {
            const reserveButtons = document.querySelectorAll('.btn-reserve[data-vehicle-id]');
            reserveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const vehicleId = this.dataset.vehicleId;
                    window.location.href = `/vehicles?vehicle=${vehicleId}`;
                });
            });
        });
    </script>
@endpush

@section('content')
    <div class="hero-section">
        <h1>Louez le véhicule parfait</h1>
        <p>Découvrez notre sélection de véhicules disponibles</p>
    </div>

    <div class="vehicles-container">
        <h2>Véhicules disponibles</h2>
        
        <div class="vehicles-grid">
            @forelse($vehicles as $vehicle)
                @include('partials.vehicle-card', ['vehicle' => $vehicle])
            @empty
                <p class="no-vehicles">Aucun véhicule disponible pour le moment.</p>
            @endforelse
        </div>
    </div>
@endsection
