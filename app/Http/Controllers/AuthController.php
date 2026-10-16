<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
                $userData = $body['data']['user'] ?? [];
                $authData = $body['data']['authorization'] ?? [];

                $request->session()->regenerate();
                $request->session()->put([
                    'access_token' => $authData['access_token'] ?? null,
                    'token_type' => $authData['token_type'] ?? 'Bearer',
                    'user' => $userData,
                ]);

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
     * Destroy an authenticated session.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('toast', [
            'type' => 'success',
            'title' => 'Logout Berhasil',
            'message' => 'Anda telah berhasil keluar dari akun.',
        ]);
    }
}
