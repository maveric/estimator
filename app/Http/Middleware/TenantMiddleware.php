<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
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
            // Redirect to tenant selection or set a default tenant
            // For now, we'll just return an error
            abort(403, 'No tenant selected. Please select a tenant first.');
        }

        return $next($request);
    }
} 