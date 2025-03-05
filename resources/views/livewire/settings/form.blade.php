                    <div>
                        <label for="default_labor_markup" class="block text-sm font-medium text-gray-700">Default Labor Markup</label>
                        <input type="number" step="0.01" wire:model="default_labor_markup" id="default_labor_markup"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('default_labor_markup') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="primary_labor_rate" class="block text-sm font-medium text-gray-700">Primary Labor Rate ($/hr)</label>
                        <input type="number" step="0.01" wire:model="primary_labor_rate" id="primary_labor_rate"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        @error('primary_labor_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div> 