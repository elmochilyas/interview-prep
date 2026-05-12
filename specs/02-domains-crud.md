# Feature: Domains CRUD

> **Spec file:** `specs/02-domains-crud.md`
> **Branch:** `feature/domains-crud`
> **Day:** 2 — Tuesday 12/05/2026
> **User Stories covered:** US2, US3, US4

---

## What I want

A full CRUD for the `domains` resource, scoped entirely to the authenticated user:

- **List page** (`/domains`): shows all domains belonging to the logged-in user, each domain card displays its name, badge color, total concept count, and mastered concept count. Zero N+1 queries.
- **Create page** (`/domains/create`): form with a name field and a hex color picker field. Validates both via `StoreDomainRequest`.
- **Edit page** (`/domains/{domain}/edit`): pre-filled form to update name and color. Validates via `UpdateDomainRequest`. Only the owner can edit.
- **Delete**: a DELETE button on the list or detail page. Only the owner can delete. Cascade deletes all concepts belonging to the domain.
- After create → redirect to `domains.index` with success flash message
- After update → redirect to `domains.index` with success flash message
- After delete → redirect to `domains.index` with success flash message

---

## What I do NOT want

- ❌ Do NOT use `$request->validate([...])` inline in the controller — use Form Request classes only
- ❌ Do NOT use `Domain::all()` — always scope to `auth()->user()->domains()`
- ❌ Do NOT allow a user to see, edit, or delete another user's domain — `abort(403)` if ownership check fails
- ❌ Do NOT generate a `domains.show` page — clicking a domain goes directly to the concepts list (handled in feature/concepts-crud)
- ❌ Do NOT add any JavaScript color picker library — use `<input type="color">` HTML native element
- ❌ Do NOT hardcode any color values in Blade — always use `$domain->color`
- ❌ Do NOT add pagination at this stage
- ❌ Do NOT generate commented-out code

---

## Acceptance Criteria

- [ ] `GET /domains` lists only the authenticated user's domains
- [ ] Each domain card shows: name, color badge, total concept count, mastered concept count
- [ ] `GET /domains/create` renders the create form
- [ ] `POST /domains` with valid data creates a domain and redirects with success message
- [ ] `POST /domains` with invalid data (missing name or bad color) shows validation errors
- [ ] `GET /domains/{domain}/edit` renders the edit form pre-filled with current values
- [ ] `PATCH /domains/{domain}` with valid data updates and redirects with success message
- [ ] `DELETE /domains/{domain}` deletes the domain and all its concepts (cascade)
- [ ] A user cannot edit or delete another user's domain — receives 403
- [ ] Domains page shows 0 concepts and 0 maîtrisés for a newly created domain
- [ ] Color badge is displayed using the hex color stored in the database
- [ ] No N+1 queries on the domains index page (verified with Debugbar)

---

## Data Involved

### Model: `Domain`

```php
// app/Models/Domain.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $fillable = ['user_id', 'name', 'color'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function concepts(): HasMany
    {
        return $this->hasMany(Concept::class);
    }
}
```

### Eager loading for N+1 prevention (MANDATORY in controller):

```php
// DomainController::index()
$domains = auth()->user()
    ->domains()
    ->withCount([
        'concepts',
        'concepts as mastered_count' => fn($q) => $q->where('status', 'mastered')
    ])
    ->get();
```

This gives each domain `$domain->concepts_count` and `$domain->mastered_count` without extra queries.

---

## Routes Involved

```php
Route::resource('domains', DomainController::class);
```

This generates:

| Method | URI | Controller Method | Route Name |
|--------|-----|-------------------|------------|
| GET | `/domains` | `index` | `domains.index` |
| GET | `/domains/create` | `create` | `domains.create` |
| POST | `/domains` | `store` | `domains.store` |
| GET | `/domains/{domain}/edit` | `edit` | `domains.edit` |
| PATCH | `/domains/{domain}` | `update` | `domains.update` |
| DELETE | `/domains/{domain}` | `destroy` | `domains.destroy` |

> `domains.show` is NOT used — exclude it or leave it returning a redirect to concepts.

---

## Form Request Classes

### `StoreDomainRequest`

```php
// app/Http/Requests/StoreDomainRequest.php
public function authorize(): bool
{
    return auth()->check();
}

public function rules(): array
{
    return [
        'name'  => ['required', 'string', 'max:255'],
        'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    ];
}
```

### `UpdateDomainRequest`

```php
// app/Http/Requests/UpdateDomainRequest.php
public function authorize(): bool
{
    return auth()->check(); // ownership is checked in controller, not here
}

public function rules(): array
{
    return [
        'name'  => ['required', 'string', 'max:255'],
        'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
    ];
}
```

---

## Controller Logic

```php
// app/Http/Controllers/DomainController.php

// index — eager load to prevent N+1
public function index()
{
    $domains = auth()->user()
        ->domains()
        ->withCount(['concepts', 'concepts as mastered_count' => fn($q) => $q->where('status', 'mastered')])
        ->get();

    return view('domains.index', compact('domains'));
}

// store — user_id comes from auth, NOT from request
public function store(StoreDomainRequest $request)
{
    auth()->user()->domains()->create($request->validated());
    return redirect()->route('domains.index')->with('success', 'Domaine créé avec succès.');
}

// edit — ownership check mandatory
public function edit(Domain $domain)
{
    abort_if($domain->user_id !== auth()->id(), 403);
    return view('domains.edit', compact('domain'));
}

// update — ownership check mandatory
public function update(UpdateDomainRequest $request, Domain $domain)
{
    abort_if($domain->user_id !== auth()->id(), 403);
    $domain->update($request->validated());
    return redirect()->route('domains.index')->with('success', 'Domaine mis à jour.');
}

// destroy — ownership check mandatory
public function destroy(Domain $domain)
{
    abort_if($domain->user_id !== auth()->id(), 403);
    $domain->delete();
    return redirect()->route('domains.index')->with('success', 'Domaine supprimé.');
}
```

---

## Blade Views

### `domains/index.blade.php` must display:
- A "Nouveau domaine" button linking to `domains.create`
- For each domain: name, color badge, `$domain->concepts_count` total, `$domain->mastered_count` maîtrisés
- A link to the concept list (route `domains.concepts.index`)
- Edit and Delete buttons (DELETE uses a form with `@method('DELETE')` and `@csrf`)
- An empty state message if no domains exist yet

### `domains/create.blade.php` must have:
- `<form method="POST" action="{{ route('domains.store') }}">` with `@csrf`
- Text input for `name` with `@error('name')` validation display
- `<input type="color" name="color">` for the color picker with `@error('color')` display
- A "Créer" submit button and a "Annuler" link back to index

### `domains/edit.blade.php` must have:
- `<form method="POST" action="{{ route('domains.update', $domain) }}">` with `@csrf` and `@method('PATCH')`
- Pre-filled name input: `value="{{ old('name', $domain->name) }}"`
- Pre-filled color input: `value="{{ old('color', $domain->color) }}"`
- A "Mettre à jour" submit button and a "Annuler" link back to index

### Delete button (on index or wherever placed):
```blade
<form method="POST" action="{{ route('domains.destroy', $domain) }}">
    @csrf
    @method('DELETE')
    <button type="submit" onclick="return confirm('Supprimer ce domaine ?')">
        Supprimer
    </button>
</form>
```

---

## Agent Output — what was generated
- Created `Domain` model with `$fillable` and relationships.
- Created `create_domains_table` migration with foreign key and cascade delete.
- Created `DomainController` with all resource methods (except `show`).
- Implemented `StoreDomainRequest` and `UpdateDomainRequest` for validation.
- Created Blade views for `index`, `create`, and `edit` using Tailwind CSS and `<x-app-layout>`.
- Integrated "Mes Domaines" link in the navigation.

## What I changed manually
- Converted Blade views from `@extends` to `<x-app-layout>` to match the Breeze layout structure.
- Ensured `index` method uses `withCount` to prevent N+1 queries.
- Added ownership checks in controller methods using `abort_if`.
