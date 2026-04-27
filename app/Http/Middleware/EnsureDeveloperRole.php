<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloperRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web') ?? $request->user();

        if (! $this->isDeveloper($user)) {
            abort(403, 'Akses dokumentasi API hanya untuk role developer.');
        }

        return $next($request);
    }

    private function isDeveloper(mixed $user): bool
    {
        if (! is_object($user)) {
            return false;
        }

        $role = strtolower(trim((string) data_get($user, 'role', '')));

        if ($role === 'developer' || $role === 'super_admin') {
            return true;
        }

        if (! method_exists($user, 'hasRole')) {
            return false;
        }

        try {
            return (bool) $user->hasRole('developer') || (bool) $user->hasRole('super_admin');
        } catch (\Throwable) {
            return false;
        }
    }
}
