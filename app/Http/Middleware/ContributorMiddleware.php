<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContributorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['contributor', 'admin'])) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
