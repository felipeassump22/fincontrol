<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TimezoneMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasCookie('client_tz')) {
            $tz = $request->cookie('client_tz');
            
            // Validar se o timezone é válido no PHP
            if (in_array($tz, timezone_identifiers_list())) {
                date_default_timezone_set($tz);
                config(['app.timezone' => $tz]);
            }
        }

        return $next($request);
    }
}
