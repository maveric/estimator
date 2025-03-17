<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Labor Rate
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 lg:p-8">
                    <form wire:submit="save">
                        <div class="grid grid-cols-6 gap-6">
                            <!-- Name -->
                            <div class="col-span-6 sm:col-span-4">
                                <x-label for="name" value="Name" />
                                <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                                <x-input-error for="name" class="mt-2" />
                            </div>

                            <!-- Cost Rate -->
                            <div class="col-span-6 sm:col-span-4">
                                <x-label for="cost_rate" value="Cost Rate (per hour)" />
                                <x-input id="cost_rate" type="number" min="0" step="0.01" class="mt-1 block w-full" wire:model="cost_rate" />
                                <x-input-error for="cost_rate" class="mt-2" />
                            </div>

                            <!-- Price Rate -->
                            <div class="col-span-6 sm:col-span-4">
                                <x-label for="price_rate" value="Price Rate (per hour)" />
                                <x-input id="price_rate" type="number" min="0" step="0.01" class="mt-1 block w-full" wire:model="price_rate" />
                                <x-input-error for="price_rate" class="mt-2" />
                            </div>

                            <!-- Is Default -->
                            <div class="col-span-6 sm:col-span-4">
                                <label class="flex items-center">
                                    <x-checkbox wire:model="is_default" />
                                    <span class="ml-2 text-sm text-gray-600">Set as default rate</span>
                                </label>
                                <x-input-error for="is_default" class="mt-2" />
                            </div>

                            <!-- Is Active -->
                            <div class="col-span-6 sm:col-span-4">
                                <label class="flex items-center">
                                    <x-checkbox wire:model="is_active" />
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                                <x-input-error for="is_active" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button type="button" class="mr-3" wire:navigate href="{{ route('labor-rates.index') }}">
                                Cancel
                            </x-secondary-button>
                            
                            <x-button>
                                Update Labor Rate
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
