<x-guest-wide-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Tenant Information -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h2 class="text-lg font-medium text-gray-900">Tenant Information</h2>
                    <div class="mt-4">
                        <p><strong>Name:</strong> {{ $tenant->name }}</p>
                        <p><strong>Subdomain:</strong> {{ $tenant->subdomain }}</p>
                        <p><strong>Settings:</strong></p>
                        <ul class="list-disc ml-5">
                            @if(!empty($tenant->settings))
                                @foreach($tenant->settings as $key => $value)
                                    <li><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                                @endforeach
                            @else
                                <li>No settings defined</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Estimates Section -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h2 class="text-lg font-medium text-gray-900">Estimates</h2>
                    @if(!empty($estimates))
                        @foreach($estimates as $estimate)
                            <div class="mt-4 border-t pt-4">
                                <p><strong>Client:</strong> {{ $estimate->client_name }}</p>
                                <p><strong>Project:</strong> {{ $estimate->project_name }}</p>
                                <p><strong>Status:</strong> {{ ucfirst($estimate->status) }}</p>
                                <p><strong>Notes:</strong> {{ $estimate->notes }}</p>
                                
                                {{-- Direct Items Section --}}
                                @if(!empty($estimate->items) && $estimate->items->isNotEmpty())
                                    <div class="ml-4 mt-2">
                                        <p class="font-medium">Direct Materials</p>
                                        <ul class="list-disc ml-8">
                                            @foreach($estimate->items as $item)
                                                <li class="mb-4 border-b pb-4">
                                                    <div class="flex flex-col">
                                                        <div class="flex justify-between items-start">
                                                            <div class="font-medium">
                                                                {{ $item->name }}
                                                                <span class="text-gray-600">
                                                                    ({{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure }})
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4 text-sm mt-2">
                                                            <div class="grid grid-cols-2 gap-4">
                                                                <div>
                                                                    <p class="text-gray-600">Per Unit:</p>
                                                                    <div class="ml-2">
                                                                        <p>Materials (Cost): ${{ number_format($item->material_cost_rate, 2) }}</p>
                                                                        <p>Materials (Billed): ${{ number_format($item->material_charge_rate, 2) }}</p>
                                                                        <p>Labor Time: {{ number_format($item->labor_units, 1) }} mins</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if(!empty($estimate->packages) && $estimate->packages->isNotEmpty())
                                    {{-- Existing packaged estimates section --}}
                                    @foreach($estimate->packages as $package)
                                        <div class="ml-4 mt-2">
                                            <p class="font-medium">Package: {{ $package->name }}</p>
                                            @if(!empty($package->assemblies))
                                                @foreach($package->assemblies as $assembly)
                                                    <div class="ml-4 mt-1">
                                                        <p>Assembly: {{ $assembly->name }} (Qty: {{ number_format($assembly->quantity, 2) }})</p>
                                                        <ul class="list-disc ml-8">
                                                            @if(!empty($assembly->items))
                                                                @foreach($assembly->items as $item)
                                                                    <li class="mb-4 border-b pb-4">
                                                                        <div class="flex flex-col">
                                                                            <div class="flex justify-between items-start">
                                                                                <div class="font-medium">
                                                                                    {{ $item->name }} 
                                                                                    <span class="text-gray-600">
                                                                                        ({{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure }} 
                                                                                        × {{ number_format($assembly->quantity, 2) }} assemblies)
                                                                                    </span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="ml-4 text-sm mt-2">
                                                                                <div class="grid grid-cols-2 gap-4">
                                                                                    <div>
                                                                                        <p class="text-gray-600">Per Unit:</p>
                                                                                        <div class="ml-2">
                                                                                            <p>Materials (Cost): ${{ number_format($item->material_cost_rate, 2) }}</p>
                                                                                            <p>Materials (Billed): ${{ number_format($item->material_charge_rate, 2) }}</p>
                                                                                            <p>Labor Time: {{ number_format($item->labor_units, 1) }} mins</p>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </li>
                                                                @endforeach
                                                            @else
                                                                <li>No items found</li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p>No assemblies found</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    {{-- À la carte estimates --}}
                                    @if(!empty($estimate->assemblies) && $estimate->assemblies->isNotEmpty())
                                        @foreach($estimate->assemblies as $assembly)
                                            <div class="ml-4 mt-2">
                                                <p class="font-medium">{{ $assembly->name }} (Qty: {{ number_format($assembly->quantity, 2) }})</p>
                                                <ul class="list-disc ml-8">
                                                    @if(!empty($assembly->items) && $assembly->items->isNotEmpty())
                                                        @foreach($assembly->items as $item)
                                                            <li class="mb-4 border-b pb-4">
                                                                <div class="flex flex-col">
                                                                    <div class="flex justify-between items-start">
                                                                        <div class="font-medium">
                                                                            {{ $item->name }} 
                                                                            <span class="text-gray-600">
                                                                                ({{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure }} 
                                                                                × {{ number_format($assembly->quantity, 2) }} assemblies)
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="ml-4 text-sm mt-2">
                                                                        <div class="grid grid-cols-2 gap-4">
                                                                            <div>
                                                                                <p class="text-gray-600">Per Unit:</p>
                                                                                <div class="ml-2">
                                                                                    <p>Materials (Cost): ${{ number_format($item->material_cost_rate, 2) }}</p>
                                                                                    <p>Materials (Billed): ${{ number_format($item->material_charge_rate, 2) }}</p>
                                                                                    <p>Labor Time: {{ number_format($item->labor_units, 1) }} mins</p>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li>No items found</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>No assemblies found</p>
                                    @endif
                                @endif

                                <!-- Estimate Totals -->
                                <div class="mt-6 border-t pt-4">
                                    <h3 class="font-medium">Estimate Totals</h3>
                                    <div class="ml-4 mt-2 grid grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="font-medium">Materials</h4>
                                            <div class="ml-2">
                                                <p>Cost: ${{ number_format($estimate->total_material_cost, 2) }}</p>
                                                <p>Charge: ${{ number_format($estimate->total_material_charge, 2) }}</p>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-medium">Labor</h4>
                                            <div class="ml-2">
                                                <p>Time: {{ $estimate->formatted_labor_hours }}</p>
                                                <p>Cost: ${{ number_format($estimate->total_labor_cost, 2) }}</p>
                                                <p>Charge: ${{ number_format($estimate->total_labor_charge, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p><strong>Total Cost:</strong> ${{ number_format($estimate->total_cost, 2) }}</p>
                                                <p><strong>Total Charge:</strong> ${{ number_format($estimate->total_charge, 2) }}</p>
                                            </div>
                                            <div>
                                                <p><strong>Total Profit:</strong> ${{ number_format($estimate->total_profit, 2) }}</p>
                                                <p><strong>Profit Margin:</strong> {{ number_format($estimate->total_profit_percent, 1) }}%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>No estimates found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-guest-wide-layout>
