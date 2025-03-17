<div>
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Labor Rates</h1>
                <p class="mt-2 text-sm text-gray-700">
                    Manage your labor rates for cost and price calculations
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <x-button href="{{ route('labor-rates.create') }}" wire:navigate>
                    Add Labor Rate
                </x-button>
            </div>
        </div>

        <div class="mt-8 flow-root">
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full py-2 align-middle">
                    <div class="relative">
                        <div class="mb-4 flex gap-x-4">
                            <div class="flex-1">
                                <x-input 
                                    type="search"
                                    wire:model.live.debounce.300ms="search"
                                    placeholder="Search labor rates..."
                                    class="w-full"
                                />
                            </div>
                            <div class="flex items-center">
                                <x-checkbox 
                                    wire:model.live="showInactive"
                                    id="show-inactive"
                                />
                                <x-label for="show-inactive" class="ml-2">
                                    Show Inactive
                                </x-label>
                            </div>
                        </div>

                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Cost Rate</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price Rate</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Default</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($laborRates as $rate)
                                        <tr wire:key="{{ $rate->id }}">
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                                {{ $rate->name }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                ${{ number_format($rate->cost_rate, 2) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                ${{ number_format($rate->price_rate, 2) }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                @if ($rate->is_default)
                                                    <x-badge color="green">Default</x-badge>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <button
                                                    wire:click="toggleStatus({{ $rate->id }})"
                                                    type="button"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 {{ $rate->is_active ? 'bg-indigo-600' : 'bg-gray-200' }}"
                                                    role="switch"
                                                    aria-checked="{{ $rate->is_active ? 'true' : 'false' }}"
                                                >
                                                    <span
                                                        aria-hidden="true"
                                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $rate->is_active ? 'translate-x-5' : 'translate-x-0' }}"
                                                    ></span>
                                                </button>
                                            </td>
                                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                <x-button href="{{ route('labor-rates.edit', $rate) }}" wire:navigate size="sm">
                                                    Edit
                                                </x-button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-4 text-sm text-gray-500 text-center">
                                                No labor rates found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $laborRates->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
