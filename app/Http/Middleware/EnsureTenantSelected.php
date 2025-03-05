<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has a current tenant
        if (!auth()->user()->current_tenant_id) {
            // Redirect to tenant selection page
            return redirect()->route('tenants.select')
                ->with('warning', 'Please select a tenant to continue.');
        }

        return $next($request);
    }
} 