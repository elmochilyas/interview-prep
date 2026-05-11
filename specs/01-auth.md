# Feature: Authentication

> **Spec file:** `specs/01-auth.md`
> **Branch:** `feature/auth`
> **Day:** 2 — Tuesday 12/05/2026
> **User Stories covered:** US1

---

## What I want

A working authentication system using Laravel Breeze with:
- User registration with name, email, password, and password confirmation
- User login with email and password
- User logout that redirects to `/login`
- All authenticated routes protected by the `auth` middleware
- After login → redirect to `/dashboard`
- After registration → redirect to `/dashboard`
- After logout → redirect to `/`
- A dashboard route at `/dashboard` that is protected and renders a basic view (will be fleshed out in the bonus spec)

---

## What I do NOT want

- ❌ Do NOT add email verification (`MustVerifyEmail`) — not needed for this project
- ❌ Do NOT add password reset / forgot password flows — not in scope
- ❌ Do NOT add social login (GitHub, Google, etc.)
- ❌ Do NOT add profile editing page beyond what Breeze generates
- ❌ Do NOT add two-factor authentication
- ❌ Do NOT modify the `users` table migration in any way
- ❌ Do NOT redirect unauthenticated users anywhere other than `/login`
- ❌ Do NOT create a custom `LoginController` or `RegisterController` — use Breeze's generated ones

---

## Acceptance Criteria

- [ ] `GET /register` renders the registration form
- [ ] `POST /register` creates a new user, hashes the password, and redirects to `/dashboard`
- [ ] `GET /login` renders the login form
- [ ] `POST /login` authenticates the user and redirects to `/dashboard`
- [ ] `POST /logout` logs the user out and redirects to `/`
- [ ] `GET /dashboard` returns 200 for authenticated users
- [ ] `GET /dashboard` redirects to `/login` for unauthenticated users (middleware working)
- [ ] Registering with an already-used email shows a validation error
- [ ] Submitting login with wrong password shows a validation error
- [ ] User's name is visible in the navigation bar after login
- [ ] Flash messages partial is displayed on the dashboard

---

## Data Involved

### Model: `User`
```php
// Relationships to add in User.php (will be used by other features)
public function domains(): HasMany
{
    return $this->hasMany(Domain::class);
}
```
> Add this relationship now so Domain feature can build on it immediately.

### Table: `users` (Breeze — do NOT modify migration)
```
id, name, email, email_verified_at, password, remember_token, created_at, updated_at
```

---

## Routes Involved

These are auto-registered by Breeze — verify with `php artisan route:list`:

| Method | URI | Action |
|--------|-----|--------|
| GET | `/register` | Auth\RegisteredUserController@create |
| POST | `/register` | Auth\RegisteredUserController@store |
| GET | `/login` | Auth\AuthenticatedSessionController@create |
| POST | `/login` | Auth\AuthenticatedSessionController@store |
| POST | `/logout` | Auth\AuthenticatedSessionController@destroy |
| GET | `/dashboard` | DashboardController@index |

The `/dashboard` route must be inside `Route::middleware('auth')` group in `routes/web.php`.

---

## Dashboard Controller (minimal — will be expanded in bonus spec)

```php
// app/Http/Controllers/DashboardController.php
<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
}
```

```php
// routes/web.php — the full auth-protected group skeleton for ALL features
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Domain routes will be added in feature/domains-crud
    // Concept routes will be added in feature/concepts-crud
    // AI generation routes will be added in feature/ai-generation
});
```

> Set up the full route group skeleton now with comments for each upcoming feature block — avoids merge conflicts later.

---

## Nav Bar Requirements

The `layouts/app.blade.php` nav must show:
- App name "InterviewPrep" linking to `/dashboard`
- "Mes Domaines" linking to `/domains`
- User name (authenticated user's name) on the right
- Logout button (POST form with `@csrf` and `@method` not needed since logout is POST)

```blade
{{-- Logout button in nav --}}
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Déconnexion</button>
</form>
```

---

## Security Rules for This Feature

- The `auth` middleware must wrap ALL non-auth routes — verify no route is accidentally public
- Session regeneration on login is handled by Breeze — do not remove it
- Password is always hashed with `bcrypt` — never stored plain

---

## Agent Output — What Was Generated
> *(Fill this in after the agent runs)*

---

## What I Changed Manually
> *(Fill this in after the agent runs — what you edited and why)*
