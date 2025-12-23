<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->force_password_change) {
            // evita loop: permite acceder a la ruta de cambio y logout
            if (
                !$request->routeIs('password.force.edit') &&
                !$request->routeIs('password.force.update') &&
                !$request->routeIs('logout')
            ) {
                return redirect()->route('password.force.edit');
            }
        }

        return $next($request);
    }
}
