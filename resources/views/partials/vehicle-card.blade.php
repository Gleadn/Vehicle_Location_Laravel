<div class="vehicle-card">
    <div class="vehicle-type-badge {{ $vehicle->type }}">
        {{ ucfirst($vehicle->type) }}
    </div>
    
    <div class="vehicle-info">
        <h3>{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
        <p class="registration">{{ $vehicle->registration_number }}</p>
        
        <div class="vehicle-details">
            <span class="detail">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                {{ $vehicle->seats }} places
            </span>
            
            <span class="detail">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                {{ ucfirst($vehicle->fuel_type) }}
            </span>
            
            <span class="detail">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                {{ number_format($vehicle->mileage, 0, ',', ' ') }} km
            </span>
        </div>
        
        <div class="vehicle-status">
            @if($vehicle->available && $vehicle->status === 'active')
                <span class="badge-available">Disponible</span>
            @elseif($vehicle->status === 'maintenance')
                <span class="badge-maintenance">En maintenance</span>
            @else
                <span class="badge-unavailable">Indisponible</span>
            @endif
        </div>
        
        <div class="vehicle-price">
            <span class="price">{{ number_format($vehicle->daily_rate, 2) }} €</span>
            <span class="period">/ jour</span>
        </div>
        
        <button class="btn-reserve" onclick="openReservationModal(
            {{ $vehicle->id }},
            '{{ $vehicle->brand }} {{ $vehicle->model }}',
            '{{ $vehicle->registration_number }}',
            '{{ number_format($vehicle->daily_rate, 2) }}',
            {{ $vehicle->seats }},
            '{{ $vehicle->fuel_type }}',
            {{ $vehicle->available ? 'true' : 'false' }},
            '{{ $vehicle->status }}'
        )">Réserver</button>
    </div>
</div>
