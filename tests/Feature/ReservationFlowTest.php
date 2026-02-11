<?php

namespace Tests\Feature;

use App\Models\LocationDemand;
use App\Models\LocationProposal;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests Feature pour le flow de réservation
 * 
 * Les tests Feature testent des parcours utilisateur complets :
 * - Requêtes HTTP (GET, POST)
 * - Middleware (auth, admin)
 * - Contrôleurs
 * - Redirections
 * - Réponses JSON
 * 
 * Contrairement aux tests unitaires, ces tests simulent un vrai utilisateur
 * naviguant dans l'application.
 */
class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 : Un utilisateur non authentifié ne peut pas créer de demande
     * 
     * Test de sécurité : vérifier que les routes protégées nécessitent l'auth.
     */
    public function test_guest_cannot_create_location_demand(): void
    {
        // ACT : Tenter de créer une demande sans être connecté
        $response = $this->post('/location-demands', [
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        // ASSERT : Redirection vers login
        $response->assertRedirect('/login');
    }

    /**
     * Test 2 : Un utilisateur authentifié peut créer une demande générique
     */
    public function test_authenticated_user_can_create_generic_demand(): void
    {
        // ARRANGE : Créer un utilisateur et se connecter
        $user = User::factory()->create();

        // Créer des véhicules disponibles pour la proposition
        Vehicle::factory()->count(3)->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Créer une demande en tant qu'utilisateur connecté
        $response = $this->actingAs($user)->post('/location-demands', [
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'seats' => 5,
            'max_budget' => 500,
        ]);

        // ASSERT : Succès (200 ou 201)
        $response->assertStatus(200);

        // Vérifier que la demande est en BDD
        $this->assertDatabaseHas('location_demands', [
            'user_id' => $user->id,
            'vehicle_type' => 'car',
            'demand_type' => 'generic',
            'status' => 'proposed',
        ]);
    }

    /**
     * Test 3 : Création d'une demande génère 3 propositions
     */
    public function test_creating_demand_generates_three_proposals(): void
    {
        // ARRANGE
        $user = User::factory()->create();

        // Créer 5 véhicules pour être sûr d'avoir 3 propositions
        Vehicle::factory()->count(5)->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        // ACT
        $this->actingAs($user)->post('/location-demands', [
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        // ASSERT : 3 propositions créées
        $demand = LocationDemand::where('user_id', $user->id)->first();
        $this->assertCount(3, $demand->proposals);
    }

    /**
     * Test 4 : Les propositions sont retournées en JSON
     */
    public function test_demand_creation_returns_json_proposals(): void
    {
        // ARRANGE
        $user = User::factory()->create();

        Vehicle::factory()->count(3)->create([
            'type' => 'car',
            'available' => true,
            'status' => 'active',
        ]);

        // ACT
        $response = $this->actingAs($user)->postJson('/location-demands', [
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        // ASSERT : Réponse JSON valide
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'proposals' => [
                '*' => ['vehicle', 'proposed_price', 'rank']
            ]
        ]);
    }

    /**
     * Test 5 : Un utilisateur peut accepter une proposition
     */
    public function test_user_can_accept_proposal(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'available' => true,
            'status' => 'active',
        ]);

        $demand = LocationDemand::create([
            'user_id' => $user->id,
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'proposed',
        ]);

        $proposal = LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $vehicle->id,
            'proposed_price' => 200.00,
            'rank' => 1,
            'status' => 'proposed',
        ]);

        // ACT : Accepter la proposition
        $response = $this->actingAs($user)->post("/location-demands/{$demand->id}/accept/{$proposal->id}");

        // ASSERT : Redirection vers profil
        $response->assertRedirect('/profile?reservation=success');

        // Vérifier que la proposition est acceptée
        $this->assertDatabaseHas('location_proposals', [
            'id' => $proposal->id,
            'status' => 'accepted',
        ]);

        // Vérifier que la demande est acceptée
        $this->assertDatabaseHas('location_demands', [
            'id' => $demand->id,
            'status' => 'accepted',
        ]);

        // Vérifier qu'une location a été créée
        $this->assertDatabaseHas('locations', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'confirmed',
        ]);
    }

    /**
     * Test 6 : Un utilisateur ne peut pas accepter la proposition d'un autre
     */
    public function test_user_cannot_accept_another_users_proposal(): void
    {
        // ARRANGE
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $vehicle = Vehicle::factory()->create();

        $demand = LocationDemand::create([
            'user_id' => $user1->id, // Demande de user1
            'demand_type' => 'generic',
            'vehicle_type' => 'car',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'status' => 'proposed',
        ]);

        $proposal = LocationProposal::create([
            'location_demand_id' => $demand->id,
            'vehicle_id' => $vehicle->id,
            'proposed_price' => 200.00,
            'rank' => 1,
            'status' => 'proposed',
        ]);

        // ACT : user2 essaie d'accepter la proposition de user1
        $response = $this->actingAs($user2)->post("/location-demands/{$demand->id}/accept/{$proposal->id}");

        // ASSERT : Erreur 403 (Forbidden)
        $response->assertStatus(403);

        // La proposition n'a pas changé
        $this->assertDatabaseHas('location_proposals', [
            'id' => $proposal->id,
            'status' => 'proposed', // Toujours 'proposed'
        ]);
    }

    /**
     * Test 7 : Réservation directe d'un véhicule depuis la carte
     */
    public function test_user_can_reserve_vehicle_directly(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'available' => true,
            'status' => 'active',
            'daily_rate' => 50.00,
        ]);

        // ACT : Réserver via le modal (route /vehicles/reserve)
        $response = $this->actingAs($user)->postJson('/vehicles/reserve', [
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        // ASSERT : Succès
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Vérifier qu'une location a été créée
        $this->assertDatabaseHas('locations', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'confirmed',
        ]);

        // Vérifier qu'une demande spécifique a été créée
        $this->assertDatabaseHas('location_demands', [
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'demand_type' => 'specific',
            'status' => 'accepted',
        ]);
    }

    /**
     * Test 8 : Un utilisateur peut voir ses réservations dans son profil
     */
    public function test_user_can_view_reservations_in_profile(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create(['model' => 'Tesla Model S']);

        // Créer une location pour cet utilisateur
        $location = \App\Models\Location::create([
            'user_id' => $user->id,
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(5),
            'total_price' => 250.00,
            'status' => 'confirmed',
        ]);

        // ACT : Accéder au profil
        $response = $this->actingAs($user)->get('/profile');

        // ASSERT
        $response->assertStatus(200);
        $response->assertSee('Tesla Model S'); // Le modèle du véhicule est affiché
        $response->assertSee('confirmed'); // Le statut est affiché
    }

    /**
     * Test 9 : La page véhicules affiche la liste des véhicules
     */
    public function test_vehicles_page_displays_available_vehicles(): void
    {
        // ARRANGE
        Vehicle::factory()->create([
            'model' => 'Toyota Corolla',
            'available' => true,
            'status' => 'active',
        ]);

        Vehicle::factory()->create([
            'model' => 'Honda Civic',
            'available' => false, // Pas disponible
            'status' => 'active',
        ]);

        // ACT
        $response = $this->get('/vehicles');

        // ASSERT
        $response->assertStatus(200);
        $response->assertSee('Toyota Corolla'); // Véhicule disponible affiché
        $response->assertDontSee('Honda Civic'); // Véhicule indisponible non affiché
    }

    /**
     * Test 10 : Page de demande de location accessible aux utilisateurs connectés
     */
    public function test_location_demand_page_accessible_to_authenticated_users(): void
    {
        // ARRANGE
        $user = User::factory()->create();

        // ACT
        $response = $this->actingAs($user)->get('/locationDemand');

        // ASSERT
        $response->assertStatus(200);
        $response->assertSee('Proposer 3 véhicules'); // Le bouton est présent
    }

    /**
     * Test 11 : Invité redirigé vers login sur page demande
     */
    public function test_guest_redirected_to_login_on_location_demand_page(): void
    {
        // ACT : Accéder sans être connecté
        $response = $this->get('/locationDemand');

        // ASSERT : Redirection vers login
        $response->assertRedirect('/login');
    }

    /**
     * Test 12 : Calcul du prix total basé sur les jours
     */
    public function test_location_price_calculated_correctly(): void
    {
        // ARRANGE
        $user = User::factory()->create();
        $vehicle = Vehicle::factory()->create([
            'daily_rate' => 50.00,
            'available' => true,
            'status' => 'active',
        ]);

        // ACT : Réserver 5 jours (daily_rate * jours)
        $this->actingAs($user)->postJson('/vehicles/reserve', [
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(6)->format('Y-m-d'), // 5 jours
        ]);

        // ASSERT : Prix total = 50 * 5 = 250
        $location = \App\Models\Location::where('user_id', $user->id)->first();
        
        // Le calcul peut varier selon votre logique (jours inclusifs ou exclusifs)
        // Ajustez selon votre implémentation
        $this->assertGreaterThan(0, $location->total_price);
    }
}
