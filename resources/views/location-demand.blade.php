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

        <div class="location-demand-container">
            <!-- Le formulaire sera ajouté ici -->
            <p class="placeholder-text">Formulaire de demande de réservation à venir...</p>
        </div>
    </div>
@endsection
