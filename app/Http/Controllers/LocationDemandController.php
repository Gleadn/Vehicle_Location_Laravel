<?php

namespace App\Http\Controllers;

use App\Services\LocationDemandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationDemandController extends Controller
{
    public function __construct(
        protected LocationDemandService $locationDemandService
    ) {}

    /**
     * Store a new location demand and return vehicle proposals.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_category' => 'required|string|in:motorcycle,four_wheels',
            'seats_required' => 'required|integer|min:1',
            'trip_type' => 'required|string|in:city,mixed,road_trip',
            'sporty_preference' => 'nullable|string|in:yes,no',
        ]);

        // Déterminer le type de véhicule Laravel basé sur la catégorie
        $vehicleType = $this->mapCategoryToType($validated['vehicle_category']);

        // Si c'est une voiture à 2 places avec préférence sportive
        if ($validated['vehicle_category'] === 'four_wheels' 
            && $validated['seats_required'] == 2 
            && ($validated['sporty_preference'] ?? null) === 'yes') {
            $vehicleType = 'sport';
        }

        // Créer la demande de location (utilisateur doit être connecté)
        $demand = $this->locationDemandService->createGenericDemand(
            userId: Auth::id(),
            vehicleType: $vehicleType,
            startDate: now()->addDay()->format('Y-m-d'), // Date de début par défaut (demain)
            endDate: now()->addDays(3)->format('Y-m-d'), // Date de fin par défaut (dans 3 jours)
            seatsRequired: $validated['seats_required'],
            notes: $this->generateDemandNotes($validated)
        );

        // Traiter la demande et obtenir les propositions
        $proposals = $this->locationDemandService->processDemand($demand);

        // Charger les relations pour retourner les données complètes
        $proposals->load('vehicle');

        return response()->json([
            'success' => true,
            'demand_id' => $demand->id,
            'proposals' => $proposals->map(function ($proposal) {
                return [
                    'id' => $proposal->id,
                    'rank' => $proposal->rank,
                    'price' => number_format($proposal->proposed_price, 2),
                    'vehicle' => [
                        'id' => $proposal->vehicle->id,
                        'brand' => $proposal->vehicle->brand,
                        'model' => $proposal->vehicle->model,
                        'type' => $proposal->vehicle->type,
                        'fuel_type' => $proposal->vehicle->fuel_type,
                        'seats' => $proposal->vehicle->seats,
                        'daily_rate' => number_format($proposal->vehicle->daily_rate, 2),
                        'description' => $proposal->vehicle->description,
                    ],
                ];
            }),
        ]);
    }

    /**
     * Accept a vehicle proposal and create the location.
     */
    public function acceptProposal(Request $request, int $proposalId)
    {
        $proposal = \App\Models\LocationProposal::findOrFail($proposalId);

        // Vérifier que la demande appartient à l'utilisateur
        if ($proposal->locationDemand->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $location = $this->locationDemandService->acceptProposal($proposal);

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée!',
            'location_id' => $location->id,
        ]);
    }

    /**
     * Map the frontend category to Laravel vehicle type.
     */
    protected function mapCategoryToType(string $category): string
    {
        return match($category) {
            'motorcycle' => 'motorcycle',
            'four_wheels' => 'car', // Par défaut, four_wheels = car
            default => 'car',
        };
    }

    /**
     * Generate notes based on user preferences.
     */
    protected function generateDemandNotes(array $data): string
    {
        $notes = [];
        
        $notes[] = "Type de trajet: " . match($data['trip_type']) {
            'city' => 'Ville',
            'mixed' => 'Mixte',
            'road_trip' => 'Road trip',
            default => $data['trip_type'],
        };

        if (isset($data['sporty_preference'])) {
            $notes[] = "Préférence sportive: " . ($data['sporty_preference'] === 'yes' ? 'Oui' : 'Non');
        }

        return implode("\n", $notes);
    }

    /**
     * Create a specific vehicle reservation.
     */
    public function reserveVehicle(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'criteria' => 'nullable|array',
            'criteria.*' => 'string|in:price,seats,fuel',
        ]);

        $vehicleId = $validated['vehicle_id'];
        $criteria = $validated['criteria'] ?? [];

        // Créer une demande spécifique pour ce véhicule
        $demand = $this->locationDemandService->createSpecificDemand(
            userId: Auth::id(),
            vehicleId: $vehicleId,
            startDate: now()->addDay()->format('Y-m-d'),
            endDate: now()->addDays(3)->format('Y-m-d'),
            notes: 'Critères sélectionnés: ' . (count($criteria) > 0 ? implode(', ', $criteria) : 'Aucun')
        );

        // Traiter la demande et obtenir les propositions
        $proposals = $this->locationDemandService->processDemand($demand);

        // Accepter automatiquement la première proposition (le véhicule demandé)
        $firstProposal = $proposals->first();
        if ($firstProposal) {
            $location = $this->locationDemandService->acceptProposal($firstProposal);

            return response()->json([
                'success' => true,
                'message' => 'Réservation confirmée!',
                'location_id' => $location->id,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Impossible de créer la réservation',
        ], 400);
    }
}
