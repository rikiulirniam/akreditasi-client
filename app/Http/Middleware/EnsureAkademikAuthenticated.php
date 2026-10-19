<?php

namespace App\Http\Middleware;

use App\Services\AkademikService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAkademikAuthenticated
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('access_token')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu.',
                ], 401);
            }

            return redirect()->route('login')->with('toast', [
                'type' => 'warning',
                'title' => 'Akses Dibatasi',
                'message' => 'Silakan login terlebih dahulu untuk mengakses halaman ini.',
            ]);
        }

        $accessToken = (string) $request->session()->get('access_token');
        $expiresAt = $request->session()->get('token_expires_at');

        // Jika token_expires_at belum ada di session, ekstrak dari payload JWT
        if (! $expiresAt && str_contains($accessToken, '.')) {
            $parts = explode('.', $accessToken);
            if (isset($parts[1])) {
                $jwtPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (is_array($jwtPayload) && isset($jwtPayload['exp'])) {
                    $expiresAt = (int) $jwtPayload['exp'];
                    $request->session()->put('token_expires_at', $expiresAt);
                }
            }
        }

        // Cek apakah token mau habis (misalnya sisa <= 5 menit / 300 detik) atau sudah lewat
        if ($expiresAt && ! $request->is('auth/refresh-token')) {
            $remainingSeconds = $expiresAt - time();

            // Jika sisa waktu <= 300 detik (5 menit), hit refresh API secara proaktif
            if ($remainingSeconds <= 300) {
                try {
                    $refreshResponse = $this->akademikService->refreshAuthToken($accessToken);
                    $body = $refreshResponse->json();

                    if ($refreshResponse->successful() && ($body['success'] ?? false) === true) {
                        $authData = $body['data']['authorization'] ?? [];
                        $newAccessToken = $authData['access_token'] ?? null;

                        if ($newAccessToken) {
                            $userData = $body['data']['user'] ?? $request->session()->get('user', []);
                            $newExpiresAt = null;
                            $permissions = [];

                            if (str_contains($newAccessToken, '.')) {
                                $parts = explode('.', $newAccessToken);
                                if (isset($parts[1])) {
                                    $jwtPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                                    if (is_array($jwtPayload)) {
                                        if (isset($jwtPayload['permissions']) && is_array($jwtPayload['permissions'])) {
                                            $permissions = $jwtPayload['permissions'];
                                        }
                                        if (isset($jwtPayload['exp'])) {
                                            $newExpiresAt = (int) $jwtPayload['exp'];
                                        }
                                    }
                                }
                            }

                            if (! $newExpiresAt && isset($authData['expires_in'])) {
                                $newExpiresAt = time() + (int) $authData['expires_in'];
                            }

                            $request->session()->put([
                                'access_token' => $newAccessToken,
                                'token_type' => $authData['token_type'] ?? 'Bearer',
                                'token_expires_at' => $newExpiresAt,
                                'user' => $userData,
                                'permissions' => ! empty($permissions) ? $permissions : $request->session()->get('permissions', []),
                            ]);
                        }
                    } elseif ($remainingSeconds <= 0) {
                        // Token sudah kadaluarsa dan refresh gagal
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        if ($request->expectsJson() || $request->is('api/*')) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Sesi login Anda telah berakhir. Silakan login kembali.',
                            ], 401);
                        }

                        return redirect()->route('login')->with('toast', [
                            'type' => 'warning',
                            'title' => 'Sesi Berakhir',
                            'message' => 'Sesi login Anda telah berakhir. Silakan login kembali.',
                        ]);
                    }
                } catch (\Throwable $e) {
                    if ($remainingSeconds <= 0) {
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        if ($request->expectsJson() || $request->is('api/*')) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Sesi login Anda terputus.',
                            ], 401);
                        }

                        return redirect()->route('login')->with('toast', [
                            'type' => 'error',
                            'title' => 'Sesi Terputus',
                            'message' => 'Tidak dapat memperbarui sesi Anda. Silakan login kembali.',
                        ]);
                    }
                }
            }
        }

        return $next($request);
    }
}
