<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nouveau Domaine') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">Nouveau Domaine</h1>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('domains.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom du domaine</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                        <input type="color" name="color" id="color" value="{{ old('color', '#3B82F6') }}"
                            class="h-10 w-full rounded-md border-gray-300 shadow-sm" required>
                        @error('color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Créer
                        </button>
                        <a href="{{ route('domains.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>