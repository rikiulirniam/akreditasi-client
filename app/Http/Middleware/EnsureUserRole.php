<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userData = $request->session()->get('user', []);
        $rawRole = $userData['role'] ?? ($userData['role_name'] ?? ($userData['roles'][0]['name'] ?? ''));
        $currentRole = strtolower(trim((string) $rawRole));

        // Parse roles yang bisa berupa array argumen atau string comma-separated ("admin,kaprodi")
        $allowedRoles = [];
        foreach ($roles as $roleGroup) {
            foreach (explode(',', $roleGroup) as $r) {
                $trimmed = strtolower(trim($r));
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        // Superadmin dan Admin selalu memiliki izin
        if ($currentRole === 'admin' || $currentRole === 'superadmin' || in_array($currentRole, $allowedRoles, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.',
            ], 403);
        }

        abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
    }
}
