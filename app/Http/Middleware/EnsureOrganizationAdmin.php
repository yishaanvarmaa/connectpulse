<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrganizationAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isOrganizationAdmin() || ! $user->organization_id) {
            abort(403);
        }

        if (! $user->organization?->isActive()) {
            return redirect()->route('login')
                ->with('error', 'Your organization account has been suspended.');
        }

        return $next($request);
    }
}
