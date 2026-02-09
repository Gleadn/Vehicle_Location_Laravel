@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
    <div class="auth-container">
        <div class="auth-box">
            <h1>Connexion</h1>

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

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
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
                </div>

                <div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Se connecter</button>
            </form>

            <p class="auth-link">
                Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a>
            </p>
        </div>
    </div>
@endsection