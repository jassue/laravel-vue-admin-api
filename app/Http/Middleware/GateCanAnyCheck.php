<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class GateCanAnyCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param string $permissions eg: 'ADMIN_CREATE|ADMIN_UPDATE'
     * @return mixed
     */
    public function handle($request, Closure $next, string $permissions)
    {
        if (!Gate::any(explode('|', $permissions))) {
            throw new AuthorizationException();
        }
        return $next($request);
    }
}
