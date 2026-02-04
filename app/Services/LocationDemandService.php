<?php

namespace App\Services;

use App\Models\LocationDemand;
use App\Models\LocationProposal;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class LocationDemandService
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    /**
     * Create a generic location demand.
     */
    public function createGenericDemand(
        int $userId,
        string $vehicleType,
        string $startDate,
        string $endDate,
        ?int $seatsRequired = null,
        ?float $maxBudget = null,
        ?string $notes = null
    ): LocationDemand {
        return LocationDemand::create([
            'user_id' => $userId,
            'demand_type' => 'generic',
            'vehicle_type' => $vehicleType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'seats_required' => $seatsRequired,
            'max_budget' => $maxBudget,
            'notes' => $notes,
            'status' => 'pending',
        ]);
    }

    /**
     * Create a specific location demand for a particular vehicle.
     */
    public function createSpecificDemand(
        int $userId,
        int $vehicleId,
        string $startDate,
        string $endDate,
        ?string $notes = null
    ): LocationDemand {
        $vehicle = Vehicle::findOrFail($vehicleId);

        return LocationDemand::create([
            'user_id' => $userId,
            'demand_type' => 'specific',
            'vehicle_id' => $vehicleId,
            'vehicle_type' => $vehicle->type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'notes' => $notes,
            'status' => 'pending',
        ]);
    }

    /**
     * Process a demand and create proposals.
     */
    public function processDemand(LocationDemand $demand): Collection
    {
        // Marquer comme en traitement
        $demand->update([
            'status' => 'processing',
            'processed_at' => now(),
        ]);

        if ($demand->isSpecific()) {
            return $this->createSpecificProposals($demand);
        }

        return $this->createGenericProposals($demand);
    }

    /**
     * Create proposals for a specific demand.
     * Rank 1: requested vehicle
     * Rank 2-3: similar vehicles
     */
    protected function createSpecificProposals(LocationDemand $demand): Collection
    {
        $requestedVehicle = $demand->requestedVehicle;
        $proposals = collect();

        // Proposition 1 : Le véhicule demandé
        $price = $this->vehicleService->calculateTotalPrice(
            $requestedVehicle,
            $demand->start_date,
            $demand->end_date
        );

        $proposals->push(LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $requestedVehicle->id,
            'proposed_price' => $price,
            'rank' => 1,
        ]));

        // Propositions 2-3 : Véhicules similaires (même type)
        $similarVehicles = $this->findSimilarVehicles($requestedVehicle, 2);

        foreach ($similarVehicles as $index => $vehicle) {
            $price = $this->vehicleService->calculateTotalPrice(
                $vehicle,
                $demand->start_date,
                $demand->end_date
            );

            $proposals->push(LocationProposal::create([
                'location_demand_id' => $demand->id,
                'vehicle_id' => $vehicle->id,
                'proposed_price' => $price,
                'rank' => $index + 2,
            ]));
        }

        // Marquer comme proposé
        $demand->update(['status' => 'proposed']);

        return $proposals;
    }

    /**
     * Create proposals for a generic demand.
     * 3 best matching vehicles based on criteria.
     */
    protected function createGenericProposals(LocationDemand $demand): Collection
    {
        $criteria = [
            'type' => $demand->vehicle_type,
        ];

        if ($demand->seats_required) {
            $criteria['seats'] = $demand->seats_required;
        }

        if ($demand->max_budget) {
            $criteria['max_budget'] = $demand->max_budget;
        }

        // Rechercher les véhicules correspondants
        $vehicles = $this->vehicleService->searchVehicles($criteria);

        // Prendre les 3 premiers (déjà triés par prix)
        $topVehicles = $vehicles->take(3);

        $proposals = collect();

        foreach ($topVehicles as $index => $vehicle) {
            $price = $this->vehicleService->calculateTotalPrice(
                $vehicle,
                $demand->start_date,
                $demand->end_date
            );

            $proposals->push(LocationProposal::create([
                'location_demand_id' => $demand->id,
                'vehicle_id' => $vehicle->id,
                'proposed_price' => $price,
                'rank' => $index + 1,
            ]));
        }

        // Marquer comme proposé
        $demand->update(['status' => 'proposed']);

        return $proposals;
    }

    /**
     * Find similar vehicles (same type, different from the given vehicle).
     */
    protected function findSimilarVehicles(Vehicle $vehicle, int $limit = 2): Collection
    {
        return Vehicle::available()
            ->where('type', $vehicle->type)
            ->where('id', '!=', $vehicle->id)
            ->orderBy('daily_rate', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Accept a proposal and create the location.
     */
    public function acceptProposal(LocationProposal $proposal): \App\Models\Location
    {
        $demand = $proposal->locationDemand;

        // Marquer la proposition comme sélectionnée
        $proposal->update(['selected' => true]);

        // Créer la location
        $location = \App\Models\Location::create([
            'user_id' => $demand->user_id,
            'vehicle_id' => $proposal->vehicle_id,
            'start_date' => $demand->start_date,
            'end_date' => $demand->end_date,
            'status' => 'confirmed',
            'total_price' => $proposal->proposed_price,
        ]);

        // Mettre à jour la demande
        $demand->update(['status' => 'accepted']);

        // Marquer le véhicule comme indisponible (optionnel)
        // $this->vehicleService->markAsUnavailable($proposal->vehicle);

        return $location;
    }

    /**
     * Reject a demand.
     */
    public function rejectDemand(LocationDemand $demand, string $reason = null): bool
    {
        return $demand->update([
            'status' => 'rejected',
            'notes' => $demand->notes ? $demand->notes . "\n\nRejet: " . $reason : "Rejet: " . $reason,
        ]);
    }

    /**
     * Get demand statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total' => LocationDemand::count(),
            'pending' => LocationDemand::where('status', 'pending')->count(),
            'processing' => LocationDemand::where('status', 'processing')->count(),
            'proposed' => LocationDemand::where('status', 'proposed')->count(),
            'accepted' => LocationDemand::where('status', 'accepted')->count(),
            'rejected' => LocationDemand::where('status', 'rejected')->count(),
            'by_type' => [
                'generic' => LocationDemand::where('demand_type', 'generic')->count(),
                'specific' => LocationDemand::where('demand_type', 'specific')->count(),
            ],
        ];
    }
}
