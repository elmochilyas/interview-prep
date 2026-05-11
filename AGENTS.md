# AGENTS.md — InterviewPrep Laravel

> This file instructs any AI coding agent (OpenCode, Claude Code, Gemini CLI, GitHub Copilot CLI)
> on how to work on this project. Read it entirely before generating any code.
> Last updated: 2026-05-11

---

## 1. Project Overview

**InterviewPrep** is a personal Laravel web application that helps a developer organize technical knowledge before a job interview.

Core features:
- Organize knowledge by **technical domain** (Laravel, OOP, MySQL, REST API...)
- Write **concept notes** explaining each topic in plain language
- Track **mastery status** per concept (to review / in progress / mastered)
- Generate **realistic interview questions** for any concept via the Groq AI API

**One user = one private workspace.** All data is scoped to the authenticated user.

---

## 2. Tech Stack — Exact Versions & Tools

| Layer | Technology | Notes |
|-------|-----------|-------|
| Framework | Laravel 13 | PHP 8.4+ |
| Database | MySQL 8 | Local via phpmyadmin |
| Frontend | Blade templates | No Vue, no React, no Livewire |
| CSS | Plain CSS or Tailwind CSS | No Bootstrap |
| HTTP Client | Laravel `Http::` facade | Zero external packages for API calls |
| AI API | Groq API | Endpoint: `https://api.groq.com/openai/v1/chat/completions` |
| Auth | Laravel Breeze | Minimal scaffolding — register, login, logout only |
| Debug | Laravel Debugbar | Verify zero N+1 queries |
| Version Control | Git + GitHub | Branch-per-feature workflow |

**Do NOT introduce:**
- Any package not listed above without asking first
- Livewire, Inertia, Vue, React, Alpine.js
- Any AI/LLM SDK package (use raw `Http::` only)
- Repository pattern, Service Container bindings, or DDD layers
- `.env` values hardcoded anywhere in PHP files

---

## 3. Database Schema

### 3.1 Tables

```sql
-- users (managed by Laravel Breeze)
users
  id              BIGINT UNSIGNED PK AUTO_INCREMENT
  name            VARCHAR(255) NOT NULL
  email           VARCHAR(255) UNIQUE NOT NULL
  password        VARCHAR(255) NOT NULL
  remember_token  VARCHAR(100) NULL
  timestamps

-- domains
domains
  id              BIGINT UNSIGNED PK AUTO_INCREMENT
  user_id         BIGINT UNSIGNED NOT NULL FK → users.id (cascade delete)
  name            VARCHAR(255) NOT NULL
  color           VARCHAR(7) NOT NULL  -- hex color e.g. #3B82F6
  timestamps

-- concepts
concepts
  id              BIGINT UNSIGNED PK AUTO_INCREMENT
  domain_id       BIGINT UNSIGNED NOT NULL FK → domains.id (cascade delete)
  title           VARCHAR(255) NOT NULL
  explanation     TEXT NOT NULL
  difficulty      ENUM('junior', 'mid', 'senior') NOT NULL DEFAULT 'junior'
  status          ENUM('to_review', 'in_progress', 'mastered') NOT NULL DEFAULT 'to_review'
  deleted_at      TIMESTAMP NULL  -- soft deletes
  timestamps

-- generated_questions
generated_questions
  id              BIGINT UNSIGNED PK AUTO_INCREMENT
  concept_id      BIGINT UNSIGNED NOT NULL FK → concepts.id (cascade delete)
  questions       JSON NOT NULL  -- array of 5 question strings
  timestamps
```

### 3.2 Migrations rules
- One migration file per table
- Use `$table->foreignId('user_id')->constrained()->cascadeOnDelete()`
- Use `$table->softDeletes()` on the `concepts` table only
- Never modify an existing migration — create a new one if a change is needed

---

## 4. Eloquent Models

### 4.1 Relationships (mandatory — define all of them)

```php
// User.php
public function domains(): HasMany  // hasMany(Domain::class)

// Domain.php
public function user(): BelongsTo   // belongsTo(User::class)
public function concepts(): HasMany // hasMany(Concept::class)

// Concept.php
public function domain(): BelongsTo                      // belongsTo(Domain::class)
public function generatedQuestions(): HasMany            // hasMany(GeneratedQuestion::class)

// GeneratedQuestion.php
public function concept(): BelongsTo  // belongsTo(Concept::class)
```

