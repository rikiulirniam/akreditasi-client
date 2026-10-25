<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard view.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->session()->get('user', [
            'nama' => 'Pengguna',
            'email' => '-',
            'role' => 'DOSEN',
        ]);

        if (str_contains(strtolower((string) ($user['role'] ?? $user['role_name'] ?? '')), 'reviewer')) {
            return redirect()->route('reviewer.dashboard');
        }

        return view('dashboard', [
            'user' => $user,
        ]);
    }
}
