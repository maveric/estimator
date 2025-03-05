<div>
    <div class="p-6">
        <h2 class="text-lg font-semibold">
            {{ $mode === 'create' ? 'Create New Item' : 'Edit Item' }}
        </h2>
        
        <div class="mt-4">
            <!-- Simple form for testing -->
            <p>Item Form Component Loaded Successfully</p>
            <p>Mode: {{ $mode }}</p>
        </div>
    </div>
</div>

<div class="mt-5 md:mt-0 md:col-span-2">
    <form wire:submit.prevent="save">
        <div class="shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 bg-white sm:p-6">
                <div class="grid grid-cols-6 gap-6">
                    <div class="col-span-6 sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input wire:model="name" type="text" id="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select wire:model="category_id" id="category_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- ... existing form fields ... -->
                </div>
            </div>

            <!-- Cost Summary -->
            <div class="bg-white shadow-md rounded-lg p-6 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cost Summary</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-500">Cost</h4>
                        <div class="mt-1">
                            <p class="text-lg font-semibold text-gray-900">${{ number_format($cost_per_unit + (($labor_units / 60) * $primaryLaborRate), 2) }}</p>
                            <p class="text-sm text-gray-500">Materials: ${{ number_format($cost_per_unit, 2) }}</p>
                            <p class="text-sm text-gray-500">Labor: ${{ number_format(($labor_units / 60) * $primaryLaborRate, 2) }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-500">Charge</h4>
                        <div class="mt-1">
                            <p class="text-lg font-semibold text-gray-900">${{ number_format($charge_per_unit + (($labor_units / 60) * $primaryLaborRate * $defaultLaborMarkup), 2) }}</p>
                            <p class="text-sm text-gray-500">Materials: ${{ number_format($charge_per_unit, 2) }}</p>
                            <p class="text-sm text-gray-500">Labor: ${{ number_format(($labor_units / 60) * $primaryLaborRate * $defaultLaborMarkup, 2) }}</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-500">Profit</h4>
                        <div class="mt-1">
                            <p class="text-lg font-semibold text-gray-900">${{ number_format(($charge_per_unit + (($labor_units / 60) * $primaryLaborRate * $defaultLaborMarkup)) - ($cost_per_unit + (($labor_units / 60) * $primaryLaborRate)), 2) }}</p>
                            <p class="text-sm text-gray-500">Margin: {{ number_format((($charge_per_unit + (($labor_units / 60) * $primaryLaborRate * $defaultLaborMarkup)) > 0) ? ((($charge_per_unit + (($labor_units / 60) * $primaryLaborRate * $defaultLaborMarkup)) - ($cost_per_unit + (($labor_units / 60) * $primaryLaborRate))) / ($charge_per_unit + (($labor_units / 60) * $primaryLaborRate * $defaultLaborMarkup)) * 100) : 0, 1) }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ $mode === 'create' ? 'Create' : 'Update' }}
                </button>
            </div>
        </div>
    </form>
</div>
