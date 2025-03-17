<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Item') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <form wire:submit="create" class="p-6 lg:p-8 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-6 gap-6">
                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <x-label for="name" value="{{ __('Name') }}" />
                            <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                            <x-input-error for="name" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="col-span-6">
                            <x-label for="description" value="{{ __('Description') }}" />
                            <x-textarea id="description" class="mt-1 block w-full" wire:model="description" />
                            <x-input-error for="description" class="mt-2" />
                        </div>

                        <!-- SKU -->
                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="sku" value="{{ __('SKU') }}" />
                            <x-input id="sku" type="text" class="mt-1 block w-full" wire:model="sku" />
                            <x-input-error for="sku" class="mt-2" />
                        </div>

                        <!-- Unit of Measure -->
                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="unit_of_measure" value="{{ __('Unit of Measure') }}" />
                            <x-input id="unit_of_measure" type="text" class="mt-1 block w-full" wire:model="unit_of_measure" />
                            <x-input-error for="unit_of_measure" class="mt-2" />
                        </div>

                        <!-- Material Cost -->
                        <div class="col-span-6 sm:col-span-2">
                            <x-label for="material_cost" value="{{ __('Material Cost') }}" />
                            <x-input id="material_cost" type="number" step="0.0001" class="mt-1 block w-full" wire:model="material_cost" />
                            <x-input-error for="material_cost" class="mt-2" />
                        </div>

                        <!-- Material Price -->
                        <div class="col-span-6 sm:col-span-2">
                            <x-label for="material_price" value="{{ __('Material Price') }}" />
                            <x-input id="material_price" type="number" step="0.0001" class="mt-1 block w-full" wire:model="material_price" />
                            <x-input-error for="material_price" class="mt-2" />
                        </div>

                        <!-- Labor Minutes -->
                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="labor_minutes" value="{{ __('Labor Time (Minutes)') }}" />
                            <x-input id="labor_minutes" type="number" step="0.0001" class="mt-1 block w-full" wire:model="labor_minutes" />
                            <x-input-error for="labor_minutes" class="mt-2" />
                        </div>

                        <!-- Labor Rate -->
                        <div class="col-span-6 sm:col-span-3">
                            <x-label for="labor_rate_id" value="{{ __('Labor Rate') }}" />
                            <x-select id="labor_rate_id" class="mt-1 block w-full" wire:model="labor_rate_id">
                                <option value="">Select a labor rate</option>
                                @foreach($this->laborRates as $rate)
                                    <option value="{{ $rate->id }}">{{ $rate->name }} (${{ number_format($rate->price_rate, 2) }}/hr)</option>
                                @endforeach
                            </x-select>
                            <x-input-error for="labor_rate_id" class="mt-2" />
                        </div>

                        <!-- Is Template -->
                        <div class="col-span-6 sm:col-span-3">
                            <label class="flex items-center">
                                <x-checkbox wire:model="is_template" />
                                <span class="ms-2 text-sm text-gray-600">{{ __('Is Template') }}</span>
                            </label>
                            <x-input-error for="is_template" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="col-span-6 sm:col-span-3">
                            <label class="flex items-center">
                                <x-checkbox wire:model="is_active" />
                                <span class="ms-2 text-sm text-gray-600">{{ __('Is Active') }}</span>
                            </label>
                            <x-input-error for="is_active" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button onclick="window.history.back()" type="button" class="me-3">
                            {{ __('Cancel') }}
                        </x-secondary-button>

                        <x-button>
                            {{ __('Create') }}
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
