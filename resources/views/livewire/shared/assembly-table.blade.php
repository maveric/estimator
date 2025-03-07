<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <!-- Assembly Header -->
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <button type="button" 
                        wire:click.prevent="toggleCollapse" 
                        class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5 transform {{ $isCollapsed ? '' : 'rotate-90' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ $assembly->name }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $assembly->description }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Quantity Editor -->
                <div class="flex items-center space-x-2">
                    @if($editingQuantity)
                        <div class="flex items-center space-x-2">
                            <input type="number" 
                                   wire:model="quantity" 
                                   class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   min="1"
                                   step="1"
                                   value="1">
                            <button wire:click.prevent="updateQuantity" 
                                    type="button"
                                    class="text-green-600 hover:text-green-900">
                                Save
                            </button>
                            <button wire:click.prevent="cancelEditQuantity" 
                                    type="button"
                                    class="text-gray-600 hover:text-gray-900">
                                Cancel
                            </button>
                        </div>
                    @else
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">Quantity: {{ $assembly->quantity }}</span>
                            <button wire:click.prevent="editQuantity" 
                                    type="button"
                                    class="text-indigo-600 hover:text-indigo-900">
                                Edit
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Assembly Totals -->
                <div class="text-sm text-gray-500">
                    <span class="font-medium">Total: ${{ number_format($totals['total_charge'], 2) }}</span>
                    <span class="text-xs ml-2">(Cost: ${{ number_format($totals['total_cost'], 2) }})</span>
                </div>
            </div>
        </div>

        <!-- Assembly Items -->
        <div class="mt-4 {{ $isCollapsed ? 'hidden' : '' }}">
            <livewire:shared.items-table 
                :parent-type="'assembly'"
                :parent-id="$assembly->id"
                :items="$assembly->items"
                wire:key="assembly-items-{{ $assembly->id }}"
            />
        </div>

        <!-- Assembly Summary (shown when collapsed) -->
        @if($isCollapsed)
            <div class="mt-2">
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-500">
                    <div>
                        <div>Material: ${{ number_format($totals['material_charge'], 2) }}</div>
                        <div>Labor: ${{ number_format($totals['labor_charge'], 2) }}</div>
                    </div>
                    <div class="text-right">
                        <div>Items: {{ $assembly->items->count() }}</div>
                        <div>Quantity: {{ $assembly->quantity }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div> 