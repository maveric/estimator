<div class="bg-white rounded-lg shadow-sm p-4">
    @if($isEditing)
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8">
                    <x-form.input-group
                        label="Name"
                        type="text"
                        name="name"
                        model="name"
                    />
                </div>
                <div class="col-span-4">
                    <x-form.input-group
                        label="Quantity"
                        type="number"
                        name="quantity"
                        model="quantity"
                        step="1"
                    />
                </div>
                <div class="col-span-12">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <x-button variant="secondary" wire:click="cancelEditing">
                    Cancel
                </x-button>
                <x-button variant="primary" type="submit">
                    Save
                </x-button>
            </div>
        </form>
    @else
        <div class="space-y-4">
            <!-- Package Header -->
            <div class="grid grid-cols-12 gap-4 items-start">
                <div class="col-span-4 flex items-start space-x-2">
                    <button wire:click="toggleCollapse" class="mt-1.5 text-gray-500 hover:text-gray-700">
                        <svg class="w-4 h-4 transform transition-transform duration-200 {{ $isCollapsed ? '' : 'rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">{{ $package->name }}</h3>
                        @if($package->description)
                            <p class="mt-1 text-sm text-gray-500">{{ $package->description }}</p>
                        @endif
                    </div>
                </div>

                <div class="col-span-6">
                    <div class="grid grid-cols-12 gap-4 text-sm">
                        <div class="col-span-4 text-center">
                            <span class="block text-gray-500 font-medium">Material</span>
                            <div class="mt-0.5">
                                <span class="block font-medium">${{ number_format($totals['material_charge'], 2) }}</span>
                                <span class="block text-xs text-gray-500">${{ number_format($totals['material_cost'], 2) }}</span>
                            </div>
                        </div>
                        <div class="col-span-4 text-center">
                            <span class="block text-gray-500 font-medium">Labor</span>
                            <div class="mt-0.5">
                                <span class="block font-medium">${{ number_format($totals['labor_charge'], 2) }}</span>
                                <span class="block text-xs text-gray-500">${{ number_format($totals['labor_cost'], 2) }}</span>
                            </div>
                        </div>
                        <div class="col-span-4 text-center">
                            <span class="block text-gray-500 font-medium">Total</span>
                            <div class="mt-0.5">
                                <span class="block font-medium">${{ number_format($totals['total_charge'], 2) }}</span>
                                <span class="block text-xs text-gray-500">${{ number_format($totals['total_cost'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-span-2 flex justify-end items-center space-x-2">
                    <span class="text-sm text-gray-500">Qty: {{ $package->quantity }}</span>
                    <x-button variant="link" wire:click="startEditing">Edit</x-button>
                    <x-button variant="danger" wire:click="delete">Delete</x-button>
                </div>
            </div>

            @unless($isCollapsed)
                <!-- Assemblies List -->
                <div>
                    <!-- Add Assembly Form -->
                    <div class="mb-4 bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Add Assembly</h4>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-8">
                                <select wire:model="selectedAssemblyId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="">Select an assembly...</option>
                                    @foreach($availableAssemblies as $assembly)
                                        <option value="{{ $assembly->id }}">{{ $assembly->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <input type="number" wire:model="newAssemblyQuantity" min="1" step="1" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Quantity">
                            </div>
                            <div class="col-span-2">
                                <button type="button" wire:click="addAssembly" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Add
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700">Assemblies</h4>
                    </div>

                    <div class="space-y-2">
                        @foreach($assemblies as $assembly)
                            @livewire('estimates.assembly-component', [
                                'assembly' => $assembly,
                                'parentType' => 'package',
                                'parentId' => $package->id
                            ], key('assembly-'.$assembly->id))
                        @endforeach
                    </div>
                </div>
            @endunless
        </div>
    @endif
</div> 