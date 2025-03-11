<div class="bg-white rounded-lg shadow-sm p-4">
    @if($isEditing)
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <x-form.input-group
                    label="Name"
                    type="text"
                    name="name"
                    model="name"
                />

                <x-form.input-group
                    label="Quantity"
                    type="number"
                    name="quantity"
                    model="quantity"
                    step="1"
                />

                <x-form.input-group
                    label="Unit of Measure"
                    type="text"
                    name="unitOfMeasure"
                    model="unitOfMeasure"
                />

                <div>
                    <label class="block text-sm font-medium text-gray-700">Labor Rate</label>
                    <select wire:model="laborRateId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Labor Rate</option>
                        @foreach($laborRates as $rate)
                            <option value="{{ $rate->id }}">{{ $rate->name }} - ${{ number_format($rate->charge_rate, 2) }}/hr (Cost: ${{ number_format($rate->cost_rate, 2) }}/hr)</option>
                        @endforeach
                    </select>
                    @error('laborRateId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <x-form.input-group
                    label="Material Cost Rate"
                    type="number"
                    name="materialCostRate"
                    model="materialCostRate"
                    step="0.01"
                />

                <x-form.input-group
                    label="Material Charge Rate"
                    type="number"
                    name="materialChargeRate"
                    model="materialChargeRate"
                    step="0.01"
                />

                <x-form.input-group
                    label="Labor Units"
                    type="number"
                    name="laborUnits"
                    model="laborUnits"
                    step="0.01"
                />
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
        <div class="grid grid-cols-10 gap-4">
            <!-- Item Name & Description -->
            <div class="col-span-3">
                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                @if($item->description)
                    <div class="text-sm text-gray-500">{{ $item->description }}</div>
                @endif
            </div>

            <!-- Quantity -->
            <div class="col-span-1">
                <x-value-display
                    :primaryValue="$item->quantity"
                    :secondaryValue="$item->unit_of_measure"
                    label="Quantity"
                />
            </div>

            <!-- Material Rate -->
            <div class="col-span-1">
                <x-value-display
                    :primaryValue="$item->material_charge_rate"
                    :secondaryValue="$item->material_cost_rate"
                    label="Rate"
                    format="currency"
                />
            </div>

            <!-- Labor Units -->
            <div class="col-span-1">
                <x-value-display
                    :primaryValue="$item->labor_units"
                    label="Labor Units"
                />
            </div>

            <!-- Labor Rate -->
            <div class="col-span-1">
                <x-value-display
                    :primaryValue="$this->laborChargeRate"
                    :secondaryValue="$this->laborCostRate"
                    label="Labor Rate"
                    format="currency"
                />
            </div>

            <!-- Total -->
            <div class="col-span-1">
                <x-value-display
                    :primaryValue="$totals['total_charge']"
                    :secondaryValue="$totals['total_cost']"
                    label="Total"
                    format="currency"
                />
            </div>

            <!-- Actions -->
            <div class="col-span-2 flex justify-end space-x-2">
                <x-button variant="link" wire:click="startEditing">Edit</x-button>
                <x-button variant="danger" wire:click="delete">Delete</x-button>
            </div>
        </div>
    @endif
</div> 