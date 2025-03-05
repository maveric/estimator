<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants for selection.
     */
    public function select()
    {
        $tenants = Tenant::all();
        
        return view('tenants.select', compact('tenants'));
    }
    
    /**
     * Set the current tenant for the user.
     */
    public function setCurrent(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);
        
        $user = auth()->user();
        
        // Check if the current_tenant_id column exists in the users table
        if (!Schema::hasColumn('users', 'current_tenant_id')) {
            // If the column doesn't exist, show a message to run migrations
            return redirect()->route('dashboard')
                ->with('error', 'The current_tenant_id column does not exist in the users table. Please run migrations first: php artisan migrate');
        }
        
        try {
            $user->current_tenant_id = $request->tenant_id;
            $user->save();
            
            return redirect()->route('dashboard')->with('success', 'Tenant selected successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error setting tenant: ' . $e->getMessage());
        }
    }
} 