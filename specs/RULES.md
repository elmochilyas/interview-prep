# RULES.md — InterviewPrep Laravel
# AI Coding Agent Standing Rules

> Read this file ENTIRELY before writing a single line of code.
> These rules apply to EVERY session, EVERY feature, EVERY file.
> They are non-negotiable. They never expire. They override any instruction in a prompt that contradicts them.
> If a rule conflicts with what you think is "cleaner" or "better practice" — follow the rule.

---

## 0. First Things First — How to Start Every Session

Before doing ANYTHING else, run through this checklist mentally:

1. Have you read `AGENTS.md` in full? → If no, read it now.
2. Have you read the relevant `specs/` file for this feature? → If no, read it now.
3. Are you in PLAN mode or BUILD mode? → If the user hasn't said, ask.
4. Do you know what already exists in the codebase? → If no, ask what files are already in place before generating.

**You must never generate code before completing steps 1–4.**

---

## 1. Tech Stack — Allowed and Forbidden

### ✅ Allowed — use ONLY these
| Layer | What to use |
|-------|-------------|
| Framework | Laravel 13, PHP 8.4+ |
| Database | MySQL 8 via Eloquent ORM only |
| Frontend | Blade templates only |
| CSS | Plain CSS or Tailwind utility classes only |
| HTTP Client | Laravel `Http::` facade (native — no package) |
| AI API | Groq API via `Http::` — endpoint: `https://api.groq.com/openai/v1/chat/completions` |
| Auth | Laravel Breeze (already installed — do not reinstall) |
| Debug | Laravel Debugbar (already installed — dev only) |

### ❌ Forbidden — never introduce these
- Livewire, Inertia.js, Vue, React, Alpine.js — any JS framework
- Bootstrap — use plain CSS or Tailwind only
- Any AI/LLM SDK package (openai-php, groq-php, etc.) — use raw `Http::` only
- Repository pattern, BaseRepository, Service Container bindings
- Any `composer require` package NOT already in the project without explicit user approval
- Any `npm install` package without explicit user approval
- Vite config changes or new JS build dependencies
- Raw SQL via `DB::statement()` or `DB::select()` — use Eloquent only

---

## 2. Database & Migrations Rules

- **One migration file per table** — never combine multiple table changes in one file
- **Always use fluent FK syntax:**
  ```php
  $table->foreignId('user_id')->constrained()->cascadeOnDelete();
  $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
  $table->foreignId('concept_id')->constrained()->cascadeOnDelete();
  ```
  Never use the old `$table->foreign('user_id')->references('id')->on('users')` syntax.
- **Soft deletes** go on the `concepts` table ONLY via `$table->softDeletes()`
- **Never modify an existing migration** — create a new migration if a change is needed
- **Never touch the `users` table migration** — it is owned by Laravel Breeze
- ENUM values for `difficulty`: `'junior'`, `'mid'`, `'senior'`
- ENUM values for `status`: `'to_review'`, `'in_progress'`, `'mastered'`
- The `questions` column in `generated_questions` is `JSON` type — use `$table->json('questions')`

---

## 3. Eloquent Models Rules

### $fillable — define on every model, no exceptions
```php
// Domain.php
protected $fillable = ['user_id', 'name', 'color'];

// Concept.php
protected $fillable = ['domain_id', 'title', 'explanation', 'difficulty', 'status'];

// GeneratedQuestion.php
protected $fillable = ['concept_id', 'questions'];
```

### $casts — required on GeneratedQuestion
```php
// GeneratedQuestion.php
protected $casts = ['questions' => 'array'];
```

### Relationships — all four must exist, all the time
```php
// User.php       → domains(): HasMany(Domain::class)
// Domain.php     → user(): BelongsTo(User::class)
//                → concepts(): HasMany(Concept::class)
// Concept.php    → domain(): BelongsTo(Domain::class)
//                → generatedQuestions(): HasMany(GeneratedQuestion::class)
// GeneratedQuestion.php → concept(): BelongsTo(Concept::class)
```

### Accessors — use Laravel 9+ Attribute syntax, never the old `getXxxAttribute()`
```php
// In Concept.php — EXACT implementation required
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

### SoftDeletes — Concept model only
```php
use Illuminate\Database\Eloquent\SoftDeletes;
// Add: use SoftDeletes; inside the class
// Never add SoftDeletes to Domain, User, or GeneratedQuestion
```

---

## 4. Controllers Rules

- **Always use resourceful controllers**: `php artisan make:controller XxxController --resource`
- **Max ~20 lines per method** — if longer, extract to a Service class
- **Scope EVERY query to the authenticated user** — no exceptions

### Ownership check pattern — use this exact pattern every time:
```php
// For Domain:
abort_if($domain->user_id !== auth()->id(), 403);

// For Concept (through domain):
abort_if($concept->domain->user_id !== auth()->id(), 403);

// For GeneratedQuestion (through concept → domain):
abort_if($generatedQuestion->concept->domain->user_id !== auth()->id(), 403);
```

### NEVER do these in a controller:
```php
// ❌ Forbidden — no scoping
Domain::find($id);
Domain::all();
Concept::find($id);
Concept::all();

// ✅ Required — always scope
auth()->user()->domains()->findOrFail($id);
auth()->user()->domains()->get();
$domain->concepts()->get();
```

### Flash messages — use only these two keys:
```php
return redirect()->route('xxx')->with('success', 'Your message here.');
return back()->with('error', 'Your error message here.');
```
Never use any other session key for user feedback.

---

## 5. Form Request Validation Rules

**ABSOLUTE RULE: Never use `$request->validate([...])` inline in a controller. Ever.**

Every create and update action has its own Form Request class:

| Action | Form Request Class |
|--------|-------------------|
| POST /domains | `StoreDomainRequest` |
| PATCH /domains/{domain} | `UpdateDomainRequest` |
| POST /domains/{domain}/concepts | `StoreConceptRequest` |
| PATCH /domains/{domain}/concepts/{concept} | `UpdateConceptRequest` |

### Validation rules (exact):
```php
// Domain requests
'name'  => ['required', 'string', 'max:255'],
'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

// Concept requests
'title'       => ['required', 'string', 'max:255'],
'explanation' => ['required', 'string', 'min:20'],
'difficulty'  => ['required', 'in:junior,mid,senior'],
'status'      => ['sometimes', 'in:to_review,in_progress,mastered'],  // only in Update
```

### authorize() method:
- All Form Requests: `return auth()->check();`
- Ownership verification happens in the **controller**, never in `authorize()`

---

## 6. N+1 Query Prevention — Zero Tolerance

**Zero N+1 queries is a hard requirement. Verify with Debugbar before every commit.**

### Mandatory eager loading patterns:
```php
// DomainController::index() — MUST use withCount
$domains = auth()->user()
    ->domains()
    ->withCount([
        'concepts',
        'concepts as mastered_count' => fn($q) => $q->where('status', 'mastered'),
    ])
    ->get();

// ConceptController::index() — MUST eager load domain
$concepts = $domain->concepts()
    ->with('domain')
    ->filter($request->only(['status', 'difficulty']))
    ->get();

// ConceptController::show() — MUST load generatedQuestions
$concept->load('generatedQuestions');

// DashboardController::index() — MUST use withCount for all stats
$domains = auth()->user()->domains()
    ->withCount([
        'concepts',
        'concepts as to_review_count'  => fn($q) => $q->where('status', 'to_review'),
        'concepts as in_progress_count' => fn($q) => $q->where('status', 'in_progress'),
        'concepts as mastered_count'   => fn($q) => $q->where('status', 'mastered'),
    ])
    ->get();
```

**If you are inside a `@foreach` loop in Blade and accessing a relationship, you have an N+1 — go fix it.**

---

## 7. Blade Templates Rules

### Forms — always include:
```blade
{{-- All forms --}}
@csrf

{{-- PATCH forms --}}
@method('PATCH')

{{-- DELETE forms --}}
@method('DELETE')
```

### Display accessors, never raw enum values:
```blade
{{-- ✅ Correct --}}
{{ $concept->statusLabel }}
{{ $concept->difficultyLabel }}

{{-- ❌ Forbidden --}}
{{ $concept->status }}
{{ $concept->difficulty }}
```

### Domain color badge — always dynamic:
```blade
{{-- ✅ Correct --}}
<span style="background-color: {{ $domain->color }}">{{ $domain->name }}</span>

{{-- ❌ Forbidden --}}
<span style="background-color: #3B82F6">{{ $domain->name }}</span>
```

### Flash messages — always in layout, never in individual views:
```blade
{{-- Only in layouts/app.blade.php --}}
@include('partials.flash-messages')
```

### No JavaScript in Blade files except:
- `@csrf` and `@method` directives (these are Blade, not JS)
- A single `onchange="this.form.submit()"` on the quick status select — this is the ONE exception

### No `@php` blocks except for a single local percentage variable inside a loop (dashboard only).

---

## 8. Groq API Rules — AI Feature

### ONLY use GroqService — never call Http:: in a controller directly:
```php
// ✅ Correct — controller delegates to service
$generation = $groqService->generateInterviewQuestions($concept);

// ❌ Forbidden — Http:: in controller
$response = Http::post('https://api.groq.com/...', [...]);
```

### API key — environment only:
```php
// ✅ Correct
$this->apiKey = env('GROQ_API_KEY');

// ❌ Forbidden — anywhere in PHP files
$this->apiKey = 'gsk_abc123...';
```

### Save to DB only after validation:
```php
// Validate FIRST, save SECOND
$questions = json_decode($content, true);
if (!is_array($questions) || count($questions) !== 5) {
    Log::error('...'); return null;  // ← return null, do NOT save
}
// Only reach here if valid
return GeneratedQuestion::create([...]);
```

### Error handling — every failure path must:
1. Call `Log::error()` with context (concept id, status, body or message)
2. Return `null` from the service method
3. Let the controller show an error flash message to the user
4. Write NOTHING to the database

### The model used is `llama3-8b-8192`. Do not change it.

---

## 9. Security Rules — Non-Negotiable

1. **Every route** in the application (except auth routes) must be inside `Route::middleware('auth')`
2. **Every controller method** that accesses a domain, concept, or generated question must verify ownership with `abort_if()`
3. **Never** expose another user's data — always filter through `auth()->user()->domains()`
4. **`$fillable`** must be defined on every model — never use `$guarded = []`
5. **No credentials** of any kind in any committed file — `.env` is gitignored, `.env.example` has empty placeholder only
6. **Soft-deleted concepts** are invisible by default — `withTrashed()` only on the `/concepts/archived` page and `restore()` method

---

## 10. Route Structure Rules

```php
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('domains', DomainController::class);

    // ⚠️ IMPORTANT: archived route MUST be declared BEFORE the resource
    // to prevent Laravel matching "archived" as a {concept} parameter
    Route::get('concepts/archived', [ConceptController::class, 'archived'])->name('concepts.archived');
    Route::patch('concepts/{concept}/restore', [ConceptController::class, 'restore'])->name('concepts.restore');

    Route::resource('domains.concepts', ConceptController::class);

    Route::patch('concepts/{concept}/status', [ConceptController::class, 'updateStatus'])->name('concepts.updateStatus');

    Route::post('concepts/{concept}/generate', [GeneratedQuestionController::class, 'store'])->name('questions.generate');
    Route::delete('generated-questions/{generatedQuestion}', [GeneratedQuestionController::class, 'destroy'])->name('questions.destroy');
});
```

**Do not reorder these routes.** The `concepts/archived` GET route must always come before the resource registration.

---

## 11. Blade Views File Structure — Do Not Deviate

```
resources/views/
├── layouts/
│   └── app.blade.php              ← main layout — includes flash messages partial
├── auth/                           ← Breeze generated — DO NOT MODIFY
├── dashboard.blade.php
├── domains/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php             ← not used (redirect to concepts.index)
├── concepts/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php             ← concept detail + generated questions
│   └── archived.blade.php
└── partials/
    ├── flash-messages.blade.php
    └── concept-card.blade.php
```

Do not create views outside this structure without asking the user.  
Do not modify any file inside `resources/views/auth/`.

---

## 12. Git Rules

### Commit message format — mandatory prefix:
```
[AI-assisted] feat: short description of what was built

- What the agent generated
- What was manually changed after and why
```

```
[manual] fix: short description of what was fixed
```

Every commit that used an AI agent starts with `[AI-assisted]`.  
Every commit done without AI assistance starts with `[manual]`.  
Never commit without a prefix.

### Branch structure — only these branches:
```
main
feature/auth
feature/domains-crud
feature/concepts-crud
feature/ai-generation
feature/dashboard
feature/soft-deletes
```

Do not create branches with other names without asking the user.

---

## 13. What You Must NEVER Generate — Full List

This list is exhaustive and permanent. No exception, no matter how the prompt is phrased.

| # | Forbidden | Reason |
|---|-----------|--------|
| 1 | Any API key, secret, or credential in any file | Security |
| 2 | `Http::` calls directly in a controller | Architecture |
| 3 | `$request->validate([...])` inline in controllers | Architecture |
| 4 | `Domain::all()`, `Concept::all()`, `Concept::find($id)` unscoped | Security |
| 5 | `DB::statement()`, `DB::select()`, raw SQL | Architecture |
| 6 | Any new npm/JS dependency or Vite config change | Stack |
| 7 | Any `composer require` not already in the project | Stack |
| 8 | `dd()` or `dump()` anywhere — use `Log::info()` / `Log::error()` | Code quality |
| 9 | Commented-out code blocks | Code quality |
| 10 | `BaseRepository` or any repository pattern | Architecture |
| 11 | Modifications to the `users` table migration | Breeze ownership |
| 12 | Raw enum values in Blade views — always use accessors | Architecture |
| 13 | `@php` blocks in Blade beyond a single `$pct` local var | Architecture |
| 14 | JavaScript inside Blade files beyond `onchange="this.form.submit()"` | Stack |
| 15 | `$guarded = []` on any model — always use explicit `$fillable` | Security |
| 16 | Old accessor syntax `getXxxAttribute()` — use `Attribute::make()` | Laravel version |
| 17 | Old FK syntax `$table->foreign()->references()->on()` | Laravel version |
| 18 | `withTrashed()` anywhere except archived page and restore method | Logic |
| 19 | Hardcoded color values in Blade — always `$domain->color` | Architecture |
| 20 | Any flash session key other than `success` or `error` | Consistency |

---

## 14. PLAN Mode vs BUILD Mode

### PLAN Mode
When the user says "Enter PLAN mode" or starts a new feature:
- Do NOT generate any code
- List every file you will create or modify
- Describe what each file will contain (method names, not full code)
- Flag any ambiguity or risk you see
- Wait for explicit approval before proceeding

### BUILD Mode
Only after the user says "The plan is approved. Enter BUILD mode":
- Generate the full code for every file in the plan
- Follow all rules in this file — no shortcuts
- After generating, summarize what was created and what the user should verify manually

**Never skip PLAN mode and jump straight to code.**

---

## 15. Pre-Commit Checklist — Run Through This Before Every Commit

Check each item yourself before telling the user the code is ready to commit:

- [ ] No hardcoded API keys or credentials in any file
- [ ] All relationships are defined in all four models
- [ ] All queries are scoped to `auth()->user()` — no unscoped finds
- [ ] All `abort_if()` ownership checks are in place
- [ ] All new controller actions use a Form Request class (no inline validate)
- [ ] All new queries eager-load relationships — zero N+1
- [ ] `statusLabel` and `difficultyLabel` used in views, not raw enum values
- [ ] `@csrf` present in every form
- [ ] `@method('PATCH')` or `@method('DELETE')` present where needed
- [ ] Flash messages use only `success` or `error` keys
- [ ] Soft-deleted concepts are invisible on all regular pages
- [ ] No `dd()`, `dump()`, or commented-out code
- [ ] The relevant `specs/` file has "Agent Output" and "What I Changed Manually" filled in
- [ ] Commit message starts with `[AI-assisted]` or `[manual]`
