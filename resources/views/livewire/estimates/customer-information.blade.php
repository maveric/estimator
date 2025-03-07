<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Customer Information</h3>
        <div class="mt-5 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-3">
                <label for="customer_name" class="block text-sm font-medium text-gray-700">Customer Name</label>
                <input type="text" 
                       wire:model.live="customer_name" 
                       id="customer_name" 
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-3">
                <label for="customer_email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" 
                       wire:model.live="customer_email" 
                       id="customer_email" 
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('customer_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-3">
                <label for="customer_phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" 
                       wire:model.live="customer_phone" 
                       id="customer_phone" 
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('customer_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-3">
                <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid Until</label>
                <input type="date" 
                       wire:model.live="valid_until" 
                       id="valid_until" 
                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                @error('valid_until') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="sm:col-span-6">
                <label for="customer_address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea wire:model.live="customer_address" 
                          id="customer_address" 
                          rows="3" 
                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                @error('customer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
</div> 