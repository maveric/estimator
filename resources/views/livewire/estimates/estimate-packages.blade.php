<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Packages</h3>
        
        <!-- Add Package Form -->
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="selectedPackage" class="block text-sm font-medium text-gray-700">Select Package</label>
                <select wire:model="selectedPackage" 
                        id="selectedPackage" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">-- Select a package --</option>
                    @foreach($availablePackages as $package)
                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="packageQuantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <input type="number" 
                       wire:model="packageQuantity" 
                       id="packageQuantity" 
                       min="1" 
                       step="1" 
                       value="1"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            
            <div class="flex items-end">
                <button type="button" 
                        wire:click="addPackage" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Add Package
                </button>
            </div>
        </div>
        
        <!-- Packages List -->
        <div class="mt-6">
            @if(count($packages) > 0)
                <div class="space-y-6">
                    @foreach($packages as $package)
                        <livewire:shared.package-table 
                            :package="$package"
                            :parent-type="'estimate'"
                            :parent-id="$estimate->id"
                            wire:key="estimate-package-{{ $package->id }}"
                        />
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-gray-500">
                    No packages added to this estimate yet.
                </div>
            @endif
        </div>
    </div>
</div> 