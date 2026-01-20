@extends('layouts.auth')

@section('title', 'Inscription')

@section('content')
    <div class="auth-container">
        <div class="auth-box">
            <h1>Inscription</h1>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Nom</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                    >
                    <small>Minimum 8 caractères</small>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmer le mot de passe</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>

            <p class="auth-link">
                Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
            </p>
        </div>
    </div>
@endsection
