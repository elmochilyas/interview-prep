<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nouveau Concept - ') }} {{ $domain->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('domains.concepts.index', $domain) }}" class="text-gray-600 hover:text-gray-900">
                    &larr; Retour à la liste
                </a>
            </div>

            <h1 class="text-2xl font-bold mb-6">Nouveau Concept</h1>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('domains.concepts.store', $domain) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">Difficulté</label>
                        <select name="difficulty" id="difficulty" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="junior" @selected(old('difficulty') === 'junior')>Junior</option>
                            <option value="mid" @selected(old('difficulty') === 'mid')>Mid</option>
                            <option value="senior" @selected(old('difficulty') === 'senior')>Senior</option>
                        </select>
                        @error('difficulty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="explanation" class="block text-sm font-medium text-gray-700 mb-1">Explication</label>
                        <textarea name="explanation" id="explanation" rows="6"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>{{ old('explanation') }}</textarea>
                        @error('explanation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit" style="background-color: #4F46E5; color: white; padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer;">
                            Créer
                        </button>
                        <a href="{{ route('domains.concepts.index', $domain) }}" style="background-color: #9CA3AF; color: #374151; padding: 10px 20px; border-radius: 6px; text-decoration: none;">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>