<div class="p-6 bg-gray-100">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $estimate->estimate_number ? 'Estimate #' . $estimate->estimate_number : 'New Estimate' }}</h1>
                <p class="text-sm text-gray-600">Status: <span class="font-medium">{{ ucfirst($estimate->status) }}</span></p>
            </div>
        </div>

        <!-- Customer Information -->
        @livewire('estimates.customer-information', ['estimate' => $estimate], key('customer-info-'.$estimate->id))

        <!-- Add Package Form -->
        <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8">
                    <label class="block text-sm font-medium text-gray-700">Package</label>
                    <select wire:model="selectedPackageId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select a package...</option>
                        @foreach($availablePackages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" wire:model="newPackageQuantity" step="1" min="1" 
                            class="block w-20 rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <button wire:click="addPackage" class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Package
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packages -->
        @if($packages->isNotEmpty())
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Packages</h2>
                <div class="space-y-4">
                    @foreach($packages as $package)
                        @livewire('estimates.package-component', [
                            'package' => $package,
                            'parentType' => 'estimate',
                            'parentId' => $estimate->id
                        ], key('package-'.$package->id))
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Add Assembly Form -->
        <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8">
                    <label class="block text-sm font-medium text-gray-700">Assembly</label>
                    <select wire:model="selectedAssemblyId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select an assembly...</option>
                        @foreach($availableAssemblies as $assembly)
                            <option value="{{ $assembly->id }}">{{ $assembly->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" wire:model="newAssemblyQuantity" step="0.01" min="0.01" 
                            class="block w-20 rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <button wire:click="addAssembly" class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Assembly
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assemblies -->
        @if($assemblies->isNotEmpty())
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Assemblies</h2>
                <div class="space-y-4">
                    @foreach($assemblies as $assembly)
                        @livewire('estimates.assembly-component', [
                            'assembly' => $assembly,
                            'parentType' => 'estimate',
                            'parentId' => $estimate->id
                        ], key('assembly-'.$assembly->id))
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Add Item Form -->
        <div class="mb-6 bg-white rounded-lg shadow-sm p-4">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8">
                    <label class="block text-sm font-medium text-gray-700">Item</label>
                    <select wire:model="selectedItemId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select an item...</option>
                        @foreach($availableItems as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} - {{ $item->unit_of_measure }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="block text-sm font-medium text-gray-700">Quantity</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <input type="number" wire:model="newItemQuantity" step="0.01" min="0.01" 
                            class="block w-20 rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <button wire:click="addItem" class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Items -->
        @if($items->isNotEmpty())
            <div class="mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Items</h2>
                <div class="space-y-4">
                    @foreach($items as $item)
                        @livewire('estimates.item-component', [
                            'item' => $item,
                            'parentType' => 'estimate',
                            'parentId' => $estimate->id
                        ], key('item-'.$item->id))
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Estimate Totals -->
        <div class="mt-6 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estimate Summary</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <!-- Left Column - Costs -->
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Material Cost:</span>
                        <span>${{ number_format($totals['material_cost'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Labor Cost:</span>
                        <span>${{ number_format($totals['labor_cost'], 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t">
                        <span class="font-medium">Total Cost:</span>
                        <span class="font-medium">${{ number_format($totals['total_cost'], 2) }}</span>
                    </div>
                </div>

                <!-- Right Column - Charges -->
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Material Charge:</span>
                        <span>${{ number_format($totals['material_charge'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Labor Charge:</span>
                        <span>${{ number_format($totals['labor_charge'], 2) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t">
                        <span class="font-medium">Subtotal:</span>
                        <span class="font-medium">${{ number_format($totals['subtotal'], 2) }}</span>
                    </div>
                </div>

                <!-- Markup and Discount - Full Width -->
                <div class="col-span-2 pt-4 mt-2 border-t space-y-4">
                    <!-- Markup -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Markup Percentage</label>
                                <div class="flex rounded-md shadow-sm">
                                    <input type="number" wire:model.live="markup_percentage" step="0.1" min="0" max="100" 
                                        class="block w-full rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        %
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm text-gray-600 mb-1">Markup Amount</span>
                            <span class="text-lg font-medium">${{ number_format($totals['markup_amount'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage</label>
                                <div class="flex rounded-md shadow-sm">
                                    <input type="number" wire:model.live="discount_percentage" step="0.1" min="0" max="100" 
                                        class="block w-full rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        %
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Additional Discount</label>
                                <div class="flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                        $
                                    </span>
                                    <input type="number" wire:model.live="discount_amount" step="0.01" min="0" 
                                        class="block w-full rounded-r-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm text-gray-600 mb-1">Total Discount</span>
                            <span class="text-lg font-medium">${{ number_format($totals['total_discount'], 2) }}</span>
                            @if($totals['discount_percentage_amount'] > 0 && $totals['discount_fixed_amount'] > 0)
                                <div class="text-xs text-gray-500 mt-1">
                                    (${{ number_format($totals['discount_percentage_amount'], 2) }} + ${{ number_format($totals['discount_fixed_amount'], 2) }})
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Final Total - Full Width -->
                <div class="col-span-2 pt-4 mt-2 border-t">
                    <div class="flex justify-between items-baseline">
                        <span class="text-lg font-bold">Total:</span>
                        <span class="text-2xl font-bold">${{ number_format($totals['final_total'], 2) }}</span>
                    </div>
                </div>

                <!-- Profit - Full Width -->
                <div class="col-span-2 pt-4 mt-2 border-t">
                    <div class="flex justify-between">
                        <span class="font-medium text-green-700">Profit:</span>
                        <div class="text-right">
                            <span class="font-medium text-green-700">${{ number_format($totals['profit'], 2) }}</span>
                            <span class="ml-2 text-sm text-green-600">({{ number_format($totals['profit_percentage'], 1) }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 