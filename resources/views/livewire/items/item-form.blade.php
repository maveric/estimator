<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $mode === 'edit' ? 'Edit Item' : 'Create New Item' }}
        </h1>
    </div>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <form wire:submit.prevent="save">
            @if (session()->has('message'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Item Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Item Name</label>
                            <input type="text" wire:model="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                            <input type="text" wire:model="sku" id="sku" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('sku') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Description -->
                        <div class="sm:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unit of Measure -->
                        <div>
                            <label for="unit_of_measure" class="block text-sm font-medium text-gray-700">Unit of Measure</label>
                            <select wire:model="unit_of_measure" id="unit_of_measure" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select a unit</option>
                                <option value="each">Each</option>
                                <option value="linear_feet">Linear Feet</option>
                                <option value="square_feet">Square Feet</option>
                                <option value="cubic_feet">Cubic Feet</option>
                                <option value="pound">Pound</option>
                                <option value="gallon">Gallon</option>
                                <option value="hour">Hour</option>
                                <option value="sheet">Sheet</option>
                                <option value="roll">Roll</option>
                                <option value="box">Box</option>
                                <option value="kit">Kit</option>
                            </select>
                            @error('unit_of_measure') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Active Status -->
                        <div>
                            <div class="flex items-center">
                                <input type="checkbox" wire:model="is_active" id="is_active" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                            </div>
                            @error('is_active') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost and Pricing Section -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Cost and Pricing</h3>
                    <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <!-- Material Cost Rate -->
                        <div>
                            <label for="material_cost_rate" class="block text-sm font-medium text-gray-700">Material Cost Rate</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" wire:model="material_cost_rate" id="material_cost_rate" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            @error('material_cost_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Material Charge Rate -->
                        <div>
                            <label for="material_charge_rate" class="block text-sm font-medium text-gray-700">Material Charge Rate</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" step="0.01" min="0" wire:model="material_charge_rate" id="material_charge_rate" class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            @error('material_charge_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Labor Units -->
                        <div>
                            <label for="labor_units" class="block text-sm font-medium text-gray-700">Labor Units (minutes)</label>
                            <input type="number" step="0.01" min="0" wire:model="labor_units" id="labor_units" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            @error('labor_units') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Categories</h3>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        @foreach($categories as $category)
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="category-{{ $category->id }}" 
                                    value="{{ $category->id }}" 
                                    wire:model="selectedCategories" 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                >
                                <label for="category-{{ $category->id }}" class="ml-2 block text-sm text-gray-700">
                                    {{ $category->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('selectedCategories') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="cancel" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 mr-3">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-800 text-white font-medium rounded hover:bg-blue-900">
                    {{ $mode === 'edit' ? 'Update Item' : 'Create Item' }}
                </button>
            </div>
        </form>
    </div>
</div>
