<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    <div class="relative">
        <x-text-input
            type="text"
            wire:model.live="search"
            @focus="open = true"
            placeholder="Search items by name or SKU..."
            class="w-full"
        />
    </div>

    <!-- Dropdown -->
    <div
        x-show="open"
        @click.away="open = false"
        class="absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg"
        x-cloak
    >
        <ul class="py-1 overflow-auto text-base leading-6 rounded-md shadow-xs max-h-60 focus:outline-none sm:text-sm sm:leading-5">
            @forelse($this->items as $item)
                <li
                    wire:key="{{ $item->id }}"
                    wire:click="selectItem({{ $item->id }})"
                    class="relative py-2 pl-3 text-gray-900 cursor-pointer select-none hover:bg-indigo-600 hover:text-white"
                >
                    <div class="flex items-center">
                        <span class="block font-normal truncate">
                            {{ $item->name }}
                            @if($item->sku)
                                <span class="text-sm opacity-75">({{ $item->sku }})</span>
                            @endif
                        </span>
                    </div>

                    @if($item->id === $selectedItemId)
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    @endif
                </li>
            @empty
                <li class="relative py-2 pl-3 text-gray-900 cursor-default select-none">
                    No items found
                </li>
            @endforelse
        </ul>
    </div>
</div>
