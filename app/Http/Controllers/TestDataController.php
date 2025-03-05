<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Estimate;
use App\Models\Item;
use App\Models\LaborRate;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;

class TestDataController extends Controller
{
    public function index()
    {
        $tenant = Tenant::first();
        
        if (!$tenant) {
            return view('test-data-empty', [
                'message' => 'No tenant data found. Please run database seeders first.'
            ]);
        }
        
        $users = User::where('tenant_id', $tenant->id)->get();
        $laborRates = LaborRate::where('tenant_id', $tenant->id)->get();
        $items = Item::where('tenant_id', $tenant->id)->get();
        $assemblies = Assembly::where('tenant_id', $tenant->id)->with('items')->get();
        $packages = Package::where('tenant_id', $tenant->id)->with('assemblies')->get();
        $estimates = Estimate::where('tenant_id', $tenant->id)
            ->with(['packages.assemblies.items', 'assemblies.items'])
            ->get();

        return view('test-data', [
            'tenant' => $tenant,
            'users' => $users,
            'laborRates' => $laborRates,
            'items' => $items,
            'assemblies' => $assemblies,
            'packages' => $packages,
            'estimates' => $estimates,
        ]);
    }
}
