<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    /**
     * Display the login view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (session()->has('access_token')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email atau NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        try {
            $response = $this->akademikService->login($credentials);
            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false) === true) {
                $this->extractAndStoreAuthSession($body, $request, true);

                return redirect()->intended(route('dashboard'))->with('toast', [
                    'type' => 'success',
                    'title' => 'Login Berhasil',
                    'message' => $body['message'] ?? 'Selamat datang kembali!',
                ]);
            }

            $errorMessage = $body['message'] ?? 'Email atau password salah.';
            $errors = $body['errors'] ?? ['email' => $errorMessage];

            return back()->withInput($request->only('email', 'remember'))
                ->withErrors($errors)
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Login Gagal',
                    'message' => $errorMessage,
                ]);
        } catch (\Throwable $e) {
            return back()->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Gagal terhubung ke server akademik.'])
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Koneksi Terputus',
                    'message' => 'Tidak dapat terhubung ke server akademik. Silakan coba lagi nanti.',
                ]);
        }
    }

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->with(['hd' => 'polines.ac.id'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google and authenticate.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $email = strtolower((string) $googleUser->getEmail());
            $domain = (string) substr(strrchr($email, '@'), 1);

            // Validasi domain email harus *.polines.ac.id atau polines.ac.id
            if ($domain !== 'polines.ac.id' && ! str_ends_with($domain, '.polines.ac.id')) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Hanya email dengan domain *.polines.ac.id yang diizinkan masuk.'])
                    ->with('toast', [
                        'type' => 'error',
                        'title' => 'Domain Email Ditolak',
                        'message' => 'Silakan gunakan email resmi Polines (*.polines.ac.id).',
                    ]);
            }

            $response = $this->akademikService->loginWithGoogle([
                'google_id' => $googleUser->getId(),
                'email' => $email,
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false) === true) {
                $this->extractAndStoreAuthSession($body, $request, true);

                return redirect()->intended(route('dashboard'))->with('toast', [
                    'type' => 'success',
                    'title' => 'Login Berhasil',
                    'message' => $body['message'] ?? 'Selamat datang kembali!',
                ]);
            }

            $errorMessage = $body['message'] ?? 'Gagal login menggunakan akun Google.';

            return redirect()->route('login')
                ->withErrors(['email' => $errorMessage])
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Login Gagal',
                    'message' => $errorMessage,
                ]);
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Gagal autentikasi dengan Google.'])
                ->with('toast', [
                    'type' => 'error',
                    'title' => 'Autentikasi Gagal',
                    'message' => 'Terjadi kesalahan saat menghubungkan ke akun Google. Silakan coba lagi.',
                ]);
        }
    }

    /**
     * Refresh the authenticated access token via API.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $token = (string) $request->session()->get('access_token');

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan.',
            ], 401);
        }

        try {
            $response = $this->akademikService->refreshAuthToken($token);
            $body = $response->json();

            if ($response->successful() && ($body['success'] ?? false) === true) {
                $sessionData = $this->extractAndStoreAuthSession($body, $request, false);

                return response()->json([
                    'success' => true,
                    'message' => 'Token berhasil diperbarui.',
                    'expires_at' => $sessionData['token_expires_at'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $body['message'] ?? 'Gagal memperbarui token.',
            ], 400);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke server akademik gagal.',
            ], 500);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = (string) $request->session()->get('access_token');

        if (! empty($token)) {
            try {
                $this->akademikService->logoutAuth($token);
            } catch (\Throwable $e) {
                // Tetap lanjutkan logout lokal meskipun API server tidak merespons
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('toast', [
            'type' => 'success',
            'title' => 'Logout Berhasil',
            'message' => 'Anda telah berhasil keluar dari akun.',
        ]);
    }

    /**
     * Extract authorization data, permissions, expiration, and store in session.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function extractAndStoreAuthSession(array $body, Request $request, bool $regenerate = false): array
    {
        $userData = $body['data']['user'] ?? $request->session()->get('user', []);
        $authData = $body['data']['authorization'] ?? [];
        $accessToken = $authData['access_token'] ?? null;

        $permissions = [];
        $expiresAt = null;

        if ($accessToken && str_contains($accessToken, '.')) {
            $parts = explode('.', $accessToken);
            if (isset($parts[1])) {
                $jwtPayload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if (is_array($jwtPayload)) {
                    if (isset($jwtPayload['permissions']) && is_array($jwtPayload['permissions'])) {
                        $permissions = $jwtPayload['permissions'];
                    }
                    if (isset($jwtPayload['exp'])) {
                        $expiresAt = (int) $jwtPayload['exp'];
                    }
                }
            }
        }

        if (! $expiresAt && isset($authData['expires_in'])) {
            $expiresAt = time() + (int) $authData['expires_in'];
        }

        if ($regenerate) {
            $request->session()->regenerate();
        }

        $sessionData = [
            'access_token' => $accessToken,
            'token_type' => $authData['token_type'] ?? 'Bearer',
            'token_expires_at' => $expiresAt,
            'user' => $userData,
            'permissions' => ! empty($permissions) ? $permissions : $request->session()->get('permissions', []),
        ];

        $request->session()->put($sessionData);

        return $sessionData;
    }
}
