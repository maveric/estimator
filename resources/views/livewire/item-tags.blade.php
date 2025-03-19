<div class="w-full">
    <div class="flex flex-wrap gap-2 mb-2">
        @foreach($tags as $tag)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ $tag['name'] }}
                <button type="button" wire:click="removeTag('{{ $tag['name'] }}')" class="ml-1 inline-flex items-center p-0.5 text-blue-400 hover:text-blue-600">
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </span>
        @endforeach
    </div>

    <div class="relative">
        <div class="flex">
            <input
                type="text"
                wire:model.live="newTag"
                wire:keydown.enter.prevent="addTag"
                wire:keydown.comma.prevent="addTag"
                placeholder="Add tags (comma-separated)..."
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"
            >
            <button
                type="button"
                wire:click="addTag"
                class="ml-2 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Add
            </button>
        </div>

        @if($showSuggestions && count($suggestions) > 0)
            <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                @foreach($suggestions as $suggestion)
                    <button
                        type="button"
                        wire:click="selectSuggestion('{{ $suggestion['name'] }}')"
                        class="w-full text-left px-4 py-2 text-sm text-gray-900 hover:bg-blue-50"
                    >
                        {{ $suggestion['name'] }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</div>
