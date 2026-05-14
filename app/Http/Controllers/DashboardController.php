<?php

namespace App\Http\Controllers;

use App\Models\GeneratedQuestion;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $domains = $user->domains()
            ->withCount([
                'concepts',
                'concepts as to_review_count'   => fn($q) => $q->where('status', 'to_review'),
                'concepts as in_progress_count' => fn($q) => $q->where('status', 'in_progress'),
                'concepts as mastered_count'    => fn($q) => $q->where('status', 'mastered'),
            ])
            ->get();

        $totalConcepts    = $domains->sum('concepts_count');
        $totalMastered    = $domains->sum('mastered_count');
        $totalInProgress  = $domains->sum('in_progress_count');
        $totalToReview    = $domains->sum('to_review_count');

        $masteryPercentage = $totalConcepts > 0
            ? round(($totalMastered / $totalConcepts) * 100)
            : 0;

        $bestDomain = $domains->sortByDesc('mastered_count')->first();
        $mostToReviewDomain = $domains->sortByDesc('to_review_count')->first();

        $totalQuestions = GeneratedQuestion::whereHas(
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