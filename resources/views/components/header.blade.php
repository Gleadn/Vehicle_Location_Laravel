<header class="main-header">
    <div class="header-left">

        <button class="burger-menu" id="burgerMenu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <nav class="burger-nav" id="burgerNav">
            <ul>
                <li><a href="/">Accueil</a></li>
                <li><a href="/vehicles">Véhicules</a></li>
                <li><a href="/locationDemand">Réservation</a></li>
            </ul>
        </nav>
    </div>

    <div class="header-right">
        @auth
            @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.index') }}" class="admin-btn" title="Panel Admin">
                    🛡️
                </a>
            @endif
        @endauth
        <a href="/profile" class="profile-btn">
        </a>
    </div>
</header>
