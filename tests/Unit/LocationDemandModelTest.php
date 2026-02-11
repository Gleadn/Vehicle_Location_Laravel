<?php

namespace Tests\Unit;

use App\Models\LocationDemand;
use App\Models\LocationProposal;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour le modèle LocationDemand
 * 
 * Ce fichier teste les relations et la logique métier
 * du modèle LocationDemand (demande de location).
 */
class LocationDemandModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 : Une demande appartient à un utilisateur
     * 
     * Test de la relation belongsTo : LocationDemand -> User
     */
    public function test_location_demand_belongs_to_user(): void
    {
        // ARRANGE
        $user = User::factory()->create(['name' => 'John Doe']);
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // ACT : Accéder à l'utilisateur via la relation
        $demandUser = $demand->user;

        // ASSERT
        $this->assertNotNull($demandUser);
        $this->assertEquals($user->id, $demandUser->id);
        $this->assertEquals('John Doe', $demandUser->name);
    }

    /**
     * Test 2 : Une demande peut avoir plusieurs propositions
     * 
     * Test de la relation hasMany : LocationDemand -> LocationProposal
     */
    public function test_location_demand_has_many_proposals(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $vehicle1 = Vehicle::factory()->create();
        $vehicle2 = Vehicle::factory()->create();

        // Créer 2 propositions pour cette demande
        LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $vehicle1->id,
            'proposed_price' => 200.00,
            'match_score' => 85,
            'rank' => 1,
            'status' => 'proposed',
        ]);

        LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $vehicle2->id,
            'proposed_price' => 220.00,
            'match_score' => 78,
            'rank' => 2,
            'status' => 'proposed',
        ]);

        // ACT
        $proposals = $demand->proposals;

        // ASSERT
        $this->assertCount(2, $proposals);
    }

    /**
     * Test 3 : Une demande spécifique a un véhicule associé
     */
    public function test_specific_demand_has_vehicle(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['model' => 'Tesla Model 3']);

        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'specific',
            'vehicle_type' => 'car',
            'vehicle_id' => $vehicle->id, // Demande pour un véhicule précis
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // ACT
        $demandVehicle = $demand->vehicle;

        // ASSERT
        $this->assertNotNull($demandVehicle);
        $this->assertEquals($vehicle->id, $demandVehicle->id);
        $this->assertEquals('Tesla Model 3', $demandVehicle->model);
    }

    /**
     * Test 4 : Une demande générique n'a pas de véhicule spécifique
     */
    public function test_generic_demand_has_no_specific_vehicle(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic', // Pas de véhicule spécifique
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // ACT & ASSERT
        $this->assertNull($demand->vehicle_id);
        $this->assertNull($demand->vehicle);
    }

    /**
     * Test 5 : Les types de demande valides
     */
    public function test_demand_type_is_valid(): void
    {
        // Les types valides
        $validTypes = ['generic', 'specific'];

        // ARRANGE & ACT
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // ASSERT
        $this->assertContains($demand->demand_type, $validTypes);
    }

    /**
     * Test 6 : Les statuts de demande valides
     */
    public function test_demand_status_is_valid(): void
    {
        // Les statuts valides
        $validStatuses = ['pending', 'processing', 'proposed', 'accepted', 'completed', 'cancelled'];

        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // ASSERT
        $this->assertContains($demand->status, $validStatuses);
    }

    /**
     * Test 7 : Mise à jour du statut d'une demande
     */
    public function test_demand_status_can_be_updated(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        // ACT : Changer le statut
        $demand->update(['status' => 'accepted']);

        // ASSERT
        $this->assertEquals('accepted', $demand->fresh()->status);
    }

    /**
     * Test 8 : Une demande a des dates de début et fin
     */
    public function test_demand_has_start_and_end_dates(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $startDate = now()->addDays(2);
        $endDate = now()->addDays(7);

        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
        ]);

        // ASSERT
        $this->assertNotNull($demand->start_date);
        $this->assertNotNull($demand->end_date);
        
        // Vérifier que ce sont des instances Carbon (Laravel)
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $demand->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $demand->end_date);
    }

    /**
     * Test 9 : Une demande peut avoir un budget maximum
     */
    public function test_demand_can_have_max_budget(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'max_budget' => 300.00,
            'status' => 'pending',
        ]);

        // ASSERT
        $this->assertEquals(300.00, $demand->max_budget);
        $this->assertIsNumeric($demand->max_budget);
    }

    /**
     * Test 10 : Récupération des propositions acceptées
     */
    public function test_demand_can_retrieve_accepted_proposal(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $vehicle = Vehicle::factory()->create();

        // Proposition refusée
        LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $vehicle->id,
            'proposed_price' => 200.00,
            'rank' => 2,
            'status' => 'rejected',
        ]);

        // Proposition acceptée
        $acceptedProposal = LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $vehicle->id,
            'proposed_price' => 180.00,
            'rank' => 1,
            'status' => 'accepted',
        ]);

        // ACT : Récupérer seulement la proposition acceptée
        // fresh() recharge depuis la BDD
        $accepted = $demand->fresh()->proposals()->where('status', 'accepted')->first();

        // ASSERT
        $this->assertNotNull($accepted);
        $this->assertEquals($acceptedProposal->id, $accepted->id);
    }

    /**
     * Test 11 : Un utilisateur peut avoir plusieurs demandes
     */
    public function test_user_can_have_multiple_demands(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'specific',
            'vehicle_type' => 'motorcycle',
            'vehicle_id' => $vehicle->id, // Requis pour demande spécifique
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
            'status' => 'accepted',
        ]);

        // ACT
        $userDemands = $user->locationDemands;

        // ASSERT
        $this->assertCount(2, $userDemands);
    }

    /**
     * Test 12 : Notes optionnelles dans une demande
     */
    public function test_demand_can_have_optional_notes(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        
        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'notes' => 'Besoin de sièges bébé',
            'status' => 'pending',
        ]);

        // ASSERT
        $this->assertEquals('Besoin de sièges bébé', $demand->notes);
    }
}
