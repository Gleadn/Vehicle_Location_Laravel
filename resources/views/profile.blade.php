@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="profile-container">
        <div class="profile-box">
            <h1>Mon Profil</h1>
            
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
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
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush