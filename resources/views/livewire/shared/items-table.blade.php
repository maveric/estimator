<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Items</h3>
        
        <!-- Add Item Form -->
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="selectedItem" class="block text-sm font-medium text-gray-700">Select Item</label>
                <select wire:model="selectedItem" 
                        id="selectedItem" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Select an item --</option>
                    @foreach($availableItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit_of_measure ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="itemQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" 
                       wire:model="itemQuantity" 
                       id="itemQuantity" 
                       min="1" 
                       step="1" 
                       value="1"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="flex items-end">
                <button type="button" 
                        wire:click="addItem" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Material</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Labor</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($items as $index => $item)
                            <tr>
                                <td class="border px-4 py-2 text-sm">{{ $item->name }}</td>
                                <td class="border px-4 py-2 text-sm">
                                    @if($editingItemIndex === $index)
                                        <input type="number" 
                                               wire:model="editingItemQuantity" 
                                               class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                               min="1"
                                               step="1"
                                               value="1">
                                    @else
                                        {{ $item->quantity }}
                                    @endif
                                </td>
                                <td class="border px-4 py-2 text-sm">{{ $item->unit_of_measure ?? 'N/A' }}</td>
                                <td class="border px-4 py-2 text-sm text-right">
                                    <div>${{ number_format($item->total_material_charge, 2) }}</div>
                                    <div class="text-xs text-gray-500">${{ number_format($item->material_charge_rate, 2) }}/unit</div>
                                </td>
                                <td class="border px-4 py-2 text-sm text-right">
                                    <div>${{ number_format($item->total_labor_charge, 2) }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->labor_units }} mins</div>
                                </td>
                                <td class="border px-4 py-2 text-sm text-right">
                                    <div>${{ number_format($item->total_charge, 2) }}</div>
                                    <div class="text-xs text-gray-500">Cost: ${{ number_format($item->total_cost, 2) }}</div>
                                </td>
                                <td class="border px-4 py-2 text-sm">
                                    <div class="flex items-center">
                                        @if($editingItemIndex === $index)
                                            <button type="button" 
                                                    wire:click="updateItem" 
                                                    class="text-green-600 hover:text-green-900">
                                                Save
                                            </button>
                                            <button type="button" 
                                                    wire:click="cancelEditItem" 
                                                    class="ml-3 text-gray-600 hover:text-gray-900">
                                                Cancel
                                            </button>
                                        @else
                                            <button type="button" 
                                                    wire:click="editItem({{ $index }})" 
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                Edit
                                            </button>
                                            <button type="button" 
                                                    wire:click="removeItem({{ $index }})"
                                                    wire:confirm="Are you sure you want to remove this item? This action cannot be undone."
                                                    class="ml-3 text-red-600 hover:text-red-900">
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
                    No items added yet.
                </div>
            @endif
        </div>
    </div>
</div> 