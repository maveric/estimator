<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Items') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Add the ItemSelect component here -->
                    <div class="mb-6">
                        <livewire:items.item-select />
                    </div>

                    <!-- Existing ItemList component -->
                    <livewire:items.item-list />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
