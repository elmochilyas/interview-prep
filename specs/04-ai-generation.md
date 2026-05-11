# Feature: AI Interview Question Generation (Groq API)

> **Spec file:** `specs/04-ai-generation.md`
> **Branch:** `feature/ai-generation`
> **Day:** 4 — Thursday 14/05/2026
> **User Stories covered:** US11, US12, US13

---

## What I want

When a user clicks "Générer des questions" on a concept detail page:
1. The app calls the Groq API (`llama3-8b-8192` model) with a prompt built from the concept's title and explanation
2. The API returns exactly 5 realistic technical interview questions in JSON
3. The 5 questions are saved to the `generated_questions` table before being shown to the user
4. The concept detail page reloads and shows the new batch of 5 questions with the generation date
5. All past generations are shown on the page (history) — most recent first
6. Each generation batch has a "Supprimer" button to delete that specific batch (not the concept)
7. If the API fails, the user sees a clear error message — nothing is saved to the DB

Everything must go through `app/Services/GroqService.php`.  
The `Http::` facade is the ONLY way to call the API — no package, no SDK.

---

## What I do NOT want

- ❌ Do NOT put `Http::` calls directly in the controller — always delegate to `GroqService`
- ❌ Do NOT store the API key anywhere other than `.env` as `GROQ_API_KEY`
- ❌ Do NOT commit `.env` or any file containing the real API key
- ❌ Do NOT save questions to the database if the API call failed or returned an error
- ❌ Do NOT display the raw JSON array — loop over the questions and render each as a `<li>`
- ❌ Do NOT add any AI/LLM SDK composer package — use raw `Http::` only
- ❌ Do NOT use `dd()` or `dump()` for debugging — use `Log::error()` in catch blocks
- ❌ Do NOT generate more or fewer than 5 questions — prompt must specify exactly 5
- ❌ Do NOT allow generating questions for another user's concept — `abort(403)` check
- ❌ Do NOT add a "regenerate all" or "clear all" button — deletion is per batch only

---

## Acceptance Criteria

- [ ] "Générer des questions" button on concept detail page triggers `POST /concepts/{concept}/generate`
- [ ] The Groq API is called with a prompt containing the concept title and explanation
- [ ] Exactly 5 questions are returned and saved to `generated_questions` as a JSON array
- [ ] The concept detail page shows the 5 questions immediately after generation
- [ ] Each batch shows its `created_at` date formatted as `d/m/Y à H:i`
- [ ] Multiple generations are shown in history (most recent first)
- [ ] Each batch has a "Supprimer" button — deletes only that batch, not the concept
- [ ] If the API is unreachable or returns an error, the user sees an error flash message
- [ ] Nothing is written to the database if the API call fails
- [ ] API key is read from `env('GROQ_API_KEY')` — never hardcoded
- [ ] No N+1 on concept show page — `generatedQuestions` is eager loaded
- [ ] A user cannot generate questions for another user's concept — 403

---

## Data Involved

### Model: `GeneratedQuestion`

```php
// app/Models/GeneratedQuestion.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedQuestion extends Model
{
    protected $fillable = ['concept_id', 'questions'];

    protected $casts = [
        'questions' => 'array',  // automatically JSON encode/decode
    ];

    public function concept(): BelongsTo
    {
        return $this->belongsTo(Concept::class);
    }
}
```

### Table: `generated_questions`
```
id            BIGINT UNSIGNED PK
concept_id    BIGINT UNSIGNED FK → concepts.id CASCADE DELETE
questions     JSON NOT NULL           -- array of exactly 5 strings
created_at, updated_at
```

---

## Routes Involved

```php
// Generate new batch of questions for a concept
Route::post('concepts/{concept}/generate', [GeneratedQuestionController::class, 'store'])
     ->name('questions.generate');

// Delete a single batch of generated questions
Route::delete('generated-questions/{generatedQuestion}', [GeneratedQuestionController::class, 'destroy'])
     ->name('questions.destroy');
```

---

## GroqService — Full Implementation

```php
// app/Services/GroqService.php
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
        $this->apiKey = env('GROQ_API_KEY');
    }

    public function generateInterviewQuestions(Concept $concept): ?GeneratedQuestion
    {
        $prompt = $this->buildPrompt($concept);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type'  => 'application/json',
            ])->timeout(30)->post($this->endpoint, [
                'model'    => $this->model,
                'messages' => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a technical interview coach. Always respond with a valid JSON array of exactly 5 strings. No explanation, no markdown, just the JSON array.',
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens'  => 800,
            ]);

            if (!$response->successful()) {
                Log::error('Groq API error', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'concept' => $concept->id,
                ]);
                return null;
            }

            $content  = $response->json('choices.0.message.content');
            $questions = json_decode($content, true);

            if (!is_array($questions) || count($questions) !== 5) {
                Log::error('Groq returned invalid format', [
                    'content' => $content,
                    'concept' => $concept->id,
                ]);
                return null;
            }

            // Save to DB only after validation
            return GeneratedQuestion::create([
                'concept_id' => $concept->id,
                'questions'  => $questions,
            ]);

        } catch (\Exception $e) {
            Log::error('Groq API exception', [
                'message' => $e->getMessage(),
                'concept' => $concept->id,
            ]);
            return null;
        }
    }

    private function buildPrompt(Concept $concept): string
    {
        return <<<EOT
        Generate exactly 5 technical interview questions about the following concept.
        
        Concept title: {$concept->title}
        Concept explanation: {$concept->explanation}
        Difficulty level: {$concept->difficulty}
        
        Return ONLY a JSON array of 5 question strings.
        Example format: ["Question 1?", "Question 2?", "Question 3?", "Question 4?", "Question 5?"]
        No explanation, no markdown code blocks, just the raw JSON array.
        EOT;
    }
}
```

---

## Controller Logic

```php
// app/Http/Controllers/GeneratedQuestionController.php
<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\GeneratedQuestion;
use App\Services\GroqService;

class GeneratedQuestionController extends Controller
{
    // Generate questions for a concept
    public function store(Concept $concept, GroqService $groqService)
    {
        abort_if($concept->domain->user_id !== auth()->id(), 403);

        $generation = $groqService->generateInterviewQuestions($concept);

        if (!$generation) {
            return back()->with('error', 'La génération a échoué. Vérifie ta connexion ou réessaie dans quelques secondes.');
        }

        return back()->with('success', '5 questions générées avec succès.');
    }

    // Delete a single batch of generated questions
    public function destroy(GeneratedQuestion $generatedQuestion)
    {
        abort_if($generatedQuestion->concept->domain->user_id !== auth()->id(), 403);

        $generatedQuestion->delete();

        return back()->with('success', 'Questions supprimées.');
    }
}
```

---

## Blade View — Concept Show Page (AI section)

Add this section to `concepts/show.blade.php`:

```blade
{{-- AI Generation section --}}
<section class="ai-section">
    <h2>Questions d'entretien générées</h2>

    {{-- Generate button --}}
    <form method="POST" action="{{ route('questions.generate', $concept) }}">
        @csrf
        <button type="submit">Générer 5 questions</button>
    </form>

    {{-- History of generations — most recent first --}}
    @forelse ($concept->generatedQuestions->sortByDesc('created_at') as $generation)
        <div class="generation-block">
            <div class="generation-header">
                <span>Généré le {{ $generation->created_at->format('d/m/Y à H:i') }}</span>

                {{-- Delete this batch --}}
                <form method="POST" action="{{ route('questions.destroy', $generation) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Supprimer</button>
                </form>
            </div>

            <ol>
                @foreach ($generation->questions as $question)
                    <li>{{ $question }}</li>
                @endforeach
            </ol>
        </div>
    @empty
        <p>Aucune question générée pour ce concept.</p>
    @endforelse
</section>
```

> `$concept->generatedQuestions` must be eager loaded in the controller with `$concept->load('generatedQuestions')`.

---

## .env Configuration

```env
# .env (never committed)
GROQ_API_KEY=your_real_key_here

# .env.example (committed — empty value)
GROQ_API_KEY=
```

---

## Error Handling Requirements

| Scenario | Expected behavior |
|----------|------------------|
| API key missing or invalid | `Log::error()` + return `null` + user sees error flash |
| Groq API timeout (>30s) | Caught by `\Exception` + same as above |
| API returns non-200 status | `Log::error()` with status + body + return `null` |
| API returns non-array JSON | `Log::error()` with content + return `null` |
| API returns array with != 5 items | `Log::error()` + return `null` — do NOT save |
| All happy paths | Questions saved, user sees success flash |

---

## Agent Output — What Was Generated
> *(Fill this in after the agent runs)*

---

## What I Changed Manually
> *(Fill this in after the agent runs — what you edited and why)*
