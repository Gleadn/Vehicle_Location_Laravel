# 📖 Guide des Tests Unitaires Laravel

## 🎯 Qu'est-ce qu'un Test Unitaire ?

Un **test unitaire** vérifie le comportement d'une petite unité de code (une méthode, une fonction) **de manière isolée**, sans dépendre d'autres parties du système.

### ✅ Avantages
- Détecte les bugs tôt dans le développement
- Documente le comportement attendu du code
- Facilite les refactoring (vous savez si vous cassez quelque chose)
- Améliore la qualité du code
- Donne confiance lors des modifications

---

## 📁 Structure des Tests dans Laravel

```
tests/
├── Feature/        # Tests d'intégration (routes, contrôleurs, BDD complète)
├── Unit/          # Tests unitaires (modèles, services, logique isolée)
└── TestCase.php   # Classe de base pour tous vos tests
```

### 🔹 Tests Unitaires vs Tests Feature

| Tests Unitaires | Tests Feature |
|----------------|---------------|
| Testent une méthode isolée | Testent un parcours utilisateur complet |
| Rapides (< 100ms) | Plus lents (requêtes HTTP, BDD, etc.) |
| Pas d'effets de bord | Simulent un vrai navigateur |
| Ex: `isAdmin()` | Ex: "POST /login" avec credentials |

---

## 🏗️ Anatomie d'un Test Laravel

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;  // ← Trait important !
    
    public function test_example(): void
    {
        // 1. ARRANGE (Préparer)
        $user = User::factory()->create(['role' => 'admin']);
        
        // 2. ACT (Agir)
        $result = $user->isAdmin();
        
        // 3. ASSERT (Vérifier)
        $this->assertTrue($result);
    }
}
```

### 🔧 Composants Clés

#### 1️⃣ **Namespace et Imports**
```php
namespace Tests\Unit;  // Indique que c'est un test unitaire

use App\Models\User;   // Les classes à tester
use Tests\TestCase;    // Classe de base Laravel
```

#### 2️⃣ **Trait RefreshDatabase**
```php
use RefreshDatabase;
```
- Réinitialise la base de données entre chaque test
- Utilise une BDD SQLite en mémoire (rapide !)
- Garantit l'isolation : un test n'affecte pas les autres

#### 3️⃣ **Nommage des Méthodes**
```php
public function test_user_with_admin_role_is_admin(): void
//              ↑
//              Commence toujours par "test_"
```
Alternatives :
```php
/** @test */
public function user_with_admin_role_is_admin(): void { }
```

#### 4️⃣ **Pattern AAA (Arrange-Act-Assert)**
```php
// ARRANGE : Préparer les données
$user = User::factory()->create(['role' => 'admin']);

// ACT : Exécuter l'action à tester
$result = $user->isAdmin();

// ASSERT : Vérifier le résultat
$this->assertTrue($result);
```

---

## 🏭 Factories : Créer des Données de Test

Les **factories** génèrent des instances de modèles avec des données réalistes.

### 📄 UserFactory.php
```php
public function definition(): array
{
    return [
        'name' => fake()->name(),           // "John Doe"
        'email' => fake()->unique()->safeEmail(), // "john@example.com"
        'password' => Hash::make('password'),
        'role' => 'user',
    ];
}
```

### 🎯 Utilisation dans les Tests

```php
// Créer UN utilisateur avec les valeurs par défaut
$user = User::factory()->create();

// Créer UN utilisateur avec des valeurs personnalisées
$admin = User::factory()->create([
    'role' => 'admin',
    'email' => 'admin@mail.com',
]);

// Créer 10 utilisateurs
$users = User::factory()->count(10)->create();

// Créer un modèle SANS l'enregistrer en BDD (juste en mémoire)
$userInMemory = User::factory()->make();
```

---

## 🧪 Assertions Principales

Laravel/PHPUnit fournit des dizaines d'assertions. Voici les plus courantes :

### ✅ Assertions Booléennes
```php
$this->assertTrue($user->isAdmin());
$this->assertFalse($user->isUser());
```

### 📊 Assertions de Comparaison
```php
$this->assertEquals('admin', $user->role);
$this->assertSame('admin', $user->role);  // Plus strict (type aussi)
$this->assertNotEquals('user', $user->role);
```

### 🗃️ Assertions de Présence
```php
$this->assertNotNull($user);
$this->assertNull($user->deleted_at);
```

### 🔢 Assertions de Collection
```php
$this->assertCount(3, $users);
$this->assertEmpty($collection);
$this->assertNotEmpty($collection);
```

### 🗄️ Assertions de Base de Données
```php
$this->assertDatabaseHas('users', [
    'email' => 'admin@mail.com',
    'role' => 'admin',
]);

$this->assertDatabaseMissing('users', [
    'email' => 'nonexistent@mail.com',
]);
```

---

## 🚀 Exécuter les Tests

### Commandes Artisan

```bash
# Exécuter TOUS les tests
php artisan test

# Exécuter seulement les tests unitaires
php artisan test --testsuite=Unit

# Exécuter un fichier de test spécifique
php artisan test --filter=UserTest

# Exécuter une méthode de test précise
php artisan test --filter=test_user_with_admin_role_is_admin

# Afficher plus de détails
php artisan test --verbose

# Arrêter au premier échec
php artisan test --stop-on-failure
```

### Alternative avec PHPUnit directement
```bash
vendor/bin/phpunit tests/Unit/UserTest.php
```

---

## 📊 Sortie des Tests

```
   PASS  Tests\Unit\UserTest
  ✓ user with admin role is admin                    0.39s  
  ✓ user with user role is not admin                 0.02s  
  ✓ user has default user role                       0.02s  
  ✓ user role can be updated                         0.02s  
  ✓ user role is persisted in database               0.03s  

  Tests:    5 passed (12 assertions)
  Duration: 0.63s
