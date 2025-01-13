<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
