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

            <!-- AI Questions Section Placeholder -->
            <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-12 text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Questions d'entretien</h3>
                <p class="text-gray-500 mb-6">Préparez votre entretien en générant des questions basées sur ce concept.</p>
                <button disabled class="px-4 py-2 bg-gray-300 text-white rounded-md cursor-not-allowed">
                    Générer des questions (Bientôt disponible)
                </button>
            </div>
        </div>
    </div>
</x-app-layout>