```

- **✓** : Test réussi (vert)
- **⨯** : Test échoué (rouge)
- **12 assertions** : Nombre total de vérifications effectuées
- **Duration** : Temps d'exécution

---

## 🎓 Exemple Complet Commenté

Notre test [tests/Unit/UserTest.php](tests/Unit/UserTest.php) contient **5 tests** qui couvrent :

### Test 1 : Utilisateur Admin
```php
public function test_user_with_admin_role_is_admin(): void
{
    $adminUser = User::factory()->create(['role' => 'admin']);
    $this->assertTrue($adminUser->isAdmin());
    $this->assertFalse($adminUser->isUser());
}
```
✅ Vérifie qu'un user avec `role = 'admin'` retourne `true` pour `isAdmin()`

### Test 2 : Utilisateur Normal
```php
public function test_user_with_user_role_is_not_admin(): void
{
    $regularUser = User::factory()->create(['role' => 'user']);
    $this->assertFalse($regularUser->isAdmin());
    $this->assertTrue($regularUser->isUser());
}
```
✅ Vérifie qu'un user avec `role = 'user'` retourne `false` pour `isAdmin()`

### Test 3 : Valeur Par Défaut
```php
public function test_user_has_default_user_role(): void
{
    $user = User::factory()->create();
    $this->assertEquals('user', $user->role);
}
```
✅ Vérifie que si on ne spécifie pas de rôle, c'est `'user'` par défaut

### Test 4 : Modification du Rôle
```php
public function test_user_role_can_be_updated(): void
{
    $user = User::factory()->create(['role' => 'user']);
    $user->update(['role' => 'admin']);
    $this->assertTrue($user->isAdmin());
}
```
✅ Vérifie qu'on peut changer le rôle d'un user (utile pour promotion admin)

### Test 5 : Persistance en Base
```php
public function test_user_role_is_persisted_in_database(): void
{
    User::factory()->create(['role' => 'admin', 'email' => 'test@admin.com']);
    $userFromDb = User::where('email', 'test@admin.com')->first();
    $this->assertTrue($userFromDb->isAdmin());
}
```
✅ Vérifie que le rôle est vraiment sauvegardé en BDD (pas juste en mémoire)

---

## 🐛 Debugging : Le Test qui Échouait

Lors de notre première exécution, le **test 3 a échoué** :

```
⨯ user has default user role
Failed asserting that null matches expected 'user'.
```

### 🔍 Cause du Bug
La `UserFactory` ne définissait pas le champ `role` :
```php
// ❌ AVANT (manquant)
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        // ... pas de 'role'
    ];
}
```

### ✅ Solution
Ajouter `'role' => 'user'` dans la factory :
```php
// ✅ APRÈS
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'role' => 'user',  // ← Ajouté !
    ];
}
```

### 📚 Leçon Apprise
Les **factories définissent l'état par défaut** pour les tests. Même si votre migration a une valeur `default`, la factory doit la spécifier explicitement.

---

## 🎯 Bonnes Pratiques

### ✅ À FAIRE

1. **Un test = un comportement**
   ```php
   // ✅ Bon : teste UNE chose
   public function test_admin_can_access_dashboard(): void { }
   
   // ❌ Mauvais : teste trop de choses
   public function test_admin_functionality(): void { }
   ```

2. **Noms de tests descriptifs**
   ```php
   // ✅ Bon : on comprend ce qui est testé
   public function test_user_with_admin_role_is_admin(): void { }
   
   // ❌ Mauvais : pas clair
   public function test_role(): void { }
   ```

3. **Tester les cas limites**
   ```php
   test_user_with_admin_role_is_admin()  // Cas normal
   test_user_with_user_role_is_not_admin()  // Cas opposé
   test_user_with_null_role()  // Cas limite
   ```

4. **Isolation des tests**
   - Toujours utiliser `RefreshDatabase`
   - Un test ne doit PAS dépendre d'un autre test

5. **Assertions claires**
   ```php
   // ✅ Bon : message d'erreur clair
   $this->assertEquals('admin', $user->role, 'Le rôle devrait être admin');
   
   // ✅ Bon : assertion spécifique
   $this->assertDatabaseHas('users', ['email' => 'test@mail.com']);
   ```

### ❌ À ÉVITER

1. **Tests trop larges**
2. **Dépendances entre tests**
3. **Données hardcodées (préférer les factories)**
4. **Tester le framework Laravel lui-même**

---

## 📝 Exercices pour Aller Plus Loin

### 🔰 Niveau Débutant
1. Créer un test pour vérifier qu'un véhicule a un modèle
2. Tester la relation `user->locations()`

### 🔸 Niveau Intermédiaire
3. Créer un test pour `VehicleService::getBestMatches()`
4. Tester que seul un admin peut accéder à `/admin` (test Feature)

### 🔥 Niveau Avancé
5. Mocker un service externe
6. Tester les événements/observers (`VehicleObserver`)

---

## 📚 Ressources

- **Documentation Laravel** : https://laravel.com/docs/11.x/testing
- **PHPUnit** : https://phpunit.de/documentation.html
- **Test Driven Development (TDD)** : Écrire les tests AVANT le code

---

## 🎓 Résumé

✅ **Tests unitaires** = tester des méthodes isolées (comme `isAdmin()`)  
✅ **RefreshDatabase** = réinitialise la BDD entre chaque test  
✅ **Factories** = génèrent des données de test réalistes  
✅ **AAA Pattern** = Arrange → Act → Assert  
✅ **Commande** = `php artisan test --filter=UserTest`  

Vous venez de créer vos premiers tests unitaires Laravel ! 🎉
