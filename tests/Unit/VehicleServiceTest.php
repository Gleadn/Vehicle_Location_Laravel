<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use App\Services\VehicleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour VehicleService
 * 
 * Ce fichier teste la logique métier du service VehicleService.
 * Les services encapsulent la logique complexe et sont facilement testables
 * car ils ne dépendent pas des routes ou du HTTP.
 */
class VehicleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected VehicleService $vehicleService;

    /**
     * setUp() : Méthode spéciale exécutée AVANT chaque test
     * 
     * Pratique pour initialiser des objets communs à tous les tests.
     * Évite la duplication de code.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->vehicleService = new VehicleService();
    }

    /**
     * Test 1 : getCheapestVehicles() retourne les véhicules triés par prix
     */
    public function test_get_cheapest_vehicles_returns_vehicles_ordered_by_price(): void
    {
        // ARRANGE : Créer 3 véhicules avec des prix différents
        Vehicle::factory()->create([
            'model' => 'Expensive Car',
            'daily_rate' => 100.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'model' => 'Cheap Car',
            'daily_rate' => 30.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'model' => 'Medium Car',
            'daily_rate' => 60.00,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Récupérer les véhicules les moins chers
        $vehicles = $this->vehicleService->getCheapestVehicles(3);

        // ASSERT : Vérifier l'ordre (du moins cher au plus cher)
        $this->assertCount(3, $vehicles);
        $this->assertEquals(30.00, $vehicles[0]->daily_rate);
        $this->assertEquals(60.00, $vehicles[1]->daily_rate);
        $this->assertEquals(100.00, $vehicles[2]->daily_rate);
    }

    /**
     * Test 2 : getCheapestVehicles() respecte la limite
     */
    public function test_get_cheapest_vehicles_respects_limit(): void
    {
        // ARRANGE : Créer 5 véhicules
        Vehicle::factory()->count(5)->create([
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Demander seulement 3 véhicules
        $vehicles = $this->vehicleService->getCheapestVehicles(3);

        // ASSERT : Vérifier qu'on a bien 3 véhicules
        $this->assertCount(3, $vehicles);
    }

    /**
     * Test 3 : getCheapestVehicles() exclut les véhicules non disponibles
     */
    public function test_get_cheapest_vehicles_excludes_unavailable_vehicles(): void
    {
        // ARRANGE : Créer des véhicules disponibles et non disponibles
        Vehicle::factory()->create([
            'daily_rate' => 20.00,
            'available' => true, // Disponible
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'daily_rate' => 10.00,
            'available' => false, // NON disponible (devrait être exclu)
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'daily_rate' => 15.00,
            'available' => true,
            'status' => 'maintenance', // En maintenance (devrait être exclu)
        ]);

        // ACT
        $vehicles = $this->vehicleService->getCheapestVehicles(10);

        // ASSERT : Seulement 1 véhicule (le disponible à 20€)
        $this->assertCount(1, $vehicles);
        $this->assertEquals(20.00, $vehicles[0]->daily_rate);
    }

    /**
     * Test 4 : searchVehicles() filtre par type
     */
    public function test_search_vehicles_filters_by_type(): void
    {
        // ARRANGE : Créer des types différents
        Vehicle::factory()->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'type' => 'motorcycle',
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Chercher seulement les voitures
        $vehicles = $this->vehicleService->searchVehicles(['type' => 'car']);

        // ASSERT : 2 voitures
        $this->assertCount(2, $vehicles);
        $this->assertTrue($vehicles->every(fn($v) => $v->type === 'car'));
    }

    /**
     * Test 5 : searchVehicles() filtre par nombre de sièges minimum
     */
    public function test_search_vehicles_filters_by_minimum_seats(): void
    {
        // ARRANGE
        Vehicle::factory()->create([
            'seats' => 2,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'seats' => 5,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'seats' => 7,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Chercher véhicules avec au moins 5 sièges
        $vehicles = $this->vehicleService->searchVehicles(['seats' => 5]);

        // ASSERT : 2 véhicules (5 et 7 sièges)
        $this->assertCount(2, $vehicles);
        $this->assertTrue($vehicles->every(fn($v) => $v->seats >= 5));
    }

    /**
     * Test 6 : searchVehicles() filtre par budget maximum
     */
    public function test_search_vehicles_filters_by_max_budget(): void
    {
        // ARRANGE
        Vehicle::factory()->create([
            'daily_rate' => 30.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'daily_rate' => 50.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'daily_rate' => 80.00,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Budget max de 60€
        $vehicles = $this->vehicleService->searchVehicles(['max_budget' => 60.00]);

        // ASSERT : 2 véhicules (30€ et 50€)
        $this->assertCount(2, $vehicles);
        $this->assertTrue($vehicles->every(fn($v) => $v->daily_rate <= 60.00));
    }

    /**
     * Test 7 : searchVehicles() combine plusieurs critères
     */
    public function test_search_vehicles_combines_multiple_criteria(): void
    {
        // ARRANGE
        Vehicle::factory()->create([
            'type' => 'car',
            'seats' => 5,
            'fuel_type' => 'electric',
            'daily_rate' => 40.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'type' => 'car',
            'seats' => 5,
            'fuel_type' => 'diesel',
            'daily_rate' => 35.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'type' => 'motorcycle',
            'seats' => 2,
            'fuel_type' => 'electric',
            'daily_rate' => 25.00,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Chercher voiture électrique avec au moins 5 sièges
        $vehicles = $this->vehicleService->searchVehicles([
            'type' => 'car',
            'seats' => 5,
            'fuel_type' => 'electric',
        ]);

        // ASSERT : 1 seul véhicule correspond
        $this->assertCount(1, $vehicles);
        $this->assertEquals('car', $vehicles[0]->type);
        $this->assertEquals('electric', $vehicles[0]->fuel_type);
        $this->assertEquals(5, $vehicles[0]->seats);
    }

    /**
     * Test 8 : getBestMatches() retourne les meilleurs matchs
     * 
     * Test plus complexe qui vérifie l'algorithme de scoring.
     */
    public function test_get_best_matches_returns_scored_vehicles(): void
    {
        // ARRANGE : Créer des véhicules avec différentes caractéristiques
        $perfectMatch = Vehicle::factory()->create([
            'type' => 'car',
            'seats' => 5,
            'fuel_type' => 'electric',
            'daily_rate' => 40.00,
            'available' => true,
            'status' => 'active',
        ]);

        $goodMatch = Vehicle::factory()->create([
            'type' => 'car',
            'seats' => 5,
            'fuel_type' => 'diesel',
            'daily_rate' => 35.00,
            'available' => true,
            'status' => 'active',
        ]);

        $poorMatch = Vehicle::factory()->create([
            'type' => 'motorcycle',
            'seats' => 2,
            'fuel_type' => 'gasoline',
            'daily_rate' => 80.00,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Chercher les meilleurs matchs pour une voiture électrique
        $matches = $this->vehicleService->getBestMatches([
            'types' => ['car'],
            'desired_seats' => 5,
            'preferred_fuel' => 'electric',
            'max_budget' => 50.00,
        ], 3);

        // ASSERT : Les voitures sont retournées (moto exclue)
        $this->assertGreaterThan(0, $matches->count());
        
        // Le premier match devrait être le meilleur
        $this->assertEquals('car', $matches->first()->type);
        
        // Toutes les correspondances respectent le budget
        $this->assertTrue($matches->every(fn($v) => $v->daily_rate <= 50.00));
    }

    /**
     * Test 9 : getBestMatches() exclut les IDs spécifiés
     */
    public function test_get_best_matches_excludes_specified_ids(): void
    {
        // ARRANGE
        $vehicle1 = Vehicle::factory()->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        $vehicle2 = Vehicle::factory()->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Exclure vehicle1
        $matches = $this->vehicleService->getBestMatches(
            ['types' => ['car']],
            3,
            [$vehicle1->id]
        );

        // ASSERT : vehicle1 n'est pas dans les résultats
        $this->assertFalse($matches->contains('id', $vehicle1->id));
        $this->assertTrue($matches->contains('id', $vehicle2->id));
    }

    /**
     * Test 10 : getBestMatches() retourne collection vide si aucun match
     */
    public function test_get_best_matches_returns_empty_collection_when_no_matches(): void
    {
        // ARRANGE : Créer seulement des motos
        Vehicle::factory()->count(3)->create([
            'type' => 'motorcycle',
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Chercher des voitures
        $matches = $this->vehicleService->getBestMatches(['types' => ['car']], 3);

        // ASSERT : Collection vide
        $this->assertCount(0, $matches);
        $this->assertTrue($matches->isEmpty());
    }
}
