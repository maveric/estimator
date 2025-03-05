<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAccessMiddleware
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

        $user = auth()->user();

        // For admin users, require current_tenant_id to be set
        if ($user->isAdmin()) {
            if (!$user->current_tenant_id) {
                return redirect()->route('tenants.select')
                    ->with('warning', 'Please select a tenant to continue.');
            }
        } else {
            // For non-admin users, lock them to their tenant_id
            if ($user->current_tenant_id !== $user->tenant_id) {
                $user->current_tenant_id = $user->tenant_id;
                $user->save();
            }
        }

        return $next($request);
    }
} 