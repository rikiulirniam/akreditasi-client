<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengarahkan ke file resources/views/akreditasi/superadmin/dashboard.blade.php
        return view('pages.superadmin.dashboard');
    }
}