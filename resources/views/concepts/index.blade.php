<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Concepts : ') }} {{ $domain->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-4">
                    <a href="{{ route('domains.index') }}" class="text-gray-600 hover:text-gray-900">
                        &larr; Retour
                    </a>
                    <h1 class="text-2xl font-bold">{{ $domain->name }}</h1>
                </div>
                <a href="{{ route('domains.concepts.create', $domain) }}" style="background-color: #4F46E5; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none;">
                    Nouveau concept
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <form method="GET" action="{{ route('domains.concepts.index', $domain) }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                        <select name="status" id="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tous les statuts</option>
                            <option value="to_review" @selected(request('status') === 'to_review')>À revoir</option>
                            <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                            <option value="mastered" @selected(request('status') === 'mastered')>Maîtrisé</option>
                        </select>
                    </div>
                    <div>
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">Difficulté</label>
                        <select name="difficulty" id="difficulty" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Toutes les difficultés</option>
                            <option value="junior" @selected(request('difficulty') === 'junior')>Junior</option>
                            <option value="mid" @selected(request('difficulty') === 'mid')>Mid</option>
                            <option value="senior" @selected(request('difficulty') === 'senior')>Senior</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-700">
                            Filtrer
                        </button>
                        <a href="{{ route('domains.concepts.index', $domain) }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            @if($concepts->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 text-center">
                    <p class="text-gray-500">Aucun concept trouvé.</p>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concept</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulté</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($concepts as $concept)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('domains.concepts.show', [$domain, $concept]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                            {{ $concept->title }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ $concept->difficultyLabel }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form method="POST" action="{{ route('concepts.updateStatus', $concept) }}">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="to_review" @selected($concept->status === 'to_review')>À revoir</option>
                                                <option value="in_progress" @selected($concept->status === 'in_progress')>En cours</option>
                                                <option value="mastered" @selected($concept->status === 'mastered')>Maîtrisé</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('domains.concepts.edit', [$domain, $concept]) }}" class="text-gray-600 hover:text-gray-900">Modifier</a>
                                            <form method="POST" action="{{ route('domains.concepts.destroy', [$domain, $concept]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Archiver ce concept ?')">Archiver</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>