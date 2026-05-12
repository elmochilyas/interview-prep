<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConceptRequest;
use App\Http\Requests\UpdateConceptRequest;
use App\Models\Concept;
use App\Models\Domain;
use Illuminate\Http\Request;

class ConceptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Domain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        $concepts = $domain->concepts()
            ->with('domain')
            ->filter($request->only(['status', 'difficulty']))
            ->get();

        return view('concepts.index', compact('domain', 'concepts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Domain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        return view('concepts.create', compact('domain'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

    /**
     * Display the specified resource.
     */
    public function show(Domain $domain, Concept $concept)
    {
        abort_if($domain->user_id !== auth()->id(), 403);
        $concept->load('generatedQuestions');

        return view('concepts.show', compact('domain', 'concept'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Domain $domain, Concept $concept)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        return view('concepts.edit', compact('domain', 'concept'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateConceptRequest $request, Domain $domain, Concept $concept)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        $concept->update($request->validated());

        return redirect()
            ->route('domains.concepts.index', $domain)
            ->with('success', 'Concept mis à jour.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Domain $domain, Concept $concept)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        $concept->delete();

        return redirect()
            ->route('domains.concepts.index', $domain)
            ->with('success', 'Concept archivé.');
    }

    /**
     * Quick status update.
     */
    public function updateStatus(Request $request, Concept $concept)
    {
        abort_if($concept->domain->user_id !== auth()->id(), 403);

        $request->validate([
            'status' => ['required', 'in:to_review,in_progress,mastered']
        ]);

        $concept->update(['status' => $request->status]);

        return back()->with('success', 'Statut mis à jour.');
    }

    /**
     * Display archived concepts.
     */
    public function archived()
    {
        $concepts = Concept::onlyTrashed()
            ->whereHas('domain', fn($q) => $q->where('user_id', auth()->id()))
            ->with('domain')
            ->get();

        return view('concepts.archived', compact('concepts'));
    }

    /**
     * Restore a soft-deleted concept.
     */
    public function restore($id)
    {
        $concept = Concept::withTrashed()->findOrFail($id);
        abort_if($concept->domain->user_id !== auth()->id(), 403);

        $concept->restore();

        return redirect()->route('concepts.archived')->with('success', 'Concept restauré.');
    }
}
