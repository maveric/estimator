<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Assemblies</h3>
            <div class="flex items-center space-x-4">
                <select wire:model="selectedAssembly" 
                        class="block w-64 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Select an assembly --</option>
                    @foreach($availableAssemblies as $availableAssembly)
                        <option value="{{ $availableAssembly->id }}">{{ $availableAssembly->name }}</option>
                    @endforeach
                </select>
                <input type="number" 
                       wire:model="assemblyQuantity" 
                       class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       min="1"
                       step="1"
                       value="1"
                       placeholder="Qty">
                <button type="button" 
                        wire:click="addAssembly" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Assembly
                </button>
            </div>
        </div>

        <!-- List of Assemblies -->
        <div class="space-y-4">
            @if(count($assemblies) > 0)
                @foreach($assemblies as $assemblyIndex => $assembly)
                    <livewire:shared.assembly-table 
                        :assembly="$assembly"
                        :parent-type="'estimate'"
                        :parent-id="$estimate->id"
                        :is-collapsed="in_array($assemblyIndex, $collapsedAssemblies)"
                        wire:key="assembly-{{ $assembly->id }}"
                    />
                @endforeach
            @else
                <div class="text-center py-4 text-gray-500">
                    No assemblies added to this estimate yet.
                </div>
            @endif
        </div>
    </div>
</div> 