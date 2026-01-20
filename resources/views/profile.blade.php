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

                <div class="form-group">
                    <label for="name">Nom</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', Auth::user()->name) }}" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', Auth::user()->email) }}" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Nouveau mot de passe (optionnel)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                    >
                    <small>Laissez vide pour conserver l'actuel</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le nouveau mot de passe</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation"
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Mettre à jour mon profil
                </button>
            </form>

            <div class="info-item">
                <label>Membre depuis :</label>
                <p>{{ Auth::user()->created_at->format('d/m/Y') }}</p>
            </div>

            <div class="profile-actions">
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