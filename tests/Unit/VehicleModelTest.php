<?php

namespace Tests\Unit;

use App\Models\Location;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour le modèle Vehicle
 * 
 * Ce fichier teste :
 * - Les relations Eloquent (hasMany, belongsTo, etc.)
 * - Les scopes (méthodes de requête réutilisables)
 * - Les accessors/mutators si présents
 */
class VehicleModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 : Un véhicule peut avoir plusieurs locations
     * 
     * Test de la relation hasMany : Vehicle -> Location
     */
    public function test_vehicle_has_many_locations(): void
    {
        // ARRANGE : Créer un utilisateur, un véhicule et 2 locations
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $location1 = Location::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now(),
            'end_date' => now()->addDays(3),
            'total_price' => 100.00,
            'status' => 'confirmed',
        ]);

        $location2 = Location::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(15),
            'total_price' => 200.00,
            'status' => 'active',
        ]);

        // ACT : Récupérer les locations via la relation
        $locations = $vehicle->locations;

        // ASSERT : Le véhicule a bien 2 locations
        $this->assertCount(2, $locations);
        $this->assertTrue($locations->contains($location1));
        $this->assertTrue($locations->contains($location2));
    }

    /**
     * Test 2 : Scope available() retourne seulement les véhicules disponibles
     * 
     * Les scopes sont des méthodes de requête réutilisables.
     * Exemple : Vehicle::available()->get()
     */
    public function test_available_scope_returns_only_available_vehicles(): void
    {
        // ARRANGE : Créer véhicules disponibles et non disponibles
        $availableVehicle = Vehicle::factory()->create([
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'available' => false,
            'status' => 'active',
        ]);

        // Un véhicule en maintenance ne peut pas être disponible (validation Observer)
        Vehicle::factory()->create([
            'available' => false,
            'status' => 'maintenance',
        ]);

        // ACT : Utiliser le scope available()
        $vehicles = Vehicle::available()->get();

        // ASSERT : Seulement 1 véhicule (disponible ET actif)
        $this->assertCount(1, $vehicles);
        $this->assertEquals($availableVehicle->id, $vehicles->first()->id);
    }

    /**
     * Test 3 : Scope ofType() filtre par type
     */
    public function test_of_type_scope_filters_by_vehicle_type(): void
    {
        // ARRANGE - Créer explicitement avec les bons sièges
        Vehicle::factory()->count(2)->create([
            'type' => 'car',
            'seats' => 5, // Valide pour type 'car'
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'type' => 'motorcycle',
            'seats' => 2, // Valide pour motorcycle
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Filtrer par type 'car'
        $cars = Vehicle::ofType('car')->get();

        // ASSERT : 2 voitures
        $this->assertCount(2, $cars);
        $this->assertTrue($cars->every(fn($v) => $v->type === 'car'));
    }

    /**
     * Test 4 : Un véhicule a des attributs obligatoires
     * 
     * Test de validation implicite via les contraintes de BDD.
     */
    public function test_vehicle_has_required_attributes(): void
    {
        // ARRANGE & ACT
        $vehicle = Vehicle::factory()->create([
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'seats' => 5, // Valide pour car
            'type' => 'car',
            'daily_rate' => 50.00,
        ]);

        // ASSERT : Les attributs sont bien enregistrés
        $this->assertEquals('Toyota', $vehicle->brand);
        $this->assertEquals('Corolla', $vehicle->model);
        $this->assertEquals('car', $vehicle->type);
        $this->assertEquals(50.00, $vehicle->daily_rate);
    }

    /**
     * Test 5 : Véhicule peut être soft deleted
     */
    public function test_vehicle_can_be_soft_deleted(): void
    {
        // ARRANGE
        $vehicle = Vehicle::factory()->create();

        // ACT : Soft delete
        $vehicle->delete();

        // ASSERT : Le véhicule existe toujours en BDD mais avec deleted_at
        $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
        
        // Le véhicule n'apparaît plus dans les requêtes normales
        $this->assertCount(0, Vehicle::all());
        
        // Mais apparaît avec withTrashed()
        $this->assertCount(1, Vehicle::withTrashed()->get());
    }

    /**
     * Test 6 : Locations actives d'un véhicule
     */
    public function test_vehicle_can_retrieve_active_locations(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        // Location active
        $activeLocation = Location::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now(),
            'end_date' => now()->addDays(3),
            'total_price' => 100.00,
            'status' => 'active',
        ]);

        // Location complétée (ne devrait pas être comptée)
        Location::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(5),
            'total_price' => 150.00,
            'status' => 'completed',
        ]);

        // ACT : Récupérer seulement les locations actives
        $activeLocations = $vehicle->locations()->where('status', 'active')->get();

        // ASSERT : 1 seule location active
        $this->assertCount(1, $activeLocations);
        $this->assertEquals($activeLocation->id, $activeLocations->first()->id);
    }

    /**
     * Test 7 : Calcul du prix total pour une période
     * 
     * Si vous avez une méthode calculatePrice() dans le modèle.
     * Exemple de test pour une logique métier dans le modèle.
     */
    public function test_vehicle_daily_rate_is_numeric(): void
    {
        // ARRANGE
        $vehicle = Vehicle::factory()->create([
            'daily_rate' => 75.50,
        ]);

        // ASSERT
        $this->assertIsNumeric($vehicle->daily_rate);
        $this->assertEquals(75.50, $vehicle->daily_rate);
    }

    /**
     * Test 8 : Factory crée un véhicule valide par défaut
     */
    public function test_factory_creates_valid_vehicle(): void
    {
        // ACT
        $vehicle = Vehicle::factory()->create();

        // ASSERT : Tous les champs requis sont présents
        $this->assertNotNull($vehicle->brand);
        $this->assertNotNull($vehicle->model);
        $this->assertNotNull($vehicle->type);
        $this->assertNotNull($vehicle->daily_rate);
        $this->assertNotNull($vehicle->seats);
        $this->assertNotNull($vehicle->fuel_type);
        
        // Valeurs cohérentes selon le type
        $validSeatsByType = [
            'motorcycle' => [1, 2],
            'car' => [5, 7],
            'van' => [8, 11],
            'sport' => [2],
        ];
        
        $this->assertContains($vehicle->seats, $validSeatsByType[$vehicle->type]);
    }

    /**
     * Test 9 : Les types de véhicules valides
     */
    public function test_vehicle_type_is_valid(): void
    {
        // Liste des types valides attendus
        $validTypes = ['car', 'motorcycle', 'van', 'sport'];

        // ARRANGE & ACT
        $vehicle = Vehicle::factory()->create();

        // ASSERT : Le type généré est dans la liste valide
        $this->assertContains($vehicle->type, $validTypes);
    }

    /**
     * Test 10 : Recherche de véhicules par plusieurs critères
     */
    public function test_vehicle_can_be_queried_with_multiple_filters(): void
    {
        // ARRANGE
        Vehicle::factory()->create([
            'type' => 'car',
            'fuel_type' => 'electric',
            'seats' => 5,
            'daily_rate' => 60.00,
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'type' => 'car',
            'fuel_type' => 'diesel',
            'seats' => 5,
            'daily_rate' => 50.00,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Requête complexe
        $vehicles = Vehicle::where('type', 'car')
            ->where('fuel_type', 'electric')
            ->where('available', true)
            ->where('daily_rate', '<=', 70)
            ->get();

        // ASSERT : 1 seul véhicule correspond
        $this->assertCount(1, $vehicles);
        $this->assertEquals('electric', $vehicles->first()->fuel_type);
    }
}
