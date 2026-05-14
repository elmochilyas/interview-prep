<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Section 1: Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-3xl font-bold text-gray-800">{{ $totalConcepts }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total concepts</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <div class="text-3xl font-bold text-green-600">{{ $totalMastered }}</div>
                    <div class="text-sm text-gray-500 mt-1">Maîtrisés</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="text-3xl font-bold text-yellow-600">{{ $totalInProgress }}</div>
                    <div class="text-sm text-gray-500 mt-1">En cours</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-red-500">
                    <div class="text-3xl font-bold text-red-600">{{ $totalToReview }}</div>
                    <div class="text-sm text-gray-500 mt-1">À revoir</div>
                </div>
            </div>

            {{-- Section 2: Global Progress Bar --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-lg mb-3">
                    Progression globale : <strong>{{ $masteryPercentage }}%</strong> maîtrisé
                </p>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-green-500 h-4 rounded-full transition-all duration-300" style="width: {{ $masteryPercentage }}%"></div>
                </div>
            </div>

            {{-- Section 3: Highlights --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-lg font-semibold mb-2">🏆 Meilleur domaine</div>
                    <div class="text-gray-700">
                        @if($bestDomain)
                            <span class="inline-block px-2 py-1 rounded text-white text-sm" style="background-color: {{ $bestDomain->color }}">
                                {{ $bestDomain->name }}
                            </span>
                            <span class="text-sm text-gray-500">({{ $bestDomain->mastered_count }} maîtrisés)</span>
                        @else
                            <span class="text-gray-400">Aucun domaine</span>
                        @endif
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-lg font-semibold mb-2">🔴 Plus à revoir</div>
                    <div class="text-gray-700">
                        @if($mostToReviewDomain)
                            <span class="inline-block px-2 py-1 rounded text-white text-sm" style="background-color: {{ $mostToReviewDomain->color }}">
                                {{ $mostToReviewDomain->name }}
                            </span>
                            <span class="text-sm text-gray-500">({{ $mostToReviewDomain->to_review_count }} à revoir)</span>
                        @else
                            <span class="text-gray-400">Aucun domaine</span>
                        @endif
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-lg font-semibold mb-2">🤖 Questions générées</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $totalQuestions }}</div>
                </div>
            </div>

            {{-- Section 4: Per-Domain Progress List --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Progression par domaine</h3>

                @forelse($domains as $domain)
                    <div class="mb-6 last:mb-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-2 py-1 rounded text-white text-sm" style="background-color: {{ $domain->color }}">
                                    {{ $domain->name }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ $domain->mastered_count }} / {{ $domain->concepts_count }} maîtrisés
                                </span>
                            </div>
                            <a href="{{ route('domains.concepts.index', $domain) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                Voir les concepts →
                            </a>
                        </div>
                        @php
                            $pct = $domain->concepts_count > 0
                                ? round(($domain->mastered_count / $domain->concepts_count) * 100)
                                : 0;
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-gray-500 mb-4">Aucun domaine créé.</p>
                        <a href="{{ route('domains.create') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Créer votre premier domaine
                        </a>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>