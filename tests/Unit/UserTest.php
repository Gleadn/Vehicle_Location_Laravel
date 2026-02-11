<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitaires pour le modèle User
 * 
 * Les tests unitaires vérifient le comportement de petites unités de code
 * de manière isolée (ici, les méthodes du modèle User).
 */
class UserTest extends TestCase
{
    /**
     * RefreshDatabase : Ce trait Laravel réinitialise la base de données
     * après chaque test pour garantir l'isolation des tests.
     * Chaque test démarre avec une base de données propre.
     */
    use RefreshDatabase;

    /**
     * Test 1 : Vérifie qu'un utilisateur avec le rôle 'admin' est reconnu comme admin
     * 
     * Méthode de test Laravel :
     * - Commence toujours par 'test_' ou utilise l'annotation @test
     * - Suit le pattern : Arrange (préparer) -> Act (agir) -> Assert (vérifier)
     */
    public function test_user_with_admin_role_is_admin(): void
    {
        // ARRANGE : Préparer les données de test
        // User::factory() utilise la UserFactory pour créer un utilisateur
        // ->create() insère vraiment l'utilisateur en base de données de test
        $adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // ACT : Exécuter l'action à tester
        $isAdmin = $adminUser->isAdmin();

        // ASSERT : Vérifier que le résultat est celui attendu
        // assertTrue() vérifie que la valeur est true
        $this->assertTrue($isAdmin);
        
        // Assertion supplémentaire pour être exhaustif
        $this->assertFalse($adminUser->isUser());
    }

    /**
     * Test 2 : Vérifie qu'un utilisateur avec le rôle 'user' n'est pas admin
     */
    public function test_user_with_user_role_is_not_admin(): void
    {
        // ARRANGE
        $regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        // ACT
        $isAdmin = $regularUser->isAdmin();

        // ASSERT
        $this->assertFalse($isAdmin);
        $this->assertTrue($regularUser->isUser());
    }

    /**
     * Test 3 : Vérifie le comportement par défaut (rôle 'user' par défaut)
     * 
     * Ce test vérifie que si on ne spécifie pas de rôle,
     * l'utilisateur a bien le rôle 'user' par défaut (défini dans la migration).
     */
    public function test_user_has_default_user_role(): void
    {
        // ARRANGE : Créer un utilisateur sans spécifier le rôle
        $user = User::factory()->create();

        // ASSERT : Vérifier directement l'attribut en base
        $this->assertEquals('user', $user->role);
        $this->assertTrue($user->isUser());
    }

    /**
     * Test 4 : Vérifie qu'un utilisateur peut avoir son rôle modifié
     * 
     * Ce test vérifie la mutabilité du rôle (important pour l'administration).
     */
    public function test_user_role_can_be_updated(): void
    {
        // ARRANGE : Créer un utilisateur normal
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        // Vérifier l'état initial
        $this->assertTrue($user->isUser());

        // ACT : Promouvoir l'utilisateur en admin
        $user->update(['role' => 'admin']);

        // ASSERT : Vérifier que le changement a bien eu lieu
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isUser());
    }

    /**
     * Test 5 : Vérifie que le rôle est bien persisté en base de données
     * 
     * Ce test vérifie que le rôle n'est pas juste en mémoire,
     * mais bien sauvegardé dans la base de données.
     */
    public function test_user_role_is_persisted_in_database(): void
    {
        // ARRANGE : Créer un admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'test@admin.com',
        ]);

        // ACT : Récupérer le même utilisateur depuis la base (nouvelle instance)
        $userFromDb = User::where('email', 'test@admin.com')->first();

        // ASSERT : Vérifier que le rôle est toujours 'admin'
        $this->assertNotNull($userFromDb);
        $this->assertEquals('admin', $userFromDb->role);
        $this->assertTrue($userFromDb->isAdmin());
    }
}
