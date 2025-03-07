<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Packages</h3>
        
        <!-- Add Package Form -->
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="selectedPackage" class="block text-sm font-medium text-gray-700">Select Package</label>
                <select wire:model="selectedPackage" 
                        id="selectedPackage" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Select a package --</option>
                    @foreach($availablePackages as $package)
                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="packageQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" 
                       wire:model="packageQuantity" 
                       id="packageQuantity" 
                       min="0.01" 
                       step="0.01" 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="flex items-end">
                <button type="button" 
                        wire:click="addPackage" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Package
                </button>
            </div>
        </div>
        
        <!-- Packages List -->
        <div class="mt-6">
            @if(count($packages) > 0)
                <div class="space-y-6">
                    @foreach($packages as $packageIndex => $package)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <!-- Package Header -->
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <button type="button" wire:click="togglePackageCollapse({{ $packageIndex }})" class="mr-2 text-gray-500 hover:text-gray-700">
                                            <svg class="w-5 h-5 transform {{ in_array($packageIndex, $collapsedPackages) ? '' : 'rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">{{ $package->name }}</h4>
                                            <div class="text-sm text-gray-500 mt-1 space-y-1">
                                                <p>Quantity: {{ $package->quantity }}</p>
                                                @if($package->description)
                                                    <p>{{ $package->description }}</p>
                                                @endif
                                                <div class="flex space-x-4">
                                                    <p>Cost: ${{ number_format($package->total_cost, 2) }}</p>
                                                    <p>Charge: ${{ number_format($package->total_charge, 2) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    @if($editingPackageIndex === $packageIndex)
                                        <div class="flex items-center mr-4">
                                            <label for="editingPackageQuantity" class="block text-sm font-medium text-gray-700 mr-2">Quantity:</label>
                                            <input 
                                                type="number" 
                                                wire:model="editingPackageQuantity" 
                                                class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                min="0.01"
                                                step="0.01"
                                            >
                                        </div>
                                        <button 
                                            type="button" 
                                            wire:click.stop="updatePackage"
                                            class="text-green-600 hover:text-green-900"
                                        >
                                            Save
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click.stop="cancelEditPackage" 
                                            class="ml-3 text-gray-600 hover:text-gray-900"
                                        >
                                            Cancel
                                        </button>
                                    @else
                                        <button 
                                            type="button" 
                                            wire:click.stop="editPackage({{ $packageIndex }})" 
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            Edit
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="removePackage({{ $packageIndex }})"
                                            wire:confirm="Are you sure you want to remove this package? This action cannot be undone."
                                            class="ml-3 text-red-600 hover:text-red-900"
                                        >
                                            Remove
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Package Content (Assemblies) -->
                            @if(!in_array($packageIndex, $collapsedPackages))
                                <div class="mt-4">
                                    <!-- Add Assembly Form -->
                                    <div class="flex justify-between items-center mb-4">
                                        <h5 class="text-sm font-medium text-gray-700">Assemblies</h5>
                                        <button 
                                            type="button"
                                            wire:click="startAddingAssemblyToPackage({{ $packageIndex }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Add Assembly
                                        </button>
                                    </div>

                                    @if($addingAssemblyToPackageIndex === $packageIndex)
                                        <div class="mb-4 bg-gray-100 p-4 rounded-md">
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                                <div>
                                                    <label for="selectedAssembly" class="block text-sm font-medium text-gray-700">Select Assembly</label>
                                                    <select wire:model="selectedAssembly" 
                                                            id="selectedAssembly" 
                                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                        <option value="">-- Select an assembly --</option>
                                                        @foreach($availableAssemblies as $assembly)
                                                            <option value="{{ $assembly->id }}">{{ $assembly->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div>
                                                    <label for="selectedAssemblyQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                                    <input type="number" 
                                                           wire:model="selectedAssemblyQuantity" 
                                                           id="selectedAssemblyQuantity" 
                                                           min="0.01" 
                                                           step="0.01" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                </div>
                                                
                                                <div class="flex items-end space-x-3">
                                                    <button type="button" 
                                                            wire:click="addAssemblyToPackage" 
                                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Add
                                                    </button>
                                                    <button type="button" 
                                                            wire:click="cancelAddingAssemblyToPackage" 
                                                            class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Assemblies List -->
                                    @if($package->assemblies->count() > 0)
                                        <div class="space-y-4">
                                            @foreach($package->assemblies as $assemblyIndex => $assembly)
                                                <div class="bg-white p-4 rounded-lg border border-gray-200">
                                                    <!-- Assembly Header -->
                                                    <div class="flex justify-between items-center">
                                                        <div class="flex-1">
                                                            <div class="flex items-center">
                                                                <button type="button" wire:click="toggleAssemblyCollapse({{ $packageIndex }}, {{ $assemblyIndex }})" class="mr-2 text-gray-500 hover:text-gray-700">
                                                                    <svg class="w-5 h-5 transform {{ in_array("{$packageIndex}-{$assemblyIndex}", $collapsedAssemblies) ? '' : 'rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                                    </svg>
                                                                </button>
                                                                <div>
                                                                    <h6 class="text-sm font-medium text-gray-900">{{ $assembly->name }}</h6>
                                                                    <div class="text-sm text-gray-500">
                                                                        <p>Quantity: {{ $assembly->quantity }}</p>
                                                                        <div class="flex space-x-4">
                                                                            <p>Cost: ${{ number_format($assembly->total_cost, 2) }}</p>
                                                                            <p>Charge: ${{ number_format($assembly->total_charge, 2) }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center">
                                                            @if($editingPackageAssemblyData['package_index'] === $packageIndex && 
                                                                $editingPackageAssemblyData['assembly_index'] === $assemblyIndex)
                                                                <div class="flex items-center mr-4">
                                                                    <label for="editingAssemblyQuantity" class="block text-sm font-medium text-gray-700 mr-2">Quantity:</label>
                                                                    <input 
                                                                        type="number" 
                                                                        wire:model="editingPackageAssemblyData.quantity" 
                                                                        class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                                        min="0.01"
                                                                        step="0.01"
                                                                    >
                                                                </div>
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="updatePackageAssembly" 
                                                                    class="text-green-600 hover:text-green-900"
                                                                >
                                                                    Save
                                                                </button>
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="cancelEditPackageAssembly" 
                                                                    class="ml-3 text-gray-600 hover:text-gray-900"
                                                                >
                                                                    Cancel
                                                                </button>
                                                            @else
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="editPackageAssembly({{ $packageIndex }}, {{ $assemblyIndex }})" 
                                                                    class="text-indigo-600 hover:text-indigo-900"
                                                                >
                                                                    Edit
                                                                </button>
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="removePackageAssembly({{ $packageIndex }}, {{ $assemblyIndex }})"
                                                                    wire:confirm="Are you sure you want to remove this assembly from the package? This action cannot be undone."
                                                                    class="ml-3 text-red-600 hover:text-red-900"
                                                                >
                                                                    Remove
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Assembly Content (Items) -->
                                                    @if(!in_array("{$packageIndex}-{$assemblyIndex}", $collapsedAssemblies))
                                                        <div class="mt-4">
                                                            <!-- Add Item Form -->
                                                            <div class="flex justify-between items-center mb-4">
                                                                <h6 class="text-sm font-medium text-gray-700">Items</h6>
                                                                <button 
                                                                    type="button"
                                                                    wire:click="startAddingItemToAssembly({{ $packageIndex }}, {{ $assemblyIndex }})"
                                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                                >
                                                                    Add Item
                                                                </button>
                                                            </div>

                                                            @if($addingItemToAssemblyData['package_index'] === $packageIndex && 
                                                                $addingItemToAssemblyData['assembly_index'] === $assemblyIndex)
                                                                <div class="mb-4 bg-gray-100 p-4 rounded-md">
                                                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                                                        <div>
                                                                            <label for="selectedItem" class="block text-sm font-medium text-gray-700">Select Item</label>
                                                                            <select wire:model="addingItemToAssemblyData.selected_item" 
                                                                                    id="selectedItem" 
                                                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                                                <option value="">-- Select an item --</option>
                                                                                @foreach($availableItems as $item)
                                                                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div>
                                                                            <label for="itemQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                                                            <input type="number" 
                                                                                   wire:model="addingItemToAssemblyData.quantity" 
                                                                                   id="itemQuantity" 
                                                                                   min="0.01" 
                                                                                   step="0.01" 
                                                                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                                        </div>
                                                                        
                                                                        <div class="flex items-end space-x-3">
                                                                            <button type="button" 
                                                                                    wire:click="addItemToAssembly" 
                                                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                                Add
                                                                            </button>
                                                                            <button type="button" 
                                                                                    wire:click="cancelAddingItemToAssembly" 
                                                                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                                                Cancel
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <!-- Items Table -->
                                                            @if($assembly->items->count() > 0)
                                                                <table class="min-w-full divide-y divide-gray-200">
                                                                    <thead class="bg-gray-50">
                                                                        <tr>
                                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                                        @foreach($assembly->items as $itemIndex => $item)
                                                                            <tr>
                                                                                <td class="border px-4 py-2 text-sm">
                                                                                    <div>
                                                                                        <span class="font-medium text-gray-900">{{ $item->name }}</span>
                                                                                        @if($item->description)
                                                                                            <p class="text-gray-500">{{ $item->description }}</p>
                                                                                        @endif
                                                                                    </div>
                                                                                </td>
                                                                                <td class="border px-4 py-2 text-sm">
                                                                                    @if($editingAssemblyItemData['package_index'] === $packageIndex && 
                                                                                        $editingAssemblyItemData['assembly_index'] === $assemblyIndex && 
                                                                                        $editingAssemblyItemData['item_index'] === $itemIndex)
                                                                                        <input 
                                                                                            type="number" 
                                                                                            wire:model="editingAssemblyItemData.quantity" 
                                                                                            class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                                                            min="0.01"
                                                                                            step="0.01"
                                                                                        >
                                                                                    @else
                                                                                        {{ $item->quantity }}
                                                                                    @endif
                                                                                </td>
                                                                                <td class="border px-4 py-2 text-sm">{{ $item->unit_of_measure ?? 'N/A' }}</td>
                                                                                <td class="border px-4 py-2 text-sm">${{ number_format($item->material_charge_rate, 2) }}</td>
                                                                                <td class="border px-4 py-2 text-sm">${{ number_format($item->quantity * $item->material_charge_rate, 2) }}</td>
                                                                                <td class="border px-4 py-2 text-sm">
                                                                                    <div class="flex items-center">
                                                                                        @if($editingAssemblyItemData['package_index'] === $packageIndex && 
                                                                                            $editingAssemblyItemData['assembly_index'] === $assemblyIndex && 
                                                                                            $editingAssemblyItemData['item_index'] === $itemIndex)
                                                                                            <button 
                                                                                                type="button" 
                                                                                                wire:click="updateAssemblyItem" 
                                                                                                class="text-green-600 hover:text-green-900"
                                                                                            >
                                                                                                Save
                                                                                            </button>
                                                                                            <button 
                                                                                                type="button" 
                                                                                                wire:click="cancelEditAssemblyItem" 
                                                                                                class="ml-3 text-gray-600 hover:text-gray-900"
                                                                                            >
                                                                                                Cancel
                                                                                            </button>
                                                                                        @else
                                                                                            <button 
                                                                                                type="button" 
                                                                                                wire:click="editAssemblyItem({{ $packageIndex }}, {{ $assemblyIndex }}, {{ $itemIndex }})" 
                                                                                                class="text-indigo-600 hover:text-indigo-900"
                                                                                            >
                                                                                                Edit
                                                                                            </button>
                                                                                            <button 
                                                                                                type="button" 
                                                                                                wire:click="removeAssemblyItem({{ $packageIndex }}, {{ $assemblyIndex }}, {{ $itemIndex }})"
                                                                                                wire:confirm="Are you sure you want to remove this item from the assembly? This action cannot be undone."
                                                                                                class="ml-3 text-red-600 hover:text-red-900"
                                                                                            >
                                                                                                Remove
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            @else
                                                                <p class="text-sm text-gray-500">No items in this assembly.</p>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="mt-2">
                                                            <p class="text-sm text-gray-500">{{ $assembly->items->count() }} {{ Str::plural('item', $assembly->items->count()) }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No assemblies in this package.</p>
                                    @endif
                                </div>
                            @else
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">{{ $package->assemblies->count() }} {{ Str::plural('assembly', $package->assemblies->count()) }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-500">
                    No packages added to this estimate yet.
                </div>
            @endif
        </div>
    </div>
</div> 