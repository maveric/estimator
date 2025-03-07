<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Estimate Details</h3>
        <div class="mt-5 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select wire:model.live.debounce.300ms="status" 
                        id="status" 
                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="approved">Approved</option>
                    <option value="declined">Declined</option>
                </select>
                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="markup_percentage" class="block text-sm font-medium text-gray-700">Markup (%)</label>
                <input type="number" 
                       wire:model.live.debounce.300ms="markup_percentage" 
                       id="markup_percentage" 
                       min="0" 
                       max="100" 
                       step="0.01" 
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('markup_percentage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="discount_percentage" class="block text-sm font-medium text-gray-700">Discount (%)</label>
                <input type="number" 
                       wire:model.live.debounce.300ms="discount_percentage" 
                       id="discount_percentage" 
                       min="0" 
                       max="100" 
                       step="0.01" 
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('discount_percentage') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-6">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea wire:model.live.debounce.1000ms="notes" 
                          id="notes" 
                          rows="3" 
                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
</div> 