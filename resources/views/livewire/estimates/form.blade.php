<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">
            Estimate
        </h1>
    </div>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <form wire:submit.prevent="save">
            @if (session()->has('message'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('message') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Customer Information Component -->
            <livewire:estimates.customer-information :estimate="$estimate" />

            <!-- Estimate Details Component -->
            <livewire:estimates.estimate-details :estimate="$estimate" />

            <!-- Packages Component -->
            <livewire:estimates.estimate-packages :estimate="$estimate" />
            
            <!-- Assemblies Section -->
            <div class="mt-6">
                <livewire:estimates.estimate-assemblies 
                    :estimate="$estimate"
                    :assemblies="$assemblies"
                    wire:key="estimate-assemblies-{{ $estimate->id }}"
                />
            </div>

            <!-- Direct Items Section -->
            <div class="mt-6">
                <livewire:shared.items-table 
                    :parent-type="'estimate'"
                    :parent-id="$estimate->id"
                    :items="$items"
                    wire:key="estimate-items-{{ $estimate->id }}"
                />
            </div>


            <!-- Totals Section -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Totals
                        <span wire:loading wire:target="calculateTotals" class="ml-2 text-sm text-gray-500">Updating...</span>
                    </h3>
                    <div class="mt-5 space-y-4">
                        <!-- Base Totals -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal:</span>
                            <span class="text-gray-900 font-medium" wire:loading.class="opacity-50" wire:target="calculateTotals">
                                ${{ number_format($subtotalCharge, 2) }}
                            </span>
                        </div>

                        <!-- Adjustments -->
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Markup Amount:</span>
                                <span class="text-gray-900" wire:loading.class="opacity-50" wire:target="calculateTotals">
                                    ${{ number_format($markupAmount, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount Amount:</span>
                                <span class="text-red-600" wire:loading.class="opacity-50" wire:target="calculateTotals">
                                    -${{ number_format($discountAmount, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Final Totals -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-900">Total Charge:</span>
                                <span class="font-medium text-gray-900" wire:loading.class="opacity-50" wire:target="calculateTotals">
                                    ${{ number_format($totalCharge, 2) }}
                                </span>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Total Cost:</span>
                                    <span wire:loading.class="opacity-50" wire:target="calculateTotals">
                                        ${{ number_format($totalCost, 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Profit:</span>
                                    <span class="text-green-600" wire:loading.class="opacity-50" wire:target="calculateTotals">
                                        ${{ number_format($totalCharge - $totalCost, 2) }} 
                                        <span class="text-gray-500">({{ $totalCost > 0 ? number_format(($totalCharge - $totalCost) / $totalCost * 100, 2) : 0 }}%)</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3">
                <button type="button" wire:click="cancel" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Estimate
                </button>
            </div>
        </form>
    </div>
</div> 