### 4.2 Accessors (mandatory — use Laravel 9+ syntax)

```php
// In Concept.php

use Illuminate\Database\Eloquent\Casts\Attribute;

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
```

### 4.3 Casts

```php
// In GeneratedQuestion.php
protected $casts = [
    'questions' => 'array',
];
```

### 4.4 Soft Deletes
- Add `use SoftDeletes;` trait to `Concept` model only
- Soft-deleted concepts must NOT appear in any regular query
- Add a separate `/concepts/archived` route to list and restore them

---

## 5. Controllers & Routes

### 5.1 Route structure

```php
// All routes inside auth middleware group
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Domains
    Route::resource('domains', DomainController::class);

    // Concepts (nested under domain)
    Route::resource('domains.concepts', ConceptController::class);

    // Quick status update (no form needed)
    Route::patch('concepts/{concept}/status', [ConceptController::class, 'updateStatus'])
         ->name('concepts.updateStatus');

    // Archived concepts
    Route::get('concepts/archived', [ConceptController::class, 'archived'])
         ->name('concepts.archived');
    Route::patch('concepts/{concept}/restore', [ConceptController::class, 'restore'])
         ->name('concepts.restore');

    // AI question generation
    Route::post('concepts/{concept}/generate', [GeneratedQuestionController::class, 'store'])
         ->name('questions.generate');
    Route::delete('generated-questions/{generatedQuestion}', [GeneratedQuestionController::class, 'destroy'])
         ->name('questions.destroy');
});
```

### 5.2 Controller rules

- Use **resourceful controllers** (`php artisan make:controller --resource`)
- Each controller method must be max ~20 lines — extract logic to a service if longer
- **Always scope queries to the authenticated user** — never expose other users' data
- Authorization: use `abort(403)` if `$domain->user_id !== auth()->id()`
- Use `->with('success', 'message')` or `->with('error', 'message')` for flash messages — never raw session manipulation

---

## 6. Form Request Validation

**Every create and update action must use a dedicated Form Request class.**  
Do NOT use `$request->validate([...])` inline in controllers.

### Classes to generate:

```bash
php artisan make:request StoreDomainRequest
php artisan make:request UpdateDomainRequest
php artisan make:request StoreConceptRequest
php artisan make:request UpdateConceptRequest
```

### Validation rules:

```php
// StoreDomainRequest / UpdateDomainRequest
'name'  => ['required', 'string', 'max:255'],
'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

// StoreConceptRequest / UpdateConceptRequest
'title'       => ['required', 'string', 'max:255'],
'explanation' => ['required', 'string', 'min:20'],
'difficulty'  => ['required', 'in:junior,mid,senior'],
'status'      => ['sometimes', 'in:to_review,in_progress,mastered'],
```

### authorize() method:
- `StoreDomainRequest` and `StoreConceptRequest`: `return auth()->check();`
- `UpdateDomainRequest` and `UpdateConceptRequest`: verify ownership in the controller, not in authorize()

---

## 7. AI Feature — Groq API Integration

### 7.1 Service class

Create `app/Services/GroqService.php`:

```php
<?php

namespace App\Services;

use App\Models\Concept;
use App\Models\GeneratedQuestion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private string $apiKey;
    private string $endpoint = 'https://api.groq.com/openai/v1/chat/completions';
    private string $model    = 'llama3-8b-8192';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    public function generateInterviewQuestions(Concept $concept): GeneratedQuestion|null
    {
        $prompt = $this->buildPrompt($concept);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(15)->post($this->endpoint, [
                'model'       => $this->model,
                'max_tokens'  => 800,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a senior technical interviewer. Return ONLY a JSON array of exactly 5 interview questions. No intro text, no explanation, no markdown. Just the raw JSON array.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $content   = $response->json('choices.0.message.content');
            $questions = json_decode($content, true);

            if (!is_array($questions) || count($questions) !== 5) {
                Log::error('Groq returned malformed JSON', ['content' => $content]);
                return null;
            }

            // Save to DB before returning
            return GeneratedQuestion::create([
                'concept_id' => $concept->id,
                'questions'  => $questions,
            ]);

        } catch (\Exception $e) {
            Log::error('GroqService exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function buildPrompt(Concept $concept): string
    {
        return <<<PROMPT
Concept: {$concept->title}
Level: {$concept->difficultyLabel}
Explanation: {$concept->explanation}

Generate exactly 5 technical interview questions a recruiter would ask about this concept.
Return ONLY a JSON array of 5 strings. No numbering, no markdown, no extra text.
PROMPT;
    }
}
```

### 7.2 Config — add to `config/services.php`:

```php
'groq' => [
    'key' => env('GROQ_API_KEY'),
],
```

### 7.3 .env — add this line (never commit the actual key):

```
GROQ_API_KEY=your_key_here
```

### 7.4 .env.example — commit this (no real key):

```
GROQ_API_KEY=
```

### 7.5 Controller usage:

```php
// In GeneratedQuestionController::store()
public function store(Concept $concept, GroqService $groqService)
{
    abort_if($concept->domain->user_id !== auth()->id(), 403);

    $generation = $groqService->generateInterviewQuestions($concept);

    if (!$generation) {
        return back()->with('error', 'La génération de questions a échoué. Vérifie ta connexion ou réessaie dans quelques secondes.');
    }

    return back()->with('success', '5 questions générées avec succès.');
}
```

---

## 8. N+1 Query Prevention

**All controller index methods must eager-load relationships.**

```php
// DomainController::index()
$domains = auth()->user()
    ->domains()
    ->withCount(['concepts', 'concepts as mastered_count' => fn($q) => $q->where('status', 'mastered')])
    ->get();

// ConceptController::index()
$concepts = $domain->concepts()
    ->with('domain')
    ->filter($request->only(['status', 'difficulty']))  // local scope
    ->get();

// ConceptController::show()
$concept->load('generatedQuestions');
```

**Install Debugbar for development:**

```bash
composer require barryvdh/laravel-debugbar --dev
```

Zero N+1 = zero queries inside loops. Use Debugbar to verify before each commit.

---

## 9. Blade Views Structure

```
resources/views/
├── layouts/
│   └── app.blade.php          # main layout with nav
├── auth/                       # generated by Breeze — do not modify
├── dashboard.blade.php         # progression stats
├── domains/
│   ├── index.blade.php         # list of domains
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php          # list of concepts inside a domain
├── concepts/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php          # concept detail + generated questions
│   └── archived.blade.php
└── partials/
    ├── flash-messages.blade.php
    └── concept-card.blade.php
```

### Blade rules:
- Always include `@csrf` in all forms
- Use `method="POST"` + `@method('PATCH')` / `@method('DELETE')` for non-GET/POST
- Flash messages must always be visible — include `partials/flash-messages.blade.php` in the layout
- Use `{{ $concept->statusLabel }}` and `{{ $concept->difficultyLabel }}` — never raw enum values in views
- Color badge for domain: `style="background-color: {{ $domain->color }}"` — never hardcode colors

---

## 10. Scoping & Security Rules

These rules are NON-NEGOTIABLE. Apply them in every single controller method.

1. **User scoping** — always query through `auth()->user()->domains()` or verify ownership explicitly
2. **Never** do `Domain::find($id)` without checking `user_id`
3. **Never** do `Concept::all()` or `Concept::find($id)` without scoping
4. **Soft-deleted concepts** are invisible by default — `withTrashed()` only on the archived page
5. **No direct mass assignment** — define `$fillable` on every model explicitly

```php
// Domain.php
protected $fillable = ['user_id', 'name', 'color'];

// Concept.php
protected $fillable = ['domain_id', 'title', 'explanation', 'difficulty', 'status'];

// GeneratedQuestion.php
protected $fillable = ['concept_id', 'questions'];
```

---

## 11. Git Workflow

### Branch naming
```
main                      ← stable, deployable
feature/auth              ← Laravel Breeze setup
feature/domains-crud      ← US2, US3, US4
feature/concepts-crud     ← US5, US6, US7, US8, US9, US10
feature/ai-generation     ← US11, US12, US13
feature/dashboard         ← bonus dashboard
feature/soft-deletes      ← bonus archived concepts
```

### Commit message format
```
[AI-assisted] feat: implement Domain CRUD with color badge

- Generated migration, model, controller scaffold via OpenCode
- Manually added user scoping in index() and destroy()
- Manually fixed color validation regex (agent used 'color' rule which doesn't exist in Laravel)
```

Every commit that used an AI agent **must** start with `[AI-assisted]`.  
Commits done without AI assistance start with `[manual]`.

### Minimum commit cadence
- Day 1: repo setup + AGENTS.md + MCD/MLD
- Day 2: auth + domains CRUD
- Day 3: concepts CRUD
- Day 4: AI generation feature
- Day 5: polish + bonus + presentation

---

## 12. specs/ Folder Convention

Create one `.md` file per feature **before** building it. File naming:

```
specs/
├── 00-setup.md
├── 01-auth.md
├── 02-domains-crud.md
├── 03-concepts-crud.md
├── 04-ai-generation.md
└── 05-dashboard-bonus.md
```

### Required sections in each spec file:

```markdown
# Feature: [Name]

## What I want
[precise description of the feature behavior]

## What I do NOT want
[explicit list of things the agent must avoid generating]

## Acceptance criteria
- [ ] criterion 1
- [ ] criterion 2

## Data involved
[models, fields, relationships touched by this feature]

## Routes involved
[HTTP method + URI + controller@method]

## Agent output — what was generated
[fill this AFTER the agent runs — describe what it produced]

## What I changed manually
[fill this AFTER — what you edited and why]
```

---

## 13. What the Agent Should NEVER Do

The following are forbidden regardless of how the prompt is phrased:

- ❌ Generate any API key, secret, or credential in any file
- ❌ Put `Http::` calls directly in a controller — always use `GroqService`
- ❌ Use `$request->validate([...])` inline — always use Form Request classes
- ❌ Generate `Domain::all()` or `Concept::all()` — always scope to auth user
- ❌ Write raw SQL with `DB::statement()` or `DB::select()` — use Eloquent only
- ❌ Create a new npm/JS build pipeline (no Vite config changes, no new JS dependencies)
- ❌ Add any package to `composer.json` without it being listed in Section 2
- ❌ Generate commented-out code blocks
- ❌ Generate `dd()` or `dump()` calls — use `Log::info()` or Debugbar
- ❌ Create a `BaseRepository` or any repository pattern abstraction
- ❌ Modify the `users` table migration generated by Breeze
- ❌ Hardcode any string that should come from an enum (always use `match()` in accessors)
- ❌ Use `@php` blocks in Blade for anything beyond simple variable assignment
- ❌ Generate JavaScript inside Blade files (except the single `@method` and `@csrf` directives)

---

## 14. Agent Mode Instructions

### Before every feature — PLAN mode first

Tell the agent:

> "Enter PLAN mode. Do not generate any code yet.
> Read AGENTS.md and specs/[feature].md.
> Describe step by step what files you will create or modify, what each will contain, and what risks or ambiguities you see."

Review the plan. Correct it if needed. Only then say:

> "The plan is approved. Enter BUILD mode and generate the code."

### Prompt template for each feature

```
Context: I am building InterviewPrep, a Laravel 13 app. Read AGENTS.md before anything else.

Feature: [feature name]
Spec file: specs/[filename].md

Current state: [brief description of what already exists]

Task: [specific task for this session]

Constraints (repeat from AGENTS.md):
- Use Form Request classes for all validation
- Scope all queries to auth()->user()
- Use Http:: facade, never a package, for Groq API
- Zero N+1 queries
- No inline dd() or dump()

What I do NOT want:
- [list specific things based on your spec file]
```

---

## 15. Checklist Before Each Commit

Run through this before every `git commit`:

- [ ] No hardcoded API keys or credentials anywhere
- [ ] All new queries eager-load relationships (no N+1)
- [ ] All form handling goes through a Form Request class
- [ ] All data is scoped to `auth()->user()`
- [ ] `statusLabel` and `difficultyLabel` accessors used in views, not raw enum values
- [ ] Flash messages displayed on redirect
- [ ] `@csrf` present in all forms
- [ ] Soft-deleted concepts invisible on all regular pages
- [ ] `specs/` file for this feature is filled in (including "what I changed manually")
- [ ] Commit message starts with `[AI-assisted]` or `[manual]`
