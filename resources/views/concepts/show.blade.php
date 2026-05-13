<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $concept->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('domains.concepts.index', $domain) }}" class="text-gray-600 hover:text-gray-900">
                    &larr; Retour à la liste
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <h1 class="text-3xl font-bold">{{ $concept->title }}</h1>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                            {{ $concept->difficultyLabel }}
                        </span>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">
                            {{ $concept->statusLabel }}
                        </span>
                    </div>
                </div>

                <div class="prose max-w-none text-gray-700 mb-6">
                    {!! nl2br(e($concept->explanation)) !!}
                </div>

                <div class="flex gap-3 pt-6 border-t border-gray-100">
                    <a href="{{ route('domains.concepts.edit', [$domain, $concept]) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Modifier le concept
                    </a>
                </div>
            </div>

            <!-- AI Generation Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-xl font-semibold mb-4">Questions d'entretien générées</h3>

                <!-- Generate Button -->
                <form method="POST" action="{{ route('questions.generate', $concept) }}" class="mb-6">
                    @csrf
                    <button type="submit" style="background-color: #16A34A; color: white; padding: 12px 24px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">
                        Générer 5 questions
                    </button>
                </form>

                <!-- History of generations - most recent first -->
                @forelse ($concept->generatedQuestions->sortByDesc('created_at') as $generation)
                    <div class="border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-sm text-gray-500">
                                Généré le {{ $generation->created_at->format('d/m/Y à H:i') }}
                            </span>
                            <form method="POST" action="{{ route('questions.destroy', $generation) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                        <ol class="list-decimal list-inside space-y-2">
                            @foreach ($generation->questions as $question)
                                <li class="text-gray-700">{{ $question }}</li>
                            @endforeach
                        </ol>
                    </div>
                @empty
                    <p class="text-gray-500">Aucune question générée pour ce concept.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>