<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $mode === 'edit' ? 'Edit Assembly' : 'Create New Assembly' }}
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
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input wire:model="name" type="text" id="name" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <label for="categories" class="block text-sm font-medium text-gray-700">Categories</label>
                            <div class="mt-2 grid grid-cols-2 gap-2">
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

                        <div class="col-span-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="description" id="description" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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

            <!-- Add a cost summary section -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Assembly Cost Summary</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500">Cost</h4>
                            <div class="mt-1">
                                <p class="text-lg font-semibold text-gray-900">${{ number_format($totalCost, 2) }}</p>
                                <p class="text-sm text-gray-500">Parts: ${{ number_format($totalPartsCost, 2) }}</p>
                                <p class="text-sm text-gray-500">Labor: ${{ number_format($laborCost, 2) }}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500">Charge</h4>
                            <div class="mt-1">
                                <p class="text-lg font-semibold text-gray-900">${{ number_format($totalCharge, 2) }}</p>
                                <p class="text-sm text-gray-500">Parts: ${{ number_format($totalPartsCharge, 2) }}</p>
                                <p class="text-sm text-gray-500">Labor: ${{ number_format($laborCharge, 2) }}</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-500">Labor</h4>
                            <div class="mt-1">
                                <p class="text-lg font-semibold text-gray-900">{{ number_format($totalLaborUnits, 2) }} minutes</p>
                                <p class="text-sm text-gray-500">{{ number_format($totalLaborUnits / 60, 2) }} hours</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assembly Items Section -->
            <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900">Assembly Items</h3>
                    
                    <!-- Add Item Form -->
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="selectedItem" class="block text-sm font-medium text-gray-700">Select Item</label>
                            <select wire:model="selectedItem" id="selectedItem" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">-- Select an item --</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit_of_measure ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" wire:model="quantity" id="quantity" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div class="flex items-end">
                            <button type="button" wire:click="addItem" class="px-4 py-2 bg-blue-800 text-white font-medium rounded hover:bg-blue-900">
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
                                        <th class="px-4 py-2 text-left">Item</th>
                                        <th class="px-4 py-2 text-left">Quantity</th>
                                        <th class="px-4 py-2 text-left">Unit</th>
                                        <th class="px-4 py-2 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($items as $index => $item)
                                        <tr>
                                            <td class="border px-4 py-2">
                                                <a href="{{ route('items.edit', $item['item_id']) }}" class="text-blue-800 hover:text-blue-900 hover:underline font-semibold">
                                                    {{ $item['name'] }}
                                                </a>
                                            </td>
                                            <td class="border px-4 py-2">
                                                @if($editingItemIndex === $index)
                                                    <input 
                                                        type="number" 
                                                        wire:model="editingItemQuantity" 
                                                        class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                        min="1"
                                                    >
                                                @else
                                                    {{ $item['quantity'] }}
                                                @endif
                                            </td>
                                            <td class="border px-4 py-2">{{ $item['unit_of_measure'] ?? 'N/A' }}</td>
                                            <td class="border px-4 py-2">
                                                <div class="flex items-center">
                                                    @if($editingItemIndex === $index)
                                                        <button 
                                                            type="button" 
                                                            wire:click="updateItem" 
                                                            class="text-green-600 hover:text-green-900 font-semibold"
                                                        >
                                                            Save
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="cancelEdit" 
                                                            class="ml-3 text-gray-600 hover:text-gray-900 font-semibold"
                                                        >
                                                            Cancel
                                                        </button>
                                                    @else
                                                        <button 
                                                            type="button" 
                                                            wire:click="editItem({{ $index }})" 
                                                            class="text-blue-800 hover:text-blue-900 font-semibold"
                                                        >
                                                            Edit
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="removeItem({{ $index }})" 
                                                            class="ml-3 text-red-600 hover:text-red-900 font-semibold"
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
                                No items added to this assembly yet.
                            </div>
                        @endif
                        @error('items') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-6 flex justify-end">
                <button type="button" wire:click="cancel" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900 mr-3">
                    Cancel
                </button>
                
                <button type="submit" class="px-4 py-2 bg-blue-800 text-white font-medium rounded hover:bg-blue-900">
                    {{ $mode === 'edit' ? 'Update Assembly' : 'Create Assembly' }}
                </button>
            </div>
        </form>
    </div>
</div>
