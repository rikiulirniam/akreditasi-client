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
            'nama' => 'Superadmin',
            'email' => '-',
            'role' => 'SUPERADMIN',
        ]);

        // Default KPI data matching Figma design
        $kpi = [
            'mahasiswa_aktif' => 425,
            'mahasiswa_baru' => 112,
            'lulus_tepat_waktu' => 87,
            'rata_rata_ipk' => 3.78,
            'prestasi_nasional' => 97,
            'prestasi_internasional' => 34,
        ];

        // Ringkasan Tri Dharma matching Figma design
        $tridharma = [
            'dtps' => 20,
            'dosen_s3' => 8,
            'lektor_kepala' => 3,
            'penelitian' => 35,
            'publikasi_scopus' => 14,
            'hki' => 8,
            'pkm' => 22,
            'kerjasama_aktif' => 17,
        ];

        // Tren data 2022 - 2026
        $tren = [
            'years' => ['2022', '2023', '2024', '2025', '2026'],
            'ipk' => [3.30, 3.40, 3.52, 3.52, 3.52],
            'kelulusan' => [73, 88, 77, 87, 85],
        ];

        // Prestasi Mahasiswa Terbaru
        $prestasiTerbaru = collect(range(1, 6))->map(fn (int $no): array => [
            'no' => $no,
            'nama' => 'Gerald Manunggal Fauzi',
            'lomba' => 'UI/UX Miaw Miaw',
            'tingkat' => 'Internasional',
            'tahun' => '2026',
        ])->all();

        return view('dashboard', [
            'user' => $user,
            'kpi' => $kpi,
            'tridharma' => $tridharma,
            'tren' => $tren,
            'prestasiTerbaru' => $prestasiTerbaru,
        ]);
    }
}
