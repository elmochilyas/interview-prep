# Feature: Dashboard & Bonus Features

> **Spec file:** `specs/05-dashboard-bonus.md`
> **Branch:** `feature/dashboard`
> **Day:** 5 — Friday 15/05/2026 (before 13h00)
> **User Stories covered:** Bonus Dashboard, Bonus Soft Deletes already in 03, Bonus Combined Filter already in 03

---

## What I want

A meaningful dashboard at `/dashboard` that gives the user an instant overview of their interview preparation progress:

- **Total concepts** across all domains
- **Concepts by status**: count of `to_review`, `in_progress`, `mastered`
- **Mastery percentage**: `(mastered / total) * 100` — shown as a percentage
- **Best mastered domain**: the domain with the highest ratio of mastered/total concepts
- **Most to-review domain**: the domain with the most `to_review` concepts
- **Total questions generated**: total count of all `GeneratedQuestion` rows for this user
- A quick-access list of all domains with their progress bars

All stats are scoped to the authenticated user only.  
Stats must be computed in the controller — zero logic in the Blade view.

---

## What I do NOT want

- ❌ Do NOT compute stats with raw SQL `DB::select()` — use Eloquent with `withCount()` and collection methods
- ❌ Do NOT put any calculation logic inside the Blade template — all numbers come pre-computed from the controller
- ❌ Do NOT display any data from other users
- ❌ Do NOT add charts or graphs using a JavaScript charting library — plain HTML/CSS only (progress bars with inline width style is fine)
- ❌ Do NOT add a "delete all" or "reset progress" button on the dashboard
- ❌ Do NOT eager load the full `explanation` text of concepts on this page — only count data needed
- ❌ Do NOT add pagination to the dashboard
- ❌ Do NOT add any new package to achieve the dashboard stats

---

## Acceptance Criteria

- [ ] `GET /dashboard` returns 200 for authenticated users
- [ ] Total concept count is correct and excludes soft-deleted concepts
- [ ] `to_review`, `in_progress`, `mastered` counts are all correct
- [ ] Mastery percentage is calculated correctly (0% if no concepts)
- [ ] Best mastered domain is shown (or "Aucun domaine" if no domains exist)
- [ ] Most to-review domain is shown (or "Aucun domaine" if no domains exist)
- [ ] Total generated questions count is shown
- [ ] Domain list with per-domain progress is shown (name + mastered/total)
- [ ] All stats are for the authenticated user only — verified by creating a second test user
- [ ] No N+1 queries on the dashboard page (verified with Debugbar)
- [ ] Dashboard shows a helpful empty state when the user has no domains yet

---

## Data Involved

All models are read-only here — no writes on the dashboard.

- `Domain` → filtered by `auth()->user()->domains()`
- `Concept` → counted via `withCount()` — no soft-deleted concepts
- `GeneratedQuestion` → counted via nested relationship

---

## Routes Involved

```php
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
```

---

## Controller Logic

```php
// app/Http/Controllers/DashboardController.php
<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Load domains with all needed counts — single query with withCount
        $domains = $user->domains()
            ->withCount([
                'concepts',
                'concepts as to_review_count'   => fn($q) => $q->where('status', 'to_review'),
                'concepts as in_progress_count'  => fn($q) => $q->where('status', 'in_progress'),
                'concepts as mastered_count'     => fn($q) => $q->where('status', 'mastered'),
            ])
            ->get();

        // Total concepts (excludes soft-deleted — SoftDeletes handles this)
        $totalConcepts  = $domains->sum('concepts_count');
        $totalMastered  = $domains->sum('mastered_count');
        $totalInProgress = $domains->sum('in_progress_count');
        $totalToReview  = $domains->sum('to_review_count');

        // Mastery percentage — avoid division by zero
        $masteryPercentage = $totalConcepts > 0
            ? round(($totalMastered / $totalConcepts) * 100)
            : 0;

        // Best mastered domain (highest mastered_count)
        $bestDomain = $domains->sortByDesc('mastered_count')->first();

        // Most to-review domain (highest to_review_count)
        $mostToReviewDomain = $domains->sortByDesc('to_review_count')->first();

        // Total generated questions
        $totalQuestions = \App\Models\GeneratedQuestion::whereHas(
            'concept.domain',
            fn($q) => $q->where('user_id', $user->id)
        )->count();

        return view('dashboard', compact(
            'domains',
            'totalConcepts',
            'totalMastered',
            'totalInProgress',
            'totalToReview',
            'masteryPercentage',
            'bestDomain',
            'mostToReviewDomain',
            'totalQuestions'
        ));
    }
}
```

---

## Blade View — `dashboard.blade.php`

Structure the dashboard with these sections:

### Section 1: Summary Stats (4 stat cards)
```blade
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-number">{{ $totalConcepts }}</span>
        <span class="stat-label">Total concepts</span>
    </div>
    <div class="stat-card stat-mastered">
        <span class="stat-number">{{ $totalMastered }}</span>
        <span class="stat-label">Maîtrisés</span>
    </div>
    <div class="stat-card stat-progress">
        <span class="stat-number">{{ $totalInProgress }}</span>
        <span class="stat-label">En cours</span>
    </div>
    <div class="stat-card stat-review">
        <span class="stat-number">{{ $totalToReview }}</span>
        <span class="stat-label">À revoir</span>
    </div>
</div>
```

### Section 2: Global Progress Bar
```blade
<div class="progress-section">
    <p>Progression globale : <strong>{{ $masteryPercentage }}%</strong> maîtrisé</p>
    <div class="progress-bar-bg">
        <div class="progress-bar-fill" style="width: {{ $masteryPercentage }}%"></div>
    </div>
</div>
```

### Section 3: Highlights
```blade
<div class="highlights">
    <div>
        <strong>🏆 Meilleur domaine :</strong>
        {{ $bestDomain ? $bestDomain->name . ' (' . $bestDomain->mastered_count . ' maîtrisés)' : 'Aucun domaine' }}
    </div>
    <div>
        <strong>🔴 Plus à revoir :</strong>
        {{ $mostToReviewDomain ? $mostToReviewDomain->name . ' (' . $mostToReviewDomain->to_review_count . ' à revoir)' : 'Aucun domaine' }}
    </div>
    <div>
        <strong>🤖 Questions générées :</strong> {{ $totalQuestions }}
    </div>
</div>
```

### Section 4: Per-Domain Progress List
```blade
@forelse ($domains as $domain)
    <div class="domain-row">
        <span class="domain-badge" style="background-color: {{ $domain->color }}">
            {{ $domain->name }}
        </span>
        <span>{{ $domain->mastered_count }} / {{ $domain->concepts_count }} maîtrisés</span>
        <div class="progress-bar-bg">
            @php
                $pct = $domain->concepts_count > 0
                    ? round(($domain->mastered_count / $domain->concepts_count) * 100)
                    : 0;
            @endphp
            <div class="progress-bar-fill" style="width: {{ $pct }}%"></div>
        </div>
        <a href="{{ route('domains.concepts.index', $domain) }}">Voir les concepts →</a>
    </div>
@empty
    <p>Aucun domaine créé. <a href="{{ route('domains.create') }}">Créer votre premier domaine</a>.</p>
@endforelse
```

> Using `@php` for a single local `$pct` variable per loop iteration is acceptable here as an exception to the no-`@php`-blocks rule. It's a display calculation, not business logic.

---

## Empty State Requirements

If the user has no domains yet, the dashboard must show:
- A friendly welcome message
- A prominent "Créer mon premier domaine" button linking to `domains.create`
- No broken stat cards (all stats show 0, not errors)

---

## Agent Output — What Was Generated

- Built `DashboardController@index()` with full progression stats computation using `withCount()` — zero N+1 queries
- All stats computed server-side: total concepts, mastered/in-progress/to-review counts, mastery percentage, best domain, most-to-review domain, total generated questions count
- All 4 Blade sections implemented: stat cards (4 cards), global progress bar, highlight cards (3 cards), per-domain progress list with inline progress bars
- Empty state shown when user has no domains (with "Créer votre premier domaine" CTA link)
- Combined filter on concepts list confirmed working — `scopeFilter()` applies both status + difficulty simultaneously via URL query params

## What I Changed Manually

1. **Updated DashboardController** — Replaced placeholder `return view('dashboard')` with full stats computation using `withCount()` with aliased subcounts for to_review, in_progress, mastered
2. **Rebuilt dashboard.blade.php** — Replaced default Breeze placeholder with 4-section layout matching spec requirements (stats cards, progress bar, highlights, per-domain list)
3. **Verified combined filter** — Confirmed `scopeFilter()` in Concept.php correctly applies both `status` and `difficulty` filters simultaneously from URL query params
4. **Added empty state** — Per-domain section shows friendly message + "Créer votre premier domaine" CTA when no domains exist
