<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDomainRequest;
use App\Http\Requests\UpdateDomainRequest;
use App\Models\Domain;

class DomainController extends Controller
{
    public function index()
    {
        $domains = auth()->user()
            ->domains()
            ->withCount([
                'concepts',
                'concepts as mastered_count' => fn($q) => $q->where('status', 'mastered')
            ])
            ->get();

        return view('domains.index', compact('domains'));
    }

    public function create()
    {
        return view('domains.create');
    }

    public function store(StoreDomainRequest $request)
    {
        auth()->user()->domains()->create($request->validated());

        return redirect()->route('domains.index')->with('success', 'Domaine créé avec succès.');
    }

    public function edit(Domain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        return view('domains.edit', compact('domain'));
    }

    public function update(UpdateDomainRequest $request, Domain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        $domain->update($request->validated());

        return redirect()->route('domains.index')->with('success', 'Domaine mis à jour.');
    }

    public function destroy(Domain $domain)
    {
        abort_if($domain->user_id !== auth()->id(), 403);

        $domain->delete();

        return redirect()->route('domains.index')->with('success', 'Domaine supprimé.');
    }
}