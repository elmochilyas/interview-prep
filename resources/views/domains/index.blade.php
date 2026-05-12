<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mes Domaines') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Mes Domaines</h1>
                <a href="{{ route('domains.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Nouveau domaine
                </a>
            </div>

            @if($domains->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 text-center">
                    <p class="text-gray-500 mb-4">Aucun domaine créé.</p>
                    <a href="{{ route('domains.create') }}" class="text-indigo-600 hover:text-indigo-800">
                        Créer votre premier domaine
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($domains as $domain)
                        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <span class="px-3 py-1 rounded-full text-white text-sm font-medium" style="background-color: {{ $domain->color }}">
                                    {{ $domain->name }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-3">
                                <span>{{ $domain->concepts_count }} concepts</span>
                                <span class="ml-2 text-green-600">{{ $domain->mastered_count }} maîtrisés</span>
                            </div>
                            <div class="flex flex-wrap gap-2 text-sm">
                                <a href="{{ route('domains.concepts.index', $domain) }}" class="text-indigo-600 hover:text-indigo-800">
                                    Voir les concepts
                                </a>
                                <a href="{{ route('domains.edit', $domain) }}" class="text-gray-600 hover:text-gray-800">
                                    Modifier
                                </a>
                                <form method="POST" action="{{ route('domains.destroy', $domain) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Supprimer ce domaine ?')">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>