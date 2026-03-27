<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfApiAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('api')->check()) {
            return redirect()->route('dashboard.page');
        }

        return $next($request);
    }
}
