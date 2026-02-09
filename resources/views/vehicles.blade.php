@extends('layouts.app')

@section('title', 'Véhicules')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/vehicles.css') }}">
@endpush

@push('scripts')
    <script>
        // Ouvrir automatiquement le modal si un véhicule est spécifié dans l'URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const vehicleId = urlParams.get('vehicle');
            
            if (vehicleId) {
                // Trouver le bouton correspondant au véhicule
                const targetButton = document.querySelector(`.btn-reserve[data-vehicle-id="${vehicleId}"]`);
                
                if (targetButton) {
                    // Ouvrir le modal avec les données du véhicule
                    setTimeout(() => {
                        openReservationModal(
                            targetButton.dataset.vehicleId,
                            targetButton.dataset.vehicleName,
                            targetButton.dataset.vehicleRegistration,
                            targetButton.dataset.dailyRate,
                            parseInt(targetButton.dataset.seats, 10),
                            targetButton.dataset.fuelType,
                            targetButton.dataset.available === 'true',
                            targetButton.dataset.status
                        );
                        
                        // Scroller vers le véhicule
                        targetButton.closest('.vehicle-card').scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                    }, 300);
                }
            }
        });
    </script>
@endpush

@section('content')
    <div class="vehicles-page">
        <div class="page-header">
            <h1>Notre flotte de véhicules</h1>
            <p>Découvrez tous nos véhicules disponibles à la location</p>
        </div>

        {{-- Section Voitures --}}
        @if($vehiclesByType['car']->isNotEmpty())
            <div class="vehicle-section">
                <h2 class="section-title">
                    <span class="icon">🚗</span> Voitures
                    <span class="count">{{ $vehiclesByType['car']->count() }} véhicule(s)</span>
                </h2>
                <div class="vehicles-grid">
                    @foreach($vehiclesByType['car'] as $vehicle)
                        @include('partials.vehicle-card', ['vehicle' => $vehicle])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Section Motos --}}
        @if($vehiclesByType['motorcycle']->isNotEmpty())
            <div class="vehicle-section">
                <h2 class="section-title">
                    <span class="icon">🏍️</span> Motos
                    <span class="count">{{ $vehiclesByType['motorcycle']->count() }} véhicule(s)</span>
                </h2>
                <div class="vehicles-grid">
                    @foreach($vehiclesByType['motorcycle'] as $vehicle)
                        @include('partials.vehicle-card', ['vehicle' => $vehicle])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Section Vans --}}
        @if($vehiclesByType['van']->isNotEmpty())
            <div class="vehicle-section">
                <h2 class="section-title">
                    <span class="icon">🚐</span> Vans
                    <span class="count">{{ $vehiclesByType['van']->count() }} véhicule(s)</span>
                </h2>
                <div class="vehicles-grid">
                    @foreach($vehiclesByType['van'] as $vehicle)
                        @include('partials.vehicle-card', ['vehicle' => $vehicle])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Section Sportives --}}
        @if($vehiclesByType['sport']->isNotEmpty())
            <div class="vehicle-section">
                <h2 class="section-title">
                    <span class="icon">🏎️</span> Sportives
                    <span class="count">{{ $vehiclesByType['sport']->count() }} véhicule(s)</span>
                </h2>
                <div class="vehicles-grid">
                    @foreach($vehiclesByType['sport'] as $vehicle)
                        @include('partials.vehicle-card', ['vehicle' => $vehicle])
                    @endforeach
                </div>
            </div>
        @endif

        @if($vehiclesByType['car']->isEmpty() && $vehiclesByType['motorcycle']->isEmpty() && 
            $vehiclesByType['van']->isEmpty() && $vehiclesByType['sport']->isEmpty())
            <div class="no-vehicles">
                <p>Aucun véhicule disponible pour le moment.</p>
            </div>
        @endif
    </div>
@endsection