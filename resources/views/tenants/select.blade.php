<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Tenant') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Please select a tenant to continue</h3>
                    
                    @if($tenants->isEmpty())
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                            <p>No tenants available. Please create a tenant first.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('tenants.set-current') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="tenant_id" class="block text-sm font-medium text-gray-700">Tenant</label>
                                <select id="tenant_id" name="tenant_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    @foreach($tenants as $tenant)
                                        <option value="{{ $tenant->id }}" {{ auth()->user()->current_tenant_id == $tenant->id ? 'selected' : '' }}>
                                            {{ $tenant->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex items-center justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-800 text-white font-medium rounded hover:bg-blue-900">
                                    Select Tenant
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 