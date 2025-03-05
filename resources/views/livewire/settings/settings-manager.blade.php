<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">System Settings</h1>
    </div>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if ($message)
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ $message }}
            </div>
        @endif

        @if ($error)
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                {{ $error }}
            </div>
        @endif

        <!-- Settings Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button class="border-indigo-500 text-indigo-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Labor Rates
                </button>
                <!-- Add more tabs here as needed -->
            </nav>
        </div>

        <!-- Labor Rates Section -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Labor Rates</h3>
                
                <!-- Add New Labor Rate Form -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Add New Labor Rate</h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <label for="new-labor-rate-name" class="block text-xs font-medium text-gray-700">Name</label>
                            <input type="text" wire:model="newLaborRate.name" id="new-labor-rate-name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('newLaborRate.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="new-labor-rate-cost" class="block text-xs font-medium text-gray-700">Cost Rate ($/hr)</label>
                            <input type="number" step="0.01" wire:model="newLaborRate.cost_rate" id="new-labor-rate-cost" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('newLaborRate.cost_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="new-labor-rate-charge" class="block text-xs font-medium text-gray-700">Charge Rate ($/hr)</label>
                            <input type="number" step="0.01" wire:model="newLaborRate.charge_rate" id="new-labor-rate-charge" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('newLaborRate.charge_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="new-labor-rate-date" class="block text-xs font-medium text-gray-700">Effective From</label>
                            <input type="date" wire:model="newLaborRate.effective_from" id="new-labor-rate-date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('newLaborRate.effective_from') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" wire:click="addLaborRate" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Add Labor Rate
                        </button>
                    </div>
                </div>
                
                <!-- Labor Rates Table -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Rate ($/hr)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charge Rate ($/hr)</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Effective From</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($laborRates as $index => $rate)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    @if($editingLaborRateId === $rate['id'])
                                        <input type="text" wire:model="laborRates.{{ $index }}.name" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error("laborRates.$index.name") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        {{ $rate['name'] }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($editingLaborRateId === $rate['id'])
                                        <input type="number" step="0.01" wire:model="laborRates.{{ $index }}.cost_rate" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error("laborRates.$index.cost_rate") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        ${{ number_format($rate['cost_rate'], 2) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($editingLaborRateId === $rate['id'])
                                        <input type="number" step="0.01" wire:model="laborRates.{{ $index }}.charge_rate" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error("laborRates.$index.charge_rate") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        ${{ number_format($rate['charge_rate'], 2) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($editingLaborRateId === $rate['id'])
                                        <input type="date" wire:model="laborRates.{{ $index }}.effective_from" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        @error("laborRates.$index.effective_from") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        {{ \Carbon\Carbon::parse($rate['effective_from'])->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($rate['is_primary'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Primary
                                        </span>
                                    @else
                                        <button wire:click="setPrimaryLaborRate({{ $rate['id'] }})" class="text-blue-600 hover:text-blue-900">
                                            Set as Primary
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($editingLaborRateId === $rate['id'])
                                        <button type="button" wire:click="updateLaborRate({{ $rate['id'] }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Save
                                        </button>
                                        <button type="button" wire:click="cancelEdit" class="text-gray-600 hover:text-gray-900">
                                            Cancel
                                        </button>
                                    @else
                                        <button type="button" wire:click="editLaborRate({{ $rate['id'] }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            Edit
                                        </button>
                                        <button type="button" wire:click="deleteLaborRate({{ $rate['id'] }})" wire:confirm="Are you sure you want to delete this labor rate?" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No labor rates found. Add your first one above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 