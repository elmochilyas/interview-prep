<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Models\GeneratedQuestion;
use App\Services\GroqService;

class GeneratedQuestionController extends Controller
{
    public function store(Concept $concept, GroqService $groqService)
    {
        abort_if($concept->domain->user_id !== auth()->id(), 403);

        $generation = $groqService->generateInterviewQuestions($concept);

        if (!$generation) {
            return back()->with('error', 'La génération a échoué. Vérifie ta connexion ou réessaie dans quelques secondes.');
        }

        return back()->with('success', '5 questions générées avec succès.');
    }

    public function destroy(GeneratedQuestion $generatedQuestion)
    {
        abort_if($generatedQuestion->concept->domain->user_id !== auth()->id(), 403);

        $generatedQuestion->delete();

        return back()->with('success', 'Questions supprimées.');
    }
}