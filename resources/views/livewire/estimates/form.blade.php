<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">
            Estimate
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

            <!-- Customer Information -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Information</h3>
                    <div class="mt-5 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                            <input type="text" wire:model="customer_name" id="customer_name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="customer_email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" wire:model="customer_email" id="customer_email" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('customer_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" wire:model="customer_phone" id="customer_phone" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('customer_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid Until</label>
                            <input type="date" wire:model="valid_until" id="valid_until" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('valid_until') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-6">
                            <label for="customer_address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea wire:model="customer_address" id="customer_address" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            @error('customer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estimate Details -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Estimate Details</h3>
                    <div class="mt-5 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select wire:model="status" id="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="draft">Draft</option>
                                <option value="sent">Sent</option>
                                <option value="approved">Approved</option>
                                <option value="declined">Declined</option>
                            </select>
                            @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="markup_percentage" class="block text-sm font-medium text-gray-700">Markup (%)</label>
                            <input type="number" wire:model="markup_percentage" id="markup_percentage" min="0" max="100" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('markup_percentage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount (%)</label>
                            <input type="number" wire:model="discount_percentage" id="discount_percentage" min="0" max="100" step="0.01" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('discount_percentage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea wire:model="notes" id="notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Packages Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Packages</h3>
                    
                    <!-- Add Package Form -->
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="packageSearch" class="block text-sm font-medium text-gray-700">Search Packages</label>
                            <input type="text" wire:model.live="packageSearch" id="packageSearch" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search packages...">
                        </div>
                        
                        <div>
                            <label for="selectedPackage" class="block text-sm font-medium text-gray-700">Select Package</label>
                            <select wire:model="selectedPackage" id="selectedPackage" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select a package --</option>
                                @foreach($availablePackages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="packageQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" wire:model="packageQuantity" id="packageQuantity" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="button" wire:click="addPackage" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <button type="button" wire:click="togglePackageCollapse({{ $packageIndex }})" class="mr-2">
                                                    <svg class="w-5 h-5 transform transition-transform duration-200 {{ in_array($packageIndex, $collapsedPackages) ? '' : 'rotate-90' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M7.293 4.707a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L10.586 10 7.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <div>
                                                    <h4 class="text-xl font-bold text-indigo-700 uppercase">{{ $package->name }}</h4>
                                                    <p class="text-sm text-gray-500 mt-1">Quantity: {{ $package->quantity }}</p>
                                                    @php
                                                        $packageTotal = $this->calculatePackageTotal($package);
                                                    @endphp
                                                    <div class="flex space-x-4 mt-1">
                                                        <p class="text-sm text-gray-500">Cost: ${{ number_format($packageTotal['cost'], 2) }}</p>
                                                        <p class="text-sm text-gray-500">Charge: ${{ number_format($packageTotal['charge'], 2) }}</p>
                                                    </div>
                                                    @if($package->description)
                                                        <p class="text-sm text-gray-500 mt-1 max-w-2xl truncate">{{ $package->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-4">
                                                <!-- Add Assembly Button -->
                                                <button type="button" 
                                                    wire:click="startAddingAssemblyToPackage({{ $packageIndex }})"
                                                    class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                                                    Add Assembly
                                                </button>
                                                <!-- Existing Package Controls -->
                                                @if ($editingPackageIndex === $packageIndex)
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
                                                        wire:click="updatePackage" 
                                                        class="text-green-600 hover:text-green-900"
                                                    >
                                                        Save
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        wire:click="cancelEditPackage" 
                                                        class="ml-3 text-gray-600 hover:text-gray-900"
                                                    >
                                                        Cancel
                                                    </button>
                                                @else
                                                    <button 
                                                        type="button" 
                                                        wire:click="editPackage({{ $packageIndex }})" 
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        wire:click="removePackage({{ $packageIndex }})" 
                                                        class="ml-3 text-red-600 hover:text-red-900"
                                                    >
                                                        Remove
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Add Assembly Form -->
                                        @if ($addingAssemblyToPackageIndex === $packageIndex)
                                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                                <div class="flex items-end space-x-4">
                                                    <div class="flex-1">
                                                        <label for="selectedPackageAssembly" class="block text-sm font-medium text-gray-700">Select Assembly</label>
                                                        <select wire:model="selectedPackageAssembly" 
                                                                id="selectedPackageAssembly" 
                                                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                            <option value="">Select an assembly...</option>
                                                            @foreach ($availableAssemblies as $assembly)
                                                                <option value="{{ $assembly->id }}">{{ $assembly->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="w-32">
                                                        <label for="packageAssemblyQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                                        <input type="number" 
                                                               wire:model="packageAssemblyQuantity" 
                                                               id="packageAssemblyQuantity" 
                                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                               min="1"
                                                               step="1">
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <button type="button" 
                                                                wire:click="addAssemblyToPackage({{ $packageIndex }})"
                                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Add
                                                        </button>
                                                        <button type="button" 
                                                                wire:click="cancelAddingAssemblyToPackage"
                                                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if (!in_array($packageIndex, $collapsedPackages))
                                            <div class="mt-4">
                                                <h5 class="text-sm font-medium text-gray-700">Assemblies in Package</h5>
                                                <div class="space-y-4">
                                                    @foreach($package->assemblies as $assemblyIndex => $assembly)
                                                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                                                            <div class="flex justify-between items-center">
                                                                <div class="flex-1">
                                                                    <div class="flex items-center">
                                                                        <button type="button" wire:click="togglePackageAssemblyCollapse({{ $packageIndex }}, {{ $assemblyIndex }})" class="mr-2 text-gray-500 hover:text-gray-700">
                                                                            <svg class="w-5 h-5 transform {{ in_array($packageIndex . '-' . $assemblyIndex, $collapsedPackageAssemblies) ? '' : 'rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                                            </svg>
                                                                        </button>
                                                                        <div>
                                                                            <h6 class="text-base font-medium text-gray-900">{{ $assembly->name }}</h6>
                                                                            <p class="text-sm text-gray-500 mt-1">Quantity: {{ $assembly->quantity }}</p>
                                                                            @if($assembly->description)
                                                                                <p class="text-sm text-gray-500 mt-1">{{ $assembly->description }}</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center">
                                                                    @if($editingPackageAssemblyData['package_index'] === $packageIndex && $editingPackageAssemblyData['assembly_index'] === $assemblyIndex)
                                                                        <div class="flex items-center mr-4">
                                                                            <label class="block text-sm font-medium text-gray-700 mr-2">Quantity:</label>
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
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            @if($assembly->items->count() > 0)
                                                                @if(!in_array($packageIndex . '-' . $assemblyIndex, $collapsedPackageAssemblies))
                                                                    <div class="mt-4">
                                                                        <h6 class="text-sm font-medium text-gray-700">Items in Assembly</h6>
                                                                        <div class="mt-2">
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
                                                                                                @if($editingPackageAssemblyItemData['package_index'] === $packageIndex && 
                                                                                                    $editingPackageAssemblyItemData['assembly_index'] === $assemblyIndex && 
                                                                                                    $editingPackageAssemblyItemData['item_index'] === $itemIndex)
                                                                                                    <input 
                                                                                                        type="number" 
                                                                                                        wire:model="editingPackageAssemblyItemData.quantity" 
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
                                                                                                    @if($editingPackageAssemblyItemData['package_index'] === $packageIndex && 
                                                                                                        $editingPackageAssemblyItemData['assembly_index'] === $assemblyIndex && 
                                                                                                        $editingPackageAssemblyItemData['item_index'] === $itemIndex)
                                                                                                        <button 
                                                                                                            type="button" 
                                                                                                            wire:click="updatePackageAssemblyItem" 
                                                                                                            class="text-green-600 hover:text-green-900"
                                                                                                        >
                                                                                                            Save
                                                                                                        </button>
                                                                                                        <button 
                                                                                                            type="button" 
                                                                                                            wire:click="cancelEditPackageAssemblyItem" 
                                                                                                            class="ml-3 text-gray-600 hover:text-gray-900"
                                                                                                        >
                                                                                                            Cancel
                                                                                                        </button>
                                                                                                    @else
                                                                                                        <button 
                                                                                                            type="button" 
                                                                                                            wire:click="editPackageAssemblyItem({{ $packageIndex }}, {{ $assemblyIndex }}, {{ $itemIndex }})" 
                                                                                                            class="text-indigo-600 hover:text-indigo-900"
                                                                                                        >
                                                                                                            Edit
                                                                                                        </button>
                                                                                                    @endif
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <div class="mt-4 text-sm text-gray-500">
                                                                    No items in this assembly.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
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

            <!-- Assemblies Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Assemblies</h3>
                    
                    <!-- Add Assembly Form -->
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="selectedAssembly" class="block text-sm font-medium text-gray-700">Select Assembly</label>
                            <select wire:model="selectedAssembly" id="selectedAssembly" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select an assembly --</option>
                                @foreach($availableAssemblies as $assembly)
                                    <option value="{{ $assembly->id }}">{{ $assembly->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="assemblyQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" wire:model="assemblyQuantity" id="assemblyQuantity" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="button" wire:click="addAssembly" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Assembly
                            </button>
                        </div>
                    </div>
                    
                    <!-- Assemblies List -->
                    <div class="mt-6">
                        @if(count($assemblies) > 0)
                            <div class="space-y-6">
                                @foreach($assemblies as $assemblyIndex => $assembly)
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex justify-between items-center">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <button type="button" wire:click="toggleAssemblyCollapse({{ $assemblyIndex }})" class="mr-2 text-gray-500 hover:text-gray-700">
                                                        <svg class="w-5 h-5 transform {{ in_array($assemblyIndex, $collapsedAssemblies) ? '' : 'rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                    <div>
                                                        <h4 class="text-lg font-medium text-gray-900">{{ $assembly['name'] }}</h4>
                                                        <p class="text-sm text-gray-500 mt-1">Quantity: {{ $assembly['quantity'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center">
                                                @if($editingAssemblyIndex === $assemblyIndex)
                                                    <div class="flex items-center mr-4">
                                                        <label for="editingAssemblyQuantity" class="block text-sm font-medium text-gray-700 mr-2">Quantity:</label>
                                                        <input 
                                                            type="number" 
                                                            wire:model="editingAssemblyQuantity" 
                                                            class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                            min="0.01"
                                                            step="0.01"
                                                        >
                                                    </div>
                                                    <button 
                                                        type="button" 
                                                        wire:click="updateAssembly" 
                                                        class="text-green-600 hover:text-green-900"
                                                    >
                                                        Save
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        wire:click="cancelEditAssembly" 
                                                        class="ml-3 text-gray-600 hover:text-gray-900"
                                                    >
                                                        Cancel
                                                    </button>
                                                @else
                                                    <button 
                                                        type="button" 
                                                        wire:click="editAssembly({{ $assemblyIndex }})" 
                                                        class="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        wire:click="removeAssembly({{ $assemblyIndex }})" 
                                                        class="ml-3 text-red-600 hover:text-red-900"
                                                    >
                                                        Remove
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($assembly['items']->count() > 0)
                                            @if(!in_array($assemblyIndex, $collapsedAssemblies))
                                                <div class="mt-4">
                                                    <h5 class="text-sm font-medium text-gray-700">Items</h5>
                                                    <div class="mt-2">
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
                                                                @foreach($assembly['items'] as $itemIndex => $item)
                                                                    <tr>
                                                                        <td class="border px-4 py-2 text-sm">
                                                                            <div>
                                                                                <span class="font-medium text-gray-900">{{ $item['name'] }}</span>
                                                                                @if($item['description'])
                                                                                    <p class="text-gray-500">{{ $item['description'] }}</p>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                        <td class="border px-4 py-2 text-sm">
                                                                            @if($editingAssemblyItemData['assembly_index'] === $assemblyIndex && $editingAssemblyItemData['item_index'] === $itemIndex)
                                                                                <input 
                                                                                    type="number" 
                                                                                    wire:model="editingAssemblyItemData.quantity" 
                                                                                    class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                                                    min="0.01"
                                                                                    step="0.01"
                                                                                >
                                                                            @else
                                                                                {{ $item['quantity'] }}
                                                                            @endif
                                                                        </td>
                                                                        <td class="border px-4 py-2 text-sm">{{ $item['unit_of_measure'] ?? 'N/A' }}</td>
                                                                        <td class="border px-4 py-2 text-sm">${{ number_format($item['material_charge_rate'], 2) }}</td>
                                                                        <td class="border px-4 py-2 text-sm">${{ number_format($item['quantity'] * $item['material_charge_rate'], 2) }}</td>
                                                                        <td class="border px-4 py-2 text-sm">
                                                                            <div class="flex items-center">
                                                                                @if($editingAssemblyItemData['assembly_index'] === $assemblyIndex && $editingAssemblyItemData['item_index'] === $itemIndex)
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
                                                                                        wire:click="editAssemblyItem({{ $assemblyIndex }}, {{ $itemIndex }})" 
                                                                                        class="text-indigo-600 hover:text-indigo-900"
                                                                                    >
                                                                                        Edit
                                                                                    </button>
                                                                                @endif
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="mt-4 text-sm text-gray-500">
                                                No items in this assembly.
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">
                                No assemblies added to this estimate yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Items</h3>
                    
                    <!-- Add Item Form -->
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="selectedItem" class="block text-sm font-medium text-gray-700">Select Item</label>
                            <select wire:model="selectedItem" id="selectedItem" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">-- Select an item --</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit_of_measure ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="itemQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" wire:model="itemQuantity" id="itemQuantity" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="button" wire:click="addItem" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Item
                            </button>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="mt-6">
                        @if(count($items) > 0)
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
                                    @foreach($items as $index => $item)
                                        <tr>
                                            <td class="border px-4 py-2 text-sm">{{ $item['name'] }}</td>
                                            <td class="border px-4 py-2 text-sm">
                                                @if($editingItemIndex === $index)
                                                    <input 
                                                        type="number" 
                                                        wire:model="editingItemQuantity" 
                                                        class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                                        min="0.01"
                                                        step="0.01"
                                                    >
                                                @else
                                                    {{ $item['quantity'] }}
                                                @endif
                                            </td>
                                            <td class="border px-4 py-2 text-sm">{{ $item['unit_of_measure'] ?? 'N/A' }}</td>
                                            <td class="border px-4 py-2 text-sm">${{ number_format($item['original_charge_rate'], 2) }}</td>
                                            <td class="border px-4 py-2 text-sm">${{ number_format($item['quantity'] * $item['original_charge_rate'], 2) }}</td>
                                            <td class="border px-4 py-2 text-sm">
                                                <div class="flex items-center">
                                                    @if($editingItemIndex === $index)
                                                        <button 
                                                            type="button" 
                                                            wire:click="updateItem" 
                                                            class="text-green-600 hover:text-green-900"
                                                        >
                                                            Save
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="cancelEditItem" 
                                                            class="ml-3 text-gray-600 hover:text-gray-900"
                                                        >
                                                            Cancel
                                                        </button>
                                                    @else
                                                        <button 
                                                            type="button" 
                                                            wire:click="editItem({{ $index }})" 
                                                            class="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            Edit
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="removeItem({{ $index }})" 
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
                            <div class="text-center py-4 text-gray-500">
                                No items added to this estimate yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Totals</h3>
                    <div class="mt-5 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Subtotal Cost</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($subtotalCost, 2) }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Subtotal Charge</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($subtotalCharge, 2) }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Markup Amount</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($markupAmount, 2) }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Discount Amount</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($discountAmount, 2) }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Total Cost</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($totalCost, 2) }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Total Charge</label>
                            <p class="mt-1 text-sm text-gray-900">${{ number_format($totalCharge, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
                <button type="button" wire:click="cancel" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Estimate
                </button>
            </div>
        </form>
    </div>
</div> 