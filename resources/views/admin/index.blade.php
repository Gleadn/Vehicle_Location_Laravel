@extends('layouts.app')

@section('title', 'Panel Admin')

@push('styles')
<style>
.admin-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.admin-header {
    text-align: center;
    padding: 2rem 0 3rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    margin-bottom: 3rem;
}

.admin-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
}

.admin-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card.metric-card {
    cursor: pointer;
    border: 2px solid transparent;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.stat-card.metric-card.is-active {
    border-color: #667eea;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.25);
}

.stat-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f0f0f0;
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.stat-icon.users {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.vehicles {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-icon.locations {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-icon.demands {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-title {
    font-size: 1.1rem;
    color: #666;
    font-weight: 600;
}

.stat-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label {
    color: #666;
    font-size: 0.95rem;
}

.stat-value {
    font-weight: 700;
    font-size: 1.3rem;
    color: #333;
}

.stat-value.primary {
    color: #667eea;
    font-size: 2rem;
}

.data-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.metric-details {
    margin-bottom: 2rem;
}

.metric-placeholder {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    color: #777;
    font-weight: 600;
}

.metric-placeholder.is-hidden,
.metric-panel.is-hidden {
    display: none;
}

.section-title {
    font-size: 1.5rem;
    margin: 0 0 1.5rem 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
}

.data-table thead {
    background: #f8f9fa;
}

.data-table th {
    text-align: left;
    padding: 1rem;
    font-weight: 600;
    color: #555;
    border-bottom: 2px solid #e5e5e5;
}

.data-table th.sortable {
    cursor: pointer;
    user-select: none;
}

.data-table th.sortable.is-sorted-asc::after {
    content: " ^";
    font-size: 0.75rem;
    color: #999;
}

.data-table th.sortable.is-sorted-desc::after {
    content: " v";
    font-size: 0.75rem;
    color: #999;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge.admin {
    background: #667eea;
    color: white;
}

.badge.user {
    background: #e5e5e5;
    color: #666;
}

.badge.confirmed {
    background: #d4edda;
    color: #155724;
}

.badge.active {
    background: #cfe2ff;
    color: #084298;
}

.badge.completed {
    background: #e2e3e5;
    color: #383d41;
}

.badge.cancelled {
    background: #f8d7da;
    color: #842029;
}

.badge.pending {
    background: #fff3cd;
    color: #856404;
}

.badge.processing {
    background: #cfe2ff;
    color: #084298;
}

.badge.proposed {
    background: #e2e3ff;
    color: #3d2c8d;
}

.badge.accepted {
    background: #d1e7dd;
    color: #0f5132;
}

.badge.rejected {
    background: #f8d7da;
    color: #842029;
}

.badge.expired {
    background: #e2e3e5;
    color: #41464b;
}

.badge.maintenance {
    background: #ffe5b4;
    color: #8a5a00;
}

.badge.out_of_service {
    background: #f8d7da;
    color: #842029;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #999;
}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-header">
        <h1>🛡️ Panel Administrateur</h1>
        <p>Tableau de bord et statistiques</p>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <!-- Utilisateurs -->
        <div class="stat-card metric-card" data-target="metric-users" role="button" tabindex="0" aria-controls="metric-users" aria-expanded="false">
            <div class="stat-card-header">
                <div class="stat-icon users">👥</div>
                <span class="stat-title">Utilisateurs</span>
            </div>
            <div class="stat-details">
                <div class="stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-value primary">{{ $stats['users']['total'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Admins</span>
                    <span class="stat-value">{{ $stats['users']['admins'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Utilisateurs</span>
                    <span class="stat-value">{{ $stats['users']['regular'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Nouveaux (7j)</span>
                    <span class="stat-value">{{ $stats['users']['recent'] }}</span>
                </div>
            </div>
        </div>

        <!-- Véhicules -->
        <div class="stat-card metric-card" data-target="metric-vehicles" role="button" tabindex="0" aria-controls="metric-vehicles" aria-expanded="false">
            <div class="stat-card-header">
                <div class="stat-icon vehicles">🚗</div>
                <span class="stat-title">Véhicules</span>
            </div>
            <div class="stat-details">
                <div class="stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-value primary">{{ $stats['vehicles']['total'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Disponibles</span>
                    <span class="stat-value">{{ $stats['vehicles']['available'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Maintenance</span>
                    <span class="stat-value">{{ $stats['vehicles']['maintenance'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Indisponibles</span>
                    <span class="stat-value">{{ $stats['vehicles']['unavailable'] }}</span>
                </div>
            </div>
        </div>

        <!-- Réservations -->
        <div class="stat-card metric-card" data-target="metric-locations" role="button" tabindex="0" aria-controls="metric-locations" aria-expanded="false">
            <div class="stat-card-header">
                <div class="stat-icon locations">📅</div>
                <span class="stat-title">Réservations</span>
            </div>
            <div class="stat-details">
                <div class="stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-value primary">{{ $stats['locations']['total'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Confirmées</span>
                    <span class="stat-value">{{ $stats['locations']['confirmed'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Actives</span>
                    <span class="stat-value">{{ $stats['locations']['active'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Terminées</span>
                    <span class="stat-value">{{ $stats['locations']['completed'] }}</span>
                </div>
            </div>
        </div>

        <!-- Demandes -->
        <div class="stat-card metric-card" data-target="metric-demands" role="button" tabindex="0" aria-controls="metric-demands" aria-expanded="false">
            <div class="stat-card-header">
                <div class="stat-icon demands">📋</div>
                <span class="stat-title">Demandes</span>
            </div>
            <div class="stat-details">
                <div class="stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-value primary">{{ $stats['demands']['total'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">En attente</span>
                    <span class="stat-value">{{ $stats['demands']['pending'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">En traitement</span>
                    <span class="stat-value">{{ $stats['demands']['processing'] }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Propositions</span>
                    <span class="stat-value">{{ $stats['demands']['proposed'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="metric-details">
        <div class="metric-placeholder">Cliquez sur une carte pour afficher les details.</div>

        <!-- Utilisateurs -->
        <div class="data-section metric-panel is-hidden" id="metric-users">
            <h2 class="section-title">👥 Derniers utilisateurs inscrits</h2>
            @if($recentUsers->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentUsers as $user)
                            <tr>
                                <td>#{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge {{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucun utilisateur pour le moment</div>
            @endif
        </div>

        <!-- Vehicules -->
        <div class="data-section metric-panel is-hidden" id="metric-vehicles">
            <h2 class="section-title">🚗 Derniers vehicules ajoutes</h2>
            @if($recentVehicles->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehicule</th>
                            <th>Immatriculation</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Disponible</th>
                            <th>Tarif/jour</th>
                            <th>Ajoute le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentVehicles as $vehicle)
                            <tr>
                                <td>#{{ $vehicle->id }}</td>
                                <td>{{ $vehicle->brand }} {{ $vehicle->model }}</td>
                                <td>{{ $vehicle->registration_number }}</td>
                                <td>{{ ucfirst($vehicle->type) }}</td>
                                <td><span class="badge {{ $vehicle->status }}">{{ ucfirst(str_replace('_', ' ', $vehicle->status)) }}</span></td>
                                <td>{{ $vehicle->available ? 'Oui' : 'Non' }}</td>
                                <td>{{ number_format($vehicle->daily_rate, 2) }} €</td>
                                <td>{{ $vehicle->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucun vehicule pour le moment</div>
            @endif
        </div>

        <!-- Reservations -->
        <div class="data-section metric-panel is-hidden" id="metric-locations">
            <h2 class="section-title">📅 Dernieres reservations</h2>
            @if($recentLocations->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Vehicule</th>
                            <th>Periode</th>
                            <th>Prix</th>
                            <th>Statut</th>
                            <th>Date reservation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLocations as $location)
                            <tr>
                                <td>#{{ $location->id }}</td>
                                <td>{{ $location->user->name }}</td>
                                <td>{{ $location->vehicle->brand }} {{ $location->vehicle->model }}</td>
                                <td>{{ \Carbon\Carbon::parse($location->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($location->end_date)->format('d/m/Y') }}</td>
                                <td>{{ number_format($location->total_price, 2) }} €</td>
                                <td><span class="badge {{ $location->status }}">{{ ucfirst($location->status) }}</span></td>
                                <td>{{ $location->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucune reservation pour le moment</div>
            @endif
        </div>

        <!-- Demandes -->
        <div class="data-section metric-panel is-hidden" id="metric-demands">
            <h2 class="section-title">📋 Dernieres demandes</h2>
            @if($recentDemands->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Type</th>
                            <th>Vehicule</th>
                            <th>Periode</th>
                            <th>Budget max</th>
                            <th>Statut</th>
                            <th>Demande le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDemands as $demand)
                            <tr>
                                <td>#{{ $demand->id }}</td>
                                <td>{{ $demand->user->name }}</td>
                                <td>{{ ucfirst($demand->demand_type) }}</td>
                                <td>
                                    @if($demand->isSpecific() && $demand->requestedVehicle)
                                        {{ $demand->requestedVehicle->brand }} {{ $demand->requestedVehicle->model }}
                                    @else
                                        {{ ucfirst($demand->vehicle_type) }}
                                    @endif
                                </td>
                                <td>{{ $demand->start_date->format('d/m/Y') }} - {{ $demand->end_date->format('d/m/Y') }}</td>
                                <td>
                                    @if($demand->max_budget)
                                        {{ number_format($demand->max_budget, 2) }} €
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><span class="badge {{ $demand->status }}">{{ ucfirst($demand->status) }}</span></td>
                                <td>{{ $demand->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Aucune demande pour le moment</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const metricCards = document.querySelectorAll('.stat-card.metric-card[data-target]');
    const metricPanels = document.querySelectorAll('.metric-panel');
    const metricPlaceholder = document.querySelector('.metric-placeholder');

    const hideAllPanels = () => {
        metricPanels.forEach((panel) => panel.classList.add('is-hidden'));
    };

    const deactivateCards = () => {
        metricCards.forEach((card) => {
            card.classList.remove('is-active');
            card.setAttribute('aria-expanded', 'false');
        });
    };

    const showPanel = (card) => {
        const targetId = card.getAttribute('data-target');
        const targetPanel = document.getElementById(targetId);

        if (!targetPanel) {
            return;
        }

        hideAllPanels();
        deactivateCards();

        targetPanel.classList.remove('is-hidden');
        card.classList.add('is-active');
        card.setAttribute('aria-expanded', 'true');

        if (metricPlaceholder) {
            metricPlaceholder.classList.add('is-hidden');
        }

        targetPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    metricCards.forEach((card) => {
        card.addEventListener('click', () => showPanel(card));
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                showPanel(card);
            }
        });
    });

    const extractDateValue = (text) => {
        const trimmed = text.trim();
        const parts = trimmed.split(' - ');
        const dateRegex = /^(\d{2})\/(\d{2})\/(\d{4})(?:\s+(\d{2}):(\d{2}))?$/;

        for (const part of parts) {
            const match = part.trim().match(dateRegex);
            if (match) {
                const day = parseInt(match[1], 10);
                const month = parseInt(match[2], 10) - 1;
                const year = parseInt(match[3], 10);
                const hour = match[4] ? parseInt(match[4], 10) : 0;
                const minute = match[5] ? parseInt(match[5], 10) : 0;
                return new Date(year, month, day, hour, minute).getTime();
            }
        }

        return null;
    };

    const extractNumericValue = (text) => {
        let normalized = text.replace(/[^0-9,.-]/g, '').trim();

        if (!normalized) {
            return null;
        }

        if (normalized.includes(',') && normalized.includes('.')) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        } else {
            normalized = normalized.replace(',', '.');
        }

        const value = parseFloat(normalized);

        return Number.isNaN(value) ? null : value;
    };

    const getCellValue = (cell) => cell.textContent.replace(/\s+/g, ' ').trim();

    const sortTableByColumn = (table, columnIndex, direction) => {
        const tbody = table.querySelector('tbody');
        if (!tbody) {
            return;
        }

        const rows = Array.from(tbody.querySelectorAll('tr'));
        const multiplier = direction === 'desc' ? -1 : 1;

        rows.sort((rowA, rowB) => {
            const cellA = rowA.children[columnIndex];
            const cellB = rowB.children[columnIndex];

            if (!cellA || !cellB) {
                return 0;
            }

            const valueA = getCellValue(cellA);
            const valueB = getCellValue(cellB);

            const dateA = extractDateValue(valueA);
            const dateB = extractDateValue(valueB);

            if (dateA !== null && dateB !== null) {
                return (dateA - dateB) * multiplier;
            }

            const numericA = extractNumericValue(valueA);
            const numericB = extractNumericValue(valueB);

            if (numericA !== null && numericB !== null) {
                return (numericA - numericB) * multiplier;
            }

            return valueA.localeCompare(valueB, 'fr', { sensitivity: 'base' }) * multiplier;
        });

        rows.forEach((row) => tbody.appendChild(row));
    };

    const initTableSorting = () => {
        const tables = document.querySelectorAll('.data-table');

        tables.forEach((table) => {
            const headers = table.querySelectorAll('thead th');

            if (headers.length > 0) {
                headers.forEach((other) => {
                    other.classList.remove('is-sorted-asc', 'is-sorted-desc');
                });
                headers[0].classList.add('is-sorted-asc');
                sortTableByColumn(table, 0, 'asc');
            }

            headers.forEach((header, index) => {
                header.classList.add('sortable');

                header.addEventListener('click', () => {
                    const isDesc = header.classList.contains('is-sorted-asc');
                    const nextDirection = isDesc ? 'desc' : 'asc';

                    headers.forEach((other) => {
                        other.classList.remove('is-sorted-asc', 'is-sorted-desc');
                    });

                    header.classList.add(nextDirection === 'asc' ? 'is-sorted-asc' : 'is-sorted-desc');
                    sortTableByColumn(table, index, nextDirection);
                });
            });
        });
    };

    initTableSorting();
</script>
@endpush
