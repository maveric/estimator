<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">{{ $mode === 'edit' ? 'Edit Package' : 'Create Package' }}</h2>
        <a href="{{ route('packages.index') }}" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">
            Back to Packages
        </a>
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
        @if (session()->has('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" wire:model="name" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="is_active" wire:model="is_active" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    @error('is_active') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" wire:model="description" rows="3" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Package Cost Summary -->
        @if (count($packageAssemblies) > 0)
            @php $costs = $this->calculateCosts(); @endphp
            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Package Cost Summary</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500">Cost</h4>
                            <div class="mt-1">
                                <p class="text-lg font-semibold text-gray-900">${{ number_format($costs['total_cost'], 2) }}</p>
                                <p class="text-sm text-gray-500">Materials: ${{ number_format($costs['material_cost'], 2) }}</p>
                                <p class="text-sm text-gray-500">Labor: ${{ number_format($costs['labor_cost'], 2) }}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500">Charge</h4>
                            <div class="mt-1">
                                <p class="text-lg font-semibold text-gray-900">${{ number_format($costs['total_charge'], 2) }}</p>
                                <p class="text-sm text-gray-500">Materials: ${{ number_format($costs['material_charge'], 2) }}</p>
                                <p class="text-sm text-gray-500">Labor: ${{ number_format($costs['labor_charge'], 2) }}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500">Profit</h4>
                            <div class="mt-1">
                                <p class="text-lg font-semibold text-gray-900">${{ number_format($costs['total_charge'] - $costs['total_cost'], 2) }}</p>
                                <p class="text-sm text-gray-500">Margin: {{ number_format(($costs['total_charge'] > 0) ? (($costs['total_charge'] - $costs['total_cost']) / $costs['total_charge'] * 100) : 0, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assemblies</h3>
            @error('packageAssemblies') <span class="text-red-500 text-sm block mb-2">{{ $message }}</span> @enderror

            <!-- Add Assembly Form -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h4 class="font-medium text-gray-700 mb-3">Add Assembly</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="selectedAssembly" class="block text-sm font-medium text-gray-700">Select Assembly</label>
                        <select wire:model="selectedAssembly" id="selectedAssembly" 
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">-- Select an assembly --</option>
                            @foreach ($availableAssemblies as $assembly)
                                <option value="{{ $assembly->id }}">{{ $assembly->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                        <input type="number" wire:model="quantity" id="quantity" 
                            step="1" 
                            min="0.01" 
                            max="999.99"
                            inputmode="decimal"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="button" wire:click="addAssembly" 
                            class="px-4 py-2 bg-blue-800 text-white font-medium rounded hover:bg-blue-900">
                            Add Assembly
                        </button>
                    </div>
                </div>
            </div>

            <!-- Assemblies Table -->
            <div class="overflow-x-auto">
                @if (count($packageAssemblies) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assembly</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($packageAssemblies as $index => $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $item['description'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($editingAssemblyIndex === $index)
                                            <input type="number" 
                                                wire:model="editingAssemblyQuantity" 
                                                step="1" 
                                                min="0.01" 
                                                max="999.99"
                                                inputmode="decimal"
                                                class="block w-24 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            @error("packageAssemblies.{$index}.quantity") 
                                                <span class="text-red-500 text-xs">{{ $message }}</span> 
                                            @enderror
                                        @else
                                            <div class="text-sm text-gray-900">{{ $item['quantity'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if ($editingAssemblyIndex === $index)
                                            <button type="button" wire:click="updateAssembly" class="text-green-600 hover:text-green-900 mr-3">
                                                Save
                                            </button>
                                            <button type="button" wire:click="cancelEdit" class="text-gray-600 hover:text-gray-900">
                                                Cancel
                                            </button>
                                        @else
                                            <button type="button" wire:click="editAssembly({{ $index }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                                Edit
                                            </button>
                                            <button type="button" wire:click="removeAssembly({{ $index }})" class="text-red-600 hover:text-red-900">
                                                Remove
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-4 text-gray-500">
                        No assemblies added to this package yet.
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" onclick="window.history.back()" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 mr-3">
                Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded hover:bg-blue-900">
                {{ $mode === 'edit' ? 'Update Package' : 'Create Package' }}
            </button>
        </div>
    </form>
</div> 