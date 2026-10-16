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
            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Akses Dibatasi',
                'message' => 'Silakan login terlebih dahulu untuk mengakses halaman ini.',
            ]);
        }

        return $next($request);
    }
}
