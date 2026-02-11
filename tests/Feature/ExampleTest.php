<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests Feature : Testent des parcours utilisateur complets
 * 
 * Contrairement aux tests unitaires qui testent une méthode isolée,
 * les tests Feature testent des requêtes HTTP complètes (routes, middleware, contrôleurs).
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Feature : Vérifie que la page d'accueil charge correctement
     * 
     * Ce test :
     * - Envoie une requête HTTP GET à '/'
     * - Vérifie que la réponse HTTP est 200 (succès)
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test Feature : Vérifie qu'un utilisateur admin peut accéder au panel admin
     * 
     * Ce test simule un parcours complet :
     * 1. Création d'un utilisateur admin
     * 2. Authentification (login)
     * 3. Accès à la route protégée /admin
     * 4. Vérification du statut 200
     */
    public function test_admin_can_access_admin_panel(): void
    {
        // ARRANGE : Créer un admin et se connecter
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        // ACT : S'authentifier et accéder à /admin
        $response = $this->actingAs($admin)->get('/admin');

        // ASSERT : Vérifier le succès
        $response->assertStatus(200);
        $response->assertSee('Panel Administrateur'); // Vérifie texte dans la page
    }

    /**
     * Test Feature : Vérifie qu'un utilisateur normal NE PEUT PAS accéder au panel admin
     * 
     * Test de sécurité : les routes protégées doivent bien rejeter les non-admins.
     */
    public function test_regular_user_cannot_access_admin_panel(): void
    {
        // ARRANGE : Créer un utilisateur normal
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        // ACT : Tenter d'accéder à /admin
        $response = $this->actingAs($user)->get('/admin');

        // ASSERT : Vérifier le rejet (403 Forbidden)
        $response->assertStatus(403);
    }

    /**
     * Test Feature : Vérifie que les invités sont redirigés vers login
     */
    public function test_guest_redirected_to_login_when_accessing_admin(): void
    {
        // ACT : Accéder à /admin sans authentification
        $response = $this->get('/admin');

        // ASSERT : Redirection vers /login
        $response->assertRedirect('/login');
    }
}

