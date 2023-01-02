<?php

namespace KieranFYI\Roles\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::user();
        abort_if(is_null($user), 403);

        if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
            return $next($request);
        }

        abort(403);
    }
}
