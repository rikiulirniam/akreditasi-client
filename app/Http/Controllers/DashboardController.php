<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard view.
     */
    public function index(Request $request): View
    {
        $user = $request->session()->get('user', [
            'nama' => 'Pengguna',
            'email' => '-',
            'role' => 'DOSEN',
        ]);

        return view('dashboard', [
            'user' => $user,
        ]);
    }
}
