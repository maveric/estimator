<div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">
                Estimate #{{ $estimate->id }}
            </h1>
            <div class="flex space-x-2">
                <a href="{{ route('estimates.edit', $estimate) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit
                </a>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Export
                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <div class="py-1" role="none">
                            <button wire:click="exportPdf" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">Export as PDF</button>
                            <button wire:click="exportCsv" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">Export as CSV</button>
                        </div>
                    </div>
                </div>
                <button wire:click="convertToInvoice" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Convert to Invoice
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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

        <!-- Estimate Header -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Estimate Information</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Created on {{ $estimate->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($estimate->status === 'draft') bg-gray-100 text-gray-800
                        @elseif($estimate->status === 'sent') bg-blue-100 text-blue-800
                        @elseif($estimate->status === 'approved') bg-green-100 text-green-800
                        @elseif($estimate->status === 'declined') bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($estimate->status) }}
                    </span>
                    <div class="mt-2">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-sm text-gray-600 hover:text-gray-900">
                                Change Status
                                <svg class="inline-block ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                                <div class="py-1" role="none">
                                    <button wire:click="changeStatus('draft')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">Draft</button>
                                    <button wire:click="changeStatus('sent')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">Sent</button>
                                    <button wire:click="changeStatus('approved')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">Approved</button>
                                    <button wire:click="changeStatus('declined')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" role="menuitem">Declined</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $estimate->customer_name }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $estimate->customer_email ?: 'N/A' }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $estimate->customer_phone ?: 'N/A' }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $estimate->customer_address ?: 'N/A' }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $estimate->valid_until ? \Carbon\Carbon::parse($estimate->valid_until)->format('M d, Y') : 'N/A' }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">Notes</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $estimate->notes ?: 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Packages Section -->
        @if ($estimate->packages->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Packages</h3>
                <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Charge</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($estimate->packages as $package)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $package->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $package->description }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $package->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-right">${{ number_format($package->total_cost, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-right">${{ number_format($package->total_charge, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Assemblies -->
        @if ($estimate->assemblies->isNotEmpty())
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Assemblies</h3>
                <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Charge</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($estimate->assemblies as $assembly)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $assembly->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $assembly->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $assembly->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($assembly->package_id)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $assembly->package->name ?? 'Package' }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${{ number_format($assembly->total_cost, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">${{ number_format($assembly->total_charge, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Items Section -->
        @if($estimate->items->count() > 0)
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Items</h3>
                <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($estimate->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->item->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->item->unit_of_measure ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($item->original_charge_rate, 2) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-right">${{ number_format($item->quantity * $item->original_charge_rate, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900">Items</h3>
                <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 text-sm text-gray-500">No items added to this estimate.</div>
                </div>
            </div>
        @endif

        <!-- Estimate Summary -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900">Estimate Summary</h3>
            <div class="mt-4 bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-6 py-5">
                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal:</span>
                            <span class="text-gray-900 font-medium">${{ number_format($subtotalCharge, 2) }}</span>
                        </div>
                        @if($estimate->markup_percentage > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Markup ({{ number_format($estimate->markup_percentage, 2) }}%):</span>
                                <span class="text-gray-900">${{ number_format($markupAmount, 2) }}</span>
                            </div>
                        @endif
                        @if($estimate->discount_percentage > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount ({{ number_format($estimate->discount_percentage, 2) }}%):</span>
                                <span class="text-red-600">-${{ number_format($discountAmount, 2) }}</span>
                            </div>
                        @endif
                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-gray-900">Total:</span>
                                <span class="font-medium text-gray-900">${{ number_format($totalCharge, 2) }}</span>
                            </div>
                            <div class="mt-4 space-y-2">
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Cost:</span>
                                    <span>${{ number_format($totalCost, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Profit:</span>
                                    <span class="text-green-600">${{ number_format($totalCharge - $totalCost, 2) }} 
                                        <span class="text-gray-500">({{ $totalCost > 0 ? number_format(($totalCharge - $totalCost) / $totalCost * 100, 2) : 0 }}%)</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version History -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Version History</h3>
                <button wire:click="toggleVersionHistory" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ $showVersionHistory ? 'Hide History' : 'Show History' }}
                </button>
            </div>
            @if($showVersionHistory)
                <div class="border-t border-gray-200">
                    @if($estimate->versions->count() > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($estimate->versions as $version)
                                <li class="px-4 py-4 flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Version {{ $version->version_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $version->created_at->format('M d, Y H:i:s') }}</p>
                                    </div>
                                    <div>
                                        <button wire:click="viewVersion({{ $version->id }})" class="text-sm text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                                        <button wire:click="revertToVersion({{ $version->id }})" wire:confirm="Are you sure you want to revert to version {{ $version->version_number }}?" class="text-sm text-red-600 hover:text-red-900">Revert</button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-5 text-sm text-gray-500">No version history available.</div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Version View Modal -->
        @if($selectedVersion)
            <div class="fixed inset-0 overflow-y-auto z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Version {{ $selectedVersion->version_number }} - {{ $selectedVersion->created_at->format('M d, Y H:i:s') }}
                                    </h3>
                                    <div class="mt-4 max-h-96 overflow-y-auto">
                                        <pre class="text-xs text-gray-700 bg-gray-50 p-4 rounded overflow-x-auto">{{ json_encode($selectedVersion->snapshot, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button wire:click="closeVersionView" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Close
                            </button>
                            <button wire:click="revertToVersion({{ $selectedVersion->id }})" wire:confirm="Are you sure you want to revert to version {{ $selectedVersion->version_number }}?" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Revert to This Version
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div> 