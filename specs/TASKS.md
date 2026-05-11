# TASKS.md — InterviewPrep Project
# All Phases · All Tasks · Time Benchmarks

> **Project:** InterviewPrep — Laravel PHP Application
> **Duration:** 5 days · Monday 11/05/2026 → Friday 15/05/2026 at 13h00
> **Total estimated work time:** ~24 hours
> **Total tasks:** 43

---

## Legend

| Symbol | Meaning |
|--------|---------|
| 🔴 Critical | Blocks everything else — do this first |
| 🟠 High | Important — do today |
| 🔵 Normal | Standard task |
| 🟢 Low | Bonus / polish |
| ⏱ | Estimated duration |
| 🔗 | Depends on (must be done first) |

---

## Overview — Phases & Time Budget

| Phase | Day | Tasks | Est. Time |
|-------|-----|-------|-----------|
| P0 — Project Setup | Day 1 | 7 tasks | ~2h 30m |
| P1 — Authentication | Day 2 | 5 tasks | ~1h 30m |
| P2 — Domains CRUD | Day 2 | 8 tasks | ~3h 00m |
| P3 — Concepts CRUD | Day 3 | 12 tasks | ~5h 00m |
| P4 — AI Generation | Day 4 | 9 tasks | ~4h 00m |
| P5 — Dashboard Bonus | Day 5 | 4 tasks | ~2h 30m |
| P6 — Deliverables & Polish | Day 5 | 4 tasks | ~3h 30m |
| **TOTAL** | | **49 tasks** | **~22h** |

> ⚠️ Day 1 is the most critical day — MCD/MLD must be validated before any code is written.

---

---

## PHASE 0 — Project Setup
> **Day:** Monday 11/05/2026
> **Estimated total:** ~2h 30m
> **Goal:** Working Laravel app running locally, all migrations done, repo and Jira ready.

---

### SETUP-1 — Create GitHub repository and commit AGENTS.md as first commit
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** Nothing — this is the very first action
- **Steps:**
  1. Create a new public GitHub repository named `interviewprep`
  2. Clone it locally
  3. Add `AGENTS.md` at the root
  4. `git add AGENTS.md && git commit -m "[manual] chore: initial commit — add AGENTS.md"`
  5. `git push origin main`
- **Done when:** AGENTS.md is visible on GitHub as commit #1

---

### SETUP-2 — Create and share Jira board with all user stories as tickets
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** Nothing
- **Steps:**
  1. Create a new Jira project (Scrum board)
  2. Create one ticket per user story (US1 → US13 + bonus)
  3. Share the board with `abderahmane.merradou@gmail.com`
  4. Move tickets to "In Progress" as you work on them
- **Done when:** Board is shared, all 13+ US visible as tickets before Monday 13h00

---

### SETUP-3 — Install Laravel 13 and configure .env
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** SETUP-1
- **Commands:**
  ```bash
  composer create-project laravel/laravel interviewprep
  cd interviewprep
  ```
- **Configure `.env`:**
  ```
  DB_DATABASE=interviewprep
  DB_USERNAME=root
  DB_PASSWORD=
  ```
- **Add to `.env.example`:**
  ```
  GROQ_API_KEY=
  ```
- **Done when:** `php artisan --version` returns Laravel 13.x with no errors

---

### SETUP-4 — Install Laravel Breeze and build assets
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** SETUP-3
- **Commands:**
  ```bash
  composer require laravel/breeze --dev
  php artisan breeze:install blade
  npm install && npm run build
  ```
- **Done when:** `/login` and `/register` routes exist and render correctly

---

### SETUP-5 — Install Laravel Debugbar (dev only)
- 🟠 **Priority:** High
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** SETUP-3
- **Command:**
  ```bash
  composer require barryvdh/laravel-debugbar --dev
  ```
- **Done when:** Debugbar toolbar appears at the bottom of the browser

---

### SETUP-6 — Create all 4 models with migrations
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 40 min
- 🔗 **Depends on:** SETUP-3
- **Commands:**
  ```bash
  php artisan make:model Domain -m
  php artisan make:model Concept -m
  php artisan make:model GeneratedQuestion -m
  ```
- Fill each migration with the exact schema from `AGENTS.md` Section 3
- Use `$table->foreignId()->constrained()->cascadeOnDelete()` — never old FK syntax
- Add `$table->softDeletes()` on `concepts` table ONLY
- **Command to run migrations:**
  ```bash
  php artisan migrate
  ```
- **Done when:** All 4 tables exist in MySQL with correct columns — verify with a DB client

---

### SETUP-7 — Create base layout and flash messages partial
- 🟠 **Priority:** High
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** SETUP-4
- **Files to create:**
  - `resources/views/layouts/app.blade.php` — main layout with nav bar
  - `resources/views/partials/flash-messages.blade.php` — shows `session('success')` and `session('error')`
- **Done when:** Nav bar renders on all pages, flash messages show on redirect

---

### SETUP-8 — Create all feature branches on GitHub
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** SETUP-1
- **Commands:**
  ```bash
  git checkout -b feature/auth && git push origin feature/auth
  git checkout main && git checkout -b feature/domains-crud && git push origin feature/domains-crud
  git checkout main && git checkout -b feature/concepts-crud && git push origin feature/concepts-crud
  git checkout main && git checkout -b feature/ai-generation && git push origin feature/ai-generation
  git checkout main && git checkout -b feature/dashboard && git push origin feature/dashboard
  git checkout main && git checkout -b feature/soft-deletes && git push origin feature/soft-deletes
  ```
- **Done when:** All 6 feature branches visible on GitHub

---

### SETUP-9 — Draw and get MCD + MLD validated by trainer
- 🔴 **Priority:** Critical — NO CODE before this is validated
- ⏱ **Estimate:** 30 min to draw, then await validation
- 🔗 **Depends on:** Nothing — can be done in parallel with repo setup
- **MCD:** `User ──< Domain ──< Concept ──< GeneratedQuestion`
- **MLD:** 4 tables with all columns, FK arrows, data types
- **Done when:** Trainer validates both diagrams — only then proceed to P1

---

---

## PHASE 1 — Authentication
> **Day:** Tuesday 12/05/2026 (morning)
> **Branch:** `feature/auth`
> **Estimated total:** ~1h 30m
> **Spec file:** `specs/01-auth.md`

---

### AUTH-1 — Add User → hasMany → Domain relationship to User model
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** SETUP-6
- **Done when:** `auth()->user()->domains()` works without error

---

### AUTH-2 — Create DashboardController with index() method
- 🟠 **Priority:** High
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** AUTH-1
- **Command:** `php artisan make:controller DashboardController`
- **Done when:** `GET /dashboard` returns 200 for logged-in users

---

### AUTH-3 — Set up full auth middleware route group in web.php
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** AUTH-2
- Define the complete `Route::middleware('auth')->group()` skeleton with all route placeholders
- **Done when:** All planned routes are registered (verify with `php artisan route:list`)

---

### AUTH-4 — Build nav bar with user name and logout button
- 🟠 **Priority:** High
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** AUTH-3, SETUP-7
- Nav shows: app name → `/dashboard`, "Mes Domaines" → `/domains`, user name, logout form
- **Done when:** Nav renders correctly after login, logout button works

---

### AUTH-5 — Test full auth flow end to end
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** AUTH-4
- Test: register → lands on dashboard ✓, logout → lands on `/` ✓, login → dashboard ✓, visit `/dashboard` unauthenticated → redirects to `/login` ✓
- **Done when:** All 4 scenarios pass manually

---

---

## PHASE 2 — Domains CRUD
> **Day:** Tuesday 12/05/2026 (afternoon)
> **Branch:** `feature/domains-crud`
> **Estimated total:** ~3h 00m
> **Spec file:** `specs/02-domains-crud.md`

---

### DOM-1 — Fill Domain model ($fillable, relationships)
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** SETUP-6, AUTH-1
- Add `$fillable`, `user()` BelongsTo, `concepts()` HasMany
- **Done when:** Model has all relationships and fillable fields defined

---

### DOM-2 — Generate DomainController (resourceful)
- 🟠 **Priority:** High
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** DOM-1
- **Command:** `php artisan make:controller DomainController --resource`
- **Done when:** Controller file exists with all 7 resource methods stubbed

---

### DOM-3 — Create StoreDomainRequest and UpdateDomainRequest
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** DOM-2
- **Commands:**
  ```bash
  php artisan make:request StoreDomainRequest
  php artisan make:request UpdateDomainRequest
  ```
- Validation rules: `name` required string max:255, `color` required regex `^#[0-9A-Fa-f]{6}$`
- **Done when:** Both Form Request classes exist with correct rules — NO inline `validate()` in controller

---

### DOM-4 — Implement index() with N+1-safe eager loading
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** DOM-3
- Use `withCount(['concepts', 'concepts as mastered_count' => ...])` — see `specs/02-domains-crud.md`
- **Done when:** Debugbar shows 1–2 queries max on the domains page, not N+1

---

### DOM-5 — Implement store(), edit(), update(), destroy() with ownership checks
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 40 min
- 🔗 **Depends on:** DOM-4
- Every method must have `abort_if($domain->user_id !== auth()->id(), 403)`
- `store()` must assign `user_id` from `auth()->id()` — never from request input
- **Done when:** Create, edit, update, delete all work correctly with ownership enforced

---

### DOM-6 — Build all 3 Blade views (index, create, edit)
- 🟠 **Priority:** High
- ⏱ **Estimate:** 45 min
- 🔗 **Depends on:** DOM-5
- `index.blade.php`: domain cards with color badge, concepts_count, mastered_count, edit/delete buttons
- `create.blade.php`: name input + `<input type="color">` + validation error display
- `edit.blade.php`: pre-filled form with `old()` fallback, `@method('PATCH')`
- Delete button: form with `@method('DELETE')` + `@csrf` + `onclick confirm()`
- **Done when:** All 3 pages render and function without layout errors

---

### DOM-7 — Verify zero N+1 on domains index with Debugbar
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** DOM-6
- Open the domains list with 3+ domains — Debugbar must show ≤2 queries
- **Done when:** Debugbar confirms zero N+1 — only then commit

---

### DOM-8 — Write specs/02-domains-crud.md — fill Agent Output + What I Changed Manually
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** DOM-7
- Fill in the "Agent Output" and "What I Changed Manually" sections of the spec
- **Done when:** Spec file is complete and committed alongside the feature code

---

---

## PHASE 3 — Concepts CRUD
> **Day:** Wednesday 13/05/2026
> **Branch:** `feature/concepts-crud`
> **Estimated total:** ~5h 00m
> **Spec file:** `specs/03-concepts-crud.md`

---

### CON-1 — Fill Concept model ($fillable, SoftDeletes, relationships, accessors, scopeFilter)
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** SETUP-6
- Add `use SoftDeletes`, `$fillable`, `domain()` BelongsTo, `generatedQuestions()` HasMany
- Add `statusLabel()` and `difficultyLabel()` accessors using `Attribute::make()` syntax
- Add `scopeFilter()` local scope for status + difficulty filtering
- **Done when:** All accessors return correct labels, `Concept::withTrashed()` works

---

### CON-2 — Generate ConceptController (resourceful) + register all routes
- 🟠 **Priority:** High
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** CON-1
- **Command:** `php artisan make:controller ConceptController --resource`
- Register nested resource: `Route::resource('domains.concepts', ConceptController::class)`
- Register `concepts/archived` GET route BEFORE the resource (critical ordering)
- Register `PATCH /concepts/{concept}/status` and `PATCH /concepts/{concept}/restore`
- **Done when:** `php artisan route:list` shows all concept routes with correct names

---

### CON-3 — Create StoreConceptRequest and UpdateConceptRequest
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** CON-2
- Store rules: `title` required max:255, `explanation` required min:20, `difficulty` in:junior,mid,senior
- Update rules: same + `status` in:to_review,in_progress,mastered
- **Done when:** Both classes exist, no inline `validate()` anywhere in controller

---

### CON-4 — Implement index() with filter + N+1-safe eager loading
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 25 min
- 🔗 **Depends on:** CON-3
- Use `$domain->concepts()->with('domain')->filter($request->only(['status','difficulty']))->get()`
- Ownership check: `abort_if($domain->user_id !== auth()->id(), 403)`
- **Done when:** Filter by status works, filter by difficulty works, both combined work

---

### CON-5 — Implement store() — force status = to_review on creation
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** CON-4
- Merge validated data with `['status' => 'to_review']` — never trust status from create request
- **Done when:** New concept always starts as `to_review` regardless of what is submitted

---

### CON-6 — Implement show() with eager loaded generatedQuestions
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** CON-5
- `$concept->load('generatedQuestions')` — not lazy load
- **Done when:** Concept detail page renders with empty questions section (AI not built yet)

---

### CON-7 — Implement edit(), update(), destroy() (soft delete) with ownership checks
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** CON-6
- `destroy()` calls `$concept->delete()` which sets `deleted_at` — does NOT permanently remove
- All methods: `abort_if($domain->user_id !== auth()->id(), 403)`
- **Done when:** Delete hides the concept from list, concept still exists in DB with `deleted_at` set

---

### CON-8 — Implement updateStatus() — quick status change from list
- 🟠 **Priority:** High
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** CON-7
- Validate `status` in:to_review,in_progress,mastered inline (exception to Form Request rule — single field PATCH)
- Ownership: `abort_if($concept->domain->user_id !== auth()->id(), 403)`
- **Done when:** Changing the select on the list row updates status without page navigation

---

### CON-9 — Implement archived() and restore()
- 🟠 **Priority:** High
- ⏱ **Estimate:** 25 min
- 🔗 **Depends on:** CON-7
- `archived()`: use `Concept::onlyTrashed()->whereHas('domain', fn($q) => $q->where('user_id', auth()->id()))`
- `restore()`: use `Concept::withTrashed()->findOrFail($id)` then `$concept->restore()`
- **Done when:** `/concepts/archived` lists soft-deleted concepts, restore button brings them back

---

### CON-10 — Build all Blade views (index, create, edit, show, archived)
- 🟠 **Priority:** High
- ⏱ **Estimate:** 60 min
- 🔗 **Depends on:** CON-9
- `index.blade.php`: filter form, table with statusLabel + difficultyLabel, quick-status select, edit/delete buttons
- `create.blade.php`: title, textarea explanation, difficulty select, submit button
- `edit.blade.php`: pre-filled form with status select, `@method('PATCH')`
- `show.blade.php`: concept details + empty AI section placeholder
- `archived.blade.php`: list with domain name + restore button per concept
- **Never** display raw `$concept->status` or `$concept->difficulty` — always use accessors
- **Done when:** All 5 views render and function correctly

---

### CON-11 — Verify zero N+1 on all concept pages with Debugbar
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** CON-10
- Check: concepts index (with 5+ concepts), concept show, archived page
- Debugbar must show ≤3 queries on each page
- **Done when:** All pages confirmed N+1-free — only then commit

---

### CON-12 — Write specs/03-concepts-crud.md — fill Agent Output + What I Changed Manually
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** CON-11
- **Done when:** Spec file sections filled in and committed

---

---

## PHASE 4 — AI Interview Question Generation
> **Day:** Thursday 14/05/2026
> **Branch:** `feature/ai-generation`
> **Estimated total:** ~4h 00m
> **Spec file:** `specs/04-ai-generation.md`

---

### AI-1 — Fill GeneratedQuestion model ($fillable, $casts, relationship)
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** SETUP-6
- `$fillable = ['concept_id', 'questions']`
- `$casts = ['questions' => 'array']` — mandatory for JSON auto-decode
- `concept()` BelongsTo relationship
- **Done when:** `$generation->questions` returns a PHP array, not a raw JSON string

---

### AI-2 — Add Concept → hasMany → GeneratedQuestion relationship
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** AI-1, CON-1
- Add `generatedQuestions()` HasMany to `Concept.php` if not already present
- **Done when:** `$concept->generatedQuestions` returns a collection

---

### AI-3 — Add GROQ_API_KEY to .env and config/services.php
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** SETUP-3
- Add to `.env`: `GROQ_API_KEY=your_real_key_here`
- Add to `.env.example`: `GROQ_API_KEY=` (empty — no key)
- Add to `config/services.php`:
  ```php
  'groq' => ['key' => env('GROQ_API_KEY')],
  ```
- **Done when:** `config('services.groq.key')` returns the key, `.env` is NOT committed

---

### AI-4 — Create GroqService with generateInterviewQuestions() method
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 45 min
- 🔗 **Depends on:** AI-1, AI-3
- Create `app/Services/GroqService.php`
- Use `Http::` facade — no external package
- Model: `llama3-8b-8192`
- Endpoint: `https://api.groq.com/openai/v1/chat/completions`
- Timeout: 30 seconds
- Prompt must specify: exactly 5 questions, JSON array only, no markdown, no explanation
- Validate response: must be array of exactly 5 strings — return `null` if not
- Save to DB ONLY after validation passes
- All failures: `Log::error()` with context + return `null`
- **Done when:** Calling the service returns a saved `GeneratedQuestion` with 5 questions in DB

---

### AI-5 — Create GeneratedQuestionController with store() and destroy()
- 🟠 **Priority:** High
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** AI-4
- `php artisan make:controller GeneratedQuestionController`
- `store()`: ownership check → call GroqService → flash success or error
- `destroy()`: ownership check via `$generatedQuestion->concept->domain->user_id` → delete batch only
- **Done when:** Both methods work, no Http:: calls in the controller

---

### AI-6 — Register AI routes in web.php
- 🟠 **Priority:** High
- ⏱ **Estimate:** 10 min
- 🔗 **Depends on:** AI-5
- `POST /concepts/{concept}/generate` → `questions.generate`
- `DELETE /generated-questions/{generatedQuestion}` → `questions.destroy`
- **Done when:** Both routes appear in `php artisan route:list`

---

### AI-7 — Update concepts/show.blade.php with AI generation section
- 🟠 **Priority:** High
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** AI-6, CON-10
- "Générer 5 questions" POST form button
- Loop over `$concept->generatedQuestions` sorted by `created_at` descending
- Each batch: generation date formatted as `d/m/Y à H:i`, ordered list of 5 questions
- Delete batch button: form with `@method('DELETE')` + `@csrf`
- Empty state: "Aucune question générée pour ce concept."
- **Done when:** Generate button creates questions, history shows all batches, delete removes a batch

---

### AI-8 — Test all error scenarios for the Groq API
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** AI-7
- Test with invalid API key → error flash shown, nothing saved to DB
- Test with empty GROQ_API_KEY → error flash shown
- Check `storage/logs/laravel.log` — all errors must be logged with context
- **Done when:** All failure paths show user-friendly error messages and log correctly

---

### AI-9 — Write specs/04-ai-generation.md — fill Agent Output + What I Changed Manually
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** AI-8
- **Done when:** Spec file fully filled in and committed on `feature/ai-generation`

---

---

## PHASE 5 — Dashboard & Bonus Features
> **Day:** Friday 15/05/2026 (morning)
> **Branch:** `feature/dashboard`
> **Estimated total:** ~2h 30m
> **Spec file:** `specs/05-dashboard-bonus.md`

---

### BON-1 — Build DashboardController with all progression stats
- 🟠 **Priority:** High
- ⏱ **Estimate:** 45 min
- 🔗 **Depends on:** P2, P3, P4 all complete
- Compute in controller (not Blade): total concepts, by-status counts, mastery %, best domain, most-to-review domain, total questions generated
- Use `withCount()` — single query for all domain stats, zero N+1
- Pass all variables pre-computed to the view
- **Done when:** Dashboard shows correct numbers — verified by creating test data

---

### BON-2 — Build dashboard.blade.php with stats, progress bars, and domain list
- 🟠 **Priority:** High
- ⏱ **Estimate:** 45 min
- 🔗 **Depends on:** BON-1
- 4 summary stat cards (total, mastered, in progress, to review)
- Global progress bar with mastery percentage
- Best domain + most-to-review domain highlights
- Per-domain progress list with inline progress bars
- Empty state if no domains exist yet (with "Create first domain" CTA)
- **Done when:** Dashboard renders all sections correctly with both data and empty states

---

### BON-3 — Verify the combined filter (status + difficulty) works on concepts list
- 🟢 **Priority:** Low
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** CON-4
- Test URL: `/domains/{id}/concepts?status=mastered&difficulty=senior`
- Verify `scopeFilter` applies both conditions simultaneously
- **Done when:** Combined filter returns only concepts matching both criteria

---

### BON-4 — Write specs/05-dashboard-bonus.md — fill Agent Output + What I Changed Manually
- 🟠 **Priority:** High
- ⏱ **Estimate:** 15 min
- 🔗 **Depends on:** BON-2
- **Done when:** Spec file fully filled in and committed

---

---

## PHASE 6 — Deliverables, Polish & Presentation
> **Day:** Friday 15/05/2026 (final push before 13h00)
> **Estimated total:** ~3h 30m

---

### DEL-1 — Write README.md
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** All features working
- **Must include:**
  - Project description (what InterviewPrep does)
  - Tech stack (Laravel 13, PHP 8.4, MySQL 8, Groq API, Tailwind/CSS)
  - Local setup instructions (clone → .env → migrate → serve)
  - Environment variables needed (`GROQ_API_KEY`)
  - Feature list with US numbers
  - Link to the Jira board
- **Done when:** README.md committed to `main`, readable on GitHub

---

### DEL-2 — Final Git audit — minimum 15 commits with correct format
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 20 min
- 🔗 **Depends on:** All features committed
- Count total commits: `git log --oneline | wc -l`
- Every AI-assisted commit starts with `[AI-assisted]`
- Every manual commit starts with `[manual]`
- All feature branches merged (or at least pushed) to GitHub
- **Done when:** `git log` shows ≥15 commits with proper prefixes

---

### DEL-3 — Final pre-demo checklist — run through RULES.md checklist
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 30 min
- 🔗 **Depends on:** DEL-2
- Go through every item in `RULES.md` Section 15
- Specifically verify:
  - [ ] No `.env` committed
  - [ ] No `dd()` or `dump()` anywhere (`grep -r "dd(" app/` should return nothing)
  - [ ] No `Domain::all()` or `Concept::all()` unscoped (`grep -r "::all()" app/`)
  - [ ] All forms have `@csrf`
  - [ ] Debugbar shows ≤3 queries on every main page
  - [ ] All 3 `specs/` files have "Agent Output" and "What I Changed Manually" filled in
- **Done when:** Every checklist item passes — no exceptions

---

### DEL-4 — Build the presentation (10 slides, mandatory structure)
- 🔴 **Priority:** Critical
- ⏱ **Estimate:** 90 min
- 🔗 **Depends on:** All features working
- **Mandatory slide order:**
  1. Titre & Auteur
  2. Contexte & Problème
  3. MCD
  4. MLD
  5. Stack & Outils (Laravel + coding agent + Groq API)
  6. Workflow AI-Assisted *(real screenshots of specs/ files and commits)*
  7. Feature AI *(how the Http:: call works)*
  8. Démo live
  9. Ce que l'agent a bien fait / Ce qu'il a mal fait ou hallucin é
  10. Conclusion
- **Rules:** max 30 words/slide · min 1 visual/slide · font ≥ 24px · slides numbered
- **Done when:** 10 slides complete, all rules respected, ready for live demo

---

---

## Daily Time Budget Summary

| Day | Date | Phase(s) | Tasks | Budget |
|-----|------|----------|-------|--------|
| Day 1 | Mon 11/05 | P0 Setup + MCD/MLD | SETUP-1 → SETUP-9 | 8h |
| Day 2 | Tue 12/05 | P1 Auth + P2 Domains | AUTH-1→5 + DOM-1→8 | 8h |
| Day 3 | Wed 13/05 | P3 Concepts CRUD | CON-1 → CON-12 | 8h |
| Day 4 | Thu 14/05 | P4 AI Generation | AI-1 → AI-9 | 8h |
| Day 5 | Fri 15/05 | P5 Dashboard + P6 Deliverables | BON-1→4 + DEL-1→4 | Until 13h00 |

---

## Critical Path — Tasks That Block Everything Else

If any of these are late, the whole project is late:

```
SETUP-1 (repo)
  └─ SETUP-3 (Laravel install)
       └─ SETUP-6 (migrations) ─────────┐
            └─ AUTH-1 (relationships)   │
                 └─ AUTH-3 (routes)     │
                      └─ DOM → CON → AI ┘
SETUP-9 (MCD/MLD validated) ── NO CODE before this
DEL-4 (presentation) ── needs everything working
```

---

## Pre-Commit Checklist (run before every single commit)

- [ ] No hardcoded API keys or credentials in any file
- [ ] All queries scoped to `auth()->user()` — no unscoped `::all()` or `::find()`
- [ ] All `abort_if()` ownership checks are in every controller method
- [ ] All create/update actions use a Form Request class — no inline `validate()`
- [ ] All new relationships are eager-loaded — zero N+1 (checked with Debugbar)
- [ ] `statusLabel` and `difficultyLabel` used in Blade — never raw enum values
- [ ] `@csrf` in every form, `@method('PATCH'/'DELETE')` where needed
- [ ] Flash messages use only `success` or `error` session keys
- [ ] No `dd()`, `dump()`, or commented-out code blocks
- [ ] Commit message starts with `[AI-assisted]` or `[manual]`
- [ ] Relevant `specs/` file has "Agent Output" and "What I Changed Manually" filled in
