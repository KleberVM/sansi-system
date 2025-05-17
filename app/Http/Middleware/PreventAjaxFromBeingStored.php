<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventAjaxFromBeingStored
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Si la petición NO es AJAX, sigue normal
        if (!$request->ajax()) {
            return $next($request);
        }

        // Si es AJAX, evita guardar la ruta
        $request->session()->forget('url.intended');

        return $next($request);
    }
}
