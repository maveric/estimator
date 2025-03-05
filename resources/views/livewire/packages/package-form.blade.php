<div>
    <form wire:submit.prevent="save" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Package Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Description')" />
            <textarea wire:model="description" id="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3"></textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <input wire:model="is_active" id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <x-input-label for="is_active" :value="__('Active')" class="ml-2" />
        </div>

        <div>
            <x-input-label :value="__('Select Assemblies')" class="mb-2" />
            <x-input-error :messages="$errors->get('selectedAssemblies')" class="mt-2" />
            
            @if(count($availableAssemblies) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-2">
                    @foreach($availableAssemblies as $assembly)
                        <div class="border rounded-lg p-4 {{ in_array($assembly->id, $selectedAssemblies) ? 'bg-indigo-50 border-indigo-300' : 'bg-white' }}">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input 
                                        id="assembly-{{ $assembly->id }}" 
                                        type="checkbox" 
                                        wire:click="toggleAssembly({{ $assembly->id }})" 
                                        {{ in_array($assembly->id, $selectedAssemblies) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="assembly-{{ $assembly->id }}" class="font-medium text-gray-700">{{ $assembly->name }}</label>
                                    @if($assembly->description)
                                        <p class="text-gray-500">{{ $assembly->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                    No assemblies available. Please create assemblies first.
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('packages.index') }}'" class="mr-3">
                {{ __('Cancel') }}
            </x-secondary-button>
            <x-primary-button>
                {{ $mode === 'create' ? __('Create Package') : __('Update Package') }}
            </x-primary-button>
        </div>
    </form>
</div> 