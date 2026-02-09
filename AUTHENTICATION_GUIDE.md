# 🔐 Guide d'Authentification Laravel

## Différence JWT vs Sessions Laravel

### JWT (ce que vous connaissez en JS)
```javascript
// Côté client
localStorage.setItem('token', 'eyJhbGciOiJIUzI1...')

// À chaque requête
fetch('/api/data', {
    headers: { 'Authorization': 'Bearer ' + token }
})
```

**Caractéristiques JWT :**
- ✅ Stateless (le serveur ne garde rien)
- ✅ Parfait pour les APIs
- ❌ Token visible côté client
- ❌ Difficile à révoquer
- ❌ Taille du token importante

### Sessions Laravel (recommandé pour apps web)
```php
// Côté serveur
Auth::attempt($credentials, $remember);
// → Laravel crée automatiquement :
//   1. Une entrée en BDD (table sessions)
//   2. Un cookie crypté envoyé au navigateur
```

**Caractéristiques Sessions :**
- ✅ Stateful (données sécurisées côté serveur)
- ✅ Cookie HTTP-only (non accessible en JS)
- ✅ Facile à révoquer (logout)
- ✅ Petite taille (juste un ID)
- ✅ Gestion automatique par Laravel

---

## 📊 Comment ça fonctionne

### 1. **Login (Connexion)**
```php
// AuthController.php
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    $remember = $request->boolean('remember');
    
    if (Auth::attempt($credentials, $remember)) {
        // ✅ Connexion réussie
        $request->session()->regenerate(); // Sécurité anti-fixation
        return redirect('/');
    }
    
    // ❌ Échec
    return back()->withErrors(['email' => 'Identifiants incorrects']);
}
```

**Ce qui se passe en coulisses :**
1. Laravel vérifie le hash du mot de passe
2. Si OK → crée une session en BDD :
   ```sql
   INSERT INTO sessions (id, user_id, ip_address, last_activity, ...)
   VALUES ('abc123...', 42, '192.168.1.1', 1738961234, ...)
   ```
3. Envoie un cookie au navigateur :
   ```
   Set-Cookie: laravel_session=abc123...; HttpOnly; Secure
   ```

### 2. **Requêtes suivantes**
```
Client                          Serveur
  |                               |
  |-- GET /profile -------------->|
  |   Cookie: laravel_session=abc123
  |                               |
  |                      1. Lit le cookie
  |                      2. SELECT * FROM sessions WHERE id = 'abc123'
  |                      3. Récupère user_id = 42
  |                      4. Auth::user() → retourne User #42
  |                               |
  |<-- 200 OK avec données user --|
```

### 3. **Remember Me (Se souvenir de moi)**
Quand coché :
- **Session normale** : expire après 4h d'inactivité
- **Remember cookie** : valide pendant **5 ans** (configurable)

```php
// config/auth.php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],

// Table users doit avoir :
// - remember_token (string, 100)
```

**Fonctionnement :**
1. Laravel crée un token aléatoire
2. Le stocke dans `users.remember_token`
3. Envoie un cookie `remember_web_xxx` au navigateur
4. Même après expiration de la session, le remember cookie reconnecte l'utilisateur

### 4. **Logout (Déconnexion)**
```php
public function logout(Request $request)
{
    Auth::logout();                      // Efface l'authentification
    $request->session()->invalidate();   // Supprime la session en BDD
    $request->session()->regenerateToken(); // Nouveau token CSRF
    
    return redirect('/');
}
```

---

## ⚙️ Configuration actuelle du projet

### **Durée de session : 4 heures**
```env
# .env
SESSION_LIFETIME=240  # 240 minutes = 4 heures
```

### **Stockage : Base de données**
```env
SESSION_DRIVER=database
```

Avantages :
- ✅ Persistant (survit aux redémarrages serveur)
- ✅ Partageable entre plusieurs serveurs
- ✅ Facile à auditer

### **Structure table sessions**
```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT,              -- NULL si non connecté
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,                -- Données sérialisées
    last_activity INT            -- Timestamp
);
```

---

## 🔒 Système de sécurité

### **Protection CSRF**
```blade
<form method="POST">
    @csrf  <!-- Token anti-CSRF automatique -->
</form>
```

Laravel vérifie automatiquement que chaque POST/PUT/DELETE vient bien de votre site.

### **Session Regeneration**
```php
$request->session()->regenerate();
```
Change l'ID de session après login pour éviter les attaques de fixation.

### **Cookie HTTP-Only**
```php
// config/session.php
'http_only' => true,  // Cookie non accessible en JavaScript
'secure' => true,     // Uniquement HTTPS en production
'same_site' => 'lax', // Protection CSRF
```

---

## 🎯 Utilisation dans le code

### **Vérifier si connecté**
```php
// Dans un contrôleur
if (Auth::check()) {
    // Utilisateur connecté
}

// Dans Blade
@auth
    <p>Bonjour {{ Auth::user()->name }}</p>
@endauth

@guest
    <a href="/login">Se connecter</a>
@endguest
```

### **Récupérer l'utilisateur**
```php
// Contrôleur
$user = Auth::user();
$userId = Auth::id();

// Blade
{{ Auth::user()->email }}
```

### **Middleware auth**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/profile', ...);  // Nécessite connexion
});
```

---

## 🆚 Quand utiliser quoi ?

| Cas d'usage | Solution |
|-------------|----------|
| Application web classique (Laravel + Blade) | **Sessions Laravel** ✅ |
| SPA (Vue, React) sur même domaine | **Laravel Sanctum** (sessions SPA) |
| API mobile ou frontend séparé | **Laravel Sanctum** (tokens API) |
| Microservices, OAuth | **Laravel Passport** (OAuth2) |

---

## 📝 Récapitulatif de votre configuration

✅ **Session de 4h** : L'utilisateur reste connecté 4h sans activité  
✅ **Remember Me** : Si coché, reste connecté jusqu'à 5 ans  
✅ **Base de données** : Sessions stockées en BDD SQLite  
✅ **Sécurité** : CSRF, HTTP-Only, Session Regeneration  

### **Fichiers modifiés**
- `.env` → `SESSION_LIFETIME=240`
- `AuthController.php` → Gestion du `$remember`
- `login.blade.php` → Ajout checkbox "Se souvenir de moi"
- `auth.css` → Styles pour la checkbox

### **Test**
1. Connectez-vous avec "Se souvenir de moi" ✓
2. Fermez le navigateur
3. Rouvrez → Toujours connecté ! 🎉
4. Attendez 4h sans activité → Déconnecté (sauf si remember me coché)

---

## 🔧 Configuration avancée (optionnel)

### **Changer la durée du Remember Me**
```php
// config/auth.php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
],

// Pour changer la durée (défaut = 5 ans)
// Aller dans vendor/laravel/framework/src/Illuminate/Auth/EloquentUserProvider.php
// Méthode: rehashPasswordIfRequired()
```

### **Nettoyage automatique des sessions expirées**
```bash
# Ajouter dans scheduler (app/Console/Kernel.php)
protected function schedule(Schedule $schedule)
{
    $schedule->command('session:clear')->daily();
}
```

### **Utiliser Redis pour plus de performance**
```env
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## 🎓 Pour aller plus loin

- [Laravel Auth Documentation](https://laravel.com/docs/authentication)
- [Sessions Laravel](https://laravel.com/docs/session)
- [Laravel Sanctum](https://laravel.com/docs/sanctum) (pour SPA/API)
- [Différence Sanctum vs Passport](https://laracasts.com/series/whats-new-in-laravel-8/episodes/6)
