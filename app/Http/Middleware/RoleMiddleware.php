<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request and check if user has one of the allowed roles.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if ($user->status === 'suspended') {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been suspended. Please contact platform support.',
            ]);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (! $user->hasRole($roles)) {
            abort(403, 'Unauthorized. You do not possess permissions to access this resource.');
        }

        return $next($request);
    }
}
