<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAkademikAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('access_token')) {
            $isApiBackedTendikRoute = $request->routeIs('tenaga-kependidikan.*', 'tenaga-pendidikan.*');

            if (app()->environment('local') && ! $isApiBackedTendikRoute) {
                $request->session()->put([
                    'access_token' => 'local-dev-token',
                    'user' => [
                        'nama' => 'Kuwat',
                        'email' => 'kuwat@example.com',
                        'role' => 'Kaprodi',
                    ],
                ]);

                return $next($request);
            }

            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Akses Dibatasi',
                'message' => 'Silakan login terlebih dahulu untuk mengakses halaman ini.',
            ]);
        }

        return $next($request);
    }
}
