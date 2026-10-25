<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengarahkan ke file resources/views/akreditasi/superadmin/dashboard.blade.php
        return view('pages.superadmin.dashboard');
    }
}
