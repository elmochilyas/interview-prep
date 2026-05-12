<?php

namespace App\Http\Controllers;

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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
