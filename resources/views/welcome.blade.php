@extends('layouts.app')

@section('title', 'Accueil')

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
