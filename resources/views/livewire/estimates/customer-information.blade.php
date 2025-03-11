<!-- Customer Information Form -->
<div class="bg-white rounded-lg shadow-sm p-4">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <x-form.input-group
                label="Customer Name"
                type="text"
                name="customer_name"
                model="customer_name"
            />
        </div>
        <div>
            <x-form.input-group
                label="Email"
                type="email"
                name="customer_email"
                model="customer_email"
            />
        </div>
        <div>
            <x-form.input-group
                label="Phone"
                type="tel"
                name="customer_phone"
                model="customer_phone"
            />
        </div>
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea 
                wire:model="customer_address"
                name="customer_address"
                rows="2"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            ></textarea>
            @error('customer_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div>
            <x-form.input-group
                label="Valid Until"
                type="date"
                name="valid_until"
                model="valid_until"
            />
        </div>
    </div>
</div> 