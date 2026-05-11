# Feature: Concepts CRUD

> **Spec file:** `specs/03-concepts-crud.md`
> **Branch:** `feature/concepts-crud`
> **Day:** 3 — Wednesday 13/05/2026
> **User Stories covered:** US5, US6, US7, US8, US9, US10
> **Bonus covered:** Soft deletes (US-BON2, US-BON3), Combined filter (US-BON4)

---

## What I want

A full CRUD for concepts, nested under a domain, with the following behaviors:

- **List page** (`/domains/{domain}/concepts`): shows all non-deleted concepts of a domain. Each row shows title, difficulty label, status label, a quick-status-change control, and links to edit/delete/show. Filterable by status AND difficulty simultaneously via URL query params.
- **Create page** (`/domains/{domain}/concepts/create`): form with title, explanation (textarea), difficulty (select), status defaults to `to_review` and is hidden or pre-set. Validates via `StoreConceptRequest`.
- **Detail page** (`/domains/{domain}/concepts/{concept}`): shows title, explanation, difficultyLabel, statusLabel, and the generated questions section (empty until AI feature is built). Has an "Edit" button and the AI generate button (wired in feature/ai-generation).
- **Edit page** (`/domains/{domain}/concepts/{concept}/edit`): pre-filled form to update title, explanation, difficulty, status. Validates via `UpdateConceptRequest`.
- **Delete**: soft delete — sets `deleted_at`, does NOT permanently remove. The concept disappears from all regular pages.
- **Quick status change** (`PATCH /concepts/{concept}/status`): a select or button group directly on the list row. Updates only the `status` field. No full form submission. Owner check required.
- **Archived page** (`/concepts/archived`): lists all soft-deleted concepts belonging to the authenticated user. Has a "Restore" button per concept.
- **Restore** (`PATCH /concepts/{concept}/restore`): restores a soft-deleted concept. Owner check required.

---

## What I do NOT want

- ❌ Do NOT use `$request->validate([...])` inline — Form Request classes only
- ❌ Do NOT use `Concept::all()` or `Concept::find($id)` without scoping to authenticated user
- ❌ Do NOT show soft-deleted concepts on any regular page — `withTrashed()` only on the archived page
- ❌ Do NOT permanently delete concepts with the regular delete action — it must soft delete
- ❌ Do NOT add a "force delete" (permanent) button at this stage
- ❌ Do NOT use JavaScript for the quick status update — use a mini HTML form with `@method('PATCH')`
- ❌ Do NOT allow a user to access concepts that belong to another user's domain — `abort(403)`
- ❌ Do NOT add `$domain->user_id` check in Form Request `authorize()` — do it in the controller
- ❌ Do NOT generate `@php` blocks in Blade for logic — use controller or model methods
- ❌ Do NOT display raw enum values (`to_review`, `junior`) in views — always use `statusLabel` and `difficultyLabel` accessors

---

## Acceptance Criteria

- [ ] `GET /domains/{domain}/concepts` lists only non-deleted concepts of that domain
- [ ] Filter by status (`?status=mastered`) returns only mastered concepts
- [ ] Filter by difficulty (`?difficulty=senior`) returns only senior concepts
- [ ] Combined filter (`?status=mastered&difficulty=senior`) works correctly
- [ ] `GET /domains/{domain}/concepts/create` renders the form
- [ ] `POST /domains/{domain}/concepts` with valid data creates the concept with status = `to_review`
- [ ] `POST /domains/{domain}/concepts` with invalid data shows validation errors
- [ ] `GET /domains/{domain}/concepts/{concept}` shows concept details and generated questions section
- [ ] `GET /domains/{domain}/concepts/{concept}/edit` shows pre-filled edit form
- [ ] `PATCH /domains/{domain}/concepts/{concept}` updates the concept
- [ ] `DELETE /domains/{domain}/concepts/{concept}` soft-deletes the concept (sets `deleted_at`)
- [ ] Soft-deleted concept does NOT appear on the list page
- [ ] `PATCH /concepts/{concept}/status` updates the status without opening a form
- [ ] `GET /concepts/archived` shows only soft-deleted concepts for the authenticated user
- [ ] `PATCH /concepts/{concept}/restore` restores a soft-deleted concept
- [ ] Status and difficulty are shown using labels (`Maîtrisé`, `Senior`) not raw values
- [ ] A user cannot access another user's concepts — 403
- [ ] No N+1 queries on the concept list page (verified with Debugbar)

---

## Data Involved

### Model: `Concept`

```php
// app/Models/Concept.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Concept extends Model
{
    use SoftDeletes;

    protected $fillable = ['domain_id', 'title', 'explanation', 'difficulty', 'status'];

    // Relationships
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function generatedQuestions(): HasMany
    {
        return $this->hasMany(GeneratedQuestion::class);
    }

    // Accessors — MANDATORY — Laravel 9+ syntax
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->status) {
                'to_review'   => 'À revoir',
                'in_progress' => 'En cours',
                'mastered'    => 'Maîtrisé',
            }
        );
    }

    protected function difficultyLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match($this->difficulty) {
                'junior' => 'Junior',
                'mid'    => 'Mid',
                'senior' => 'Senior',
            }
        );
    }

    // Local scope for filtering — used in ConceptController::index()
    public function scopeFilter($query, array $filters): void
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }
    }
}
```

---

## Routes Involved

```php
// Nested resource — concepts always belong to a domain
Route::resource('domains.concepts', ConceptController::class);

// Quick status update — standalone, not nested
Route::patch('concepts/{concept}/status', [ConceptController::class, 'updateStatus'])
     ->name('concepts.updateStatus');

// Soft delete management — IMPORTANT: define BEFORE the resource route to avoid conflict
Route::get('concepts/archived', [ConceptController::class, 'archived'])
     ->name('concepts.archived');
Route::patch('concepts/{concept}/restore', [ConceptController::class, 'restore'])
     ->name('concepts.restore');
```

> ⚠️ The `concepts/archived` GET route MUST be defined BEFORE `Route::resource()` to avoid Laravel routing it to `concepts.show` with `{concept}` = "archived".

Full generated routes from `domains.concepts` resource:

| Method | URI | Controller Method | Route Name |
|--------|-----|-------------------|------------|
| GET | `/domains/{domain}/concepts` | `index` | `domains.concepts.index` |
| GET | `/domains/{domain}/concepts/create` | `create` | `domains.concepts.create` |
| POST | `/domains/{domain}/concepts` | `store` | `domains.concepts.store` |
| GET | `/domains/{domain}/concepts/{concept}` | `show` | `domains.concepts.show` |
| GET | `/domains/{domain}/concepts/{concept}/edit` | `edit` | `domains.concepts.edit` |
| PATCH | `/domains/{domain}/concepts/{concept}` | `update` | `domains.concepts.update` |
| DELETE | `/domains/{domain}/concepts/{concept}` | `destroy` | `domains.concepts.destroy` |

---

## Form Request Classes

### `StoreConceptRequest`

```php
public function authorize(): bool { return auth()->check(); }

public function rules(): array
{
    return [
        'title'       => ['required', 'string', 'max:255'],
        'explanation' => ['required', 'string', 'min:20'],
        'difficulty'  => ['required', 'in:junior,mid,senior'],
    ];
}
```

### `UpdateConceptRequest`

```php
public function authorize(): bool { return auth()->check(); }

public function rules(): array
{
    return [
        'title'       => ['required', 'string', 'max:255'],
        'explanation' => ['required', 'string', 'min:20'],
        'difficulty'  => ['required', 'in:junior,mid,senior'],
        'status'      => ['required', 'in:to_review,in_progress,mastered'],
    ];
}
```

---

## Controller Logic

```php
// app/Http/Controllers/ConceptController.php

// index — scoped to domain, eager load, filterable
public function index(Request $request, Domain $domain)
{
    abort_if($domain->user_id !== auth()->id(), 403);

    $concepts = $domain->concepts()
        ->with('domain')
        ->filter($request->only(['status', 'difficulty']))
        ->get();

    return view('concepts.index', compact('domain', 'concepts'));
}

// store — domain ownership check + status forced to to_review
public function store(StoreConceptRequest $request, Domain $domain)
{
    abort_if($domain->user_id !== auth()->id(), 403);

    $domain->concepts()->create(array_merge(
        $request->validated(),
        ['status' => 'to_review']
    ));

    return redirect()
        ->route('domains.concepts.index', $domain)
        ->with('success', 'Concept créé avec succès.');
}

// show — load generated questions
public function show(Domain $domain, Concept $concept)
{
    abort_if($domain->user_id !== auth()->id(), 403);
    $concept->load('generatedQuestions');
    return view('concepts.show', compact('domain', 'concept'));
}

// updateStatus — quick change, only updates status field
public function updateStatus(Request $request, Concept $concept)
{
    abort_if($concept->domain->user_id !== auth()->id(), 403);

    $request->validate(['status' => ['required', 'in:to_review,in_progress,mastered']]);
    $concept->update(['status' => $request->status]);

    return back()->with('success', 'Statut mis à jour.');
}

// destroy — soft delete (SoftDeletes trait handles this)
public function destroy(Domain $domain, Concept $concept)
{
    abort_if($domain->user_id !== auth()->id(), 403);
    $concept->delete(); // sets deleted_at, does NOT permanently remove
    return redirect()
        ->route('domains.concepts.index', $domain)
        ->with('success', 'Concept archivé.');
}

// archived — show soft-deleted concepts for current user only
public function archived()
{
    $concepts = Concept::onlyTrashed()
        ->whereHas('domain', fn($q) => $q->where('user_id', auth()->id()))
        ->with('domain')
        ->get();

    return view('concepts.archived', compact('concepts'));
}

// restore — undelete a concept
public function restore(Concept $concept)
{
    // Must use withTrashed() to find soft-deleted record
    $concept = Concept::withTrashed()->findOrFail($concept->id ?? request()->route('concept'));
    abort_if($concept->domain->user_id !== auth()->id(), 403);
    $concept->restore();
    return redirect()->route('concepts.archived')->with('success', 'Concept restauré.');
}
```

---

## Blade Views

### `concepts/index.blade.php` must have:
- Filter form with `<select name="status">` and `<select name="difficulty">` — GET method, submits to same page
- Table or card list with: title, `$concept->difficultyLabel`, `$concept->statusLabel`
- Quick status form per row:
```blade
<form method="POST" action="{{ route('concepts.updateStatus', $concept) }}">
    @csrf
    @method('PATCH')
    <select name="status" onchange="this.form.submit()">
        <option value="to_review"   @selected($concept->status === 'to_review')>À revoir</option>
        <option value="in_progress" @selected($concept->status === 'in_progress')>En cours</option>
        <option value="mastered"    @selected($concept->status === 'mastered')>Maîtrisé</option>
    </select>
</form>
```
- Link to `domains.concepts.show`, link to `domains.concepts.edit`
- Delete form with `@method('DELETE')` and `@csrf`
- "Nouveau concept" button

### `concepts/show.blade.php` must have:
- `$concept->title`, `$concept->difficultyLabel`, `$concept->statusLabel`
- `$concept->explanation` in a formatted block
- Generated questions section (loop over `$concept->generatedQuestions` — empty until AI feature)
- "Générer des questions" button (will be wired in specs/04)
- "Modifier" link to edit page

### `concepts/archived.blade.php` must have:
- List of archived concepts with their domain name
- Restore form per concept:
```blade
<form method="POST" action="{{ route('concepts.restore', $concept->id) }}">
    @csrf
    @method('PATCH')
    <button type="submit">Restaurer</button>
</form>
```

---

## Agent Output — What Was Generated
> *(Fill this in after the agent runs)*

---

## What I Changed Manually
> *(Fill this in after the agent runs — what you edited and why)*
