<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubkriteriaController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(Request $request): View
    {
        $kriteriaId = $request->query('id_kriteria');
        $kriterias = [];
        $subkriterias = [];

        try {
            $kriterias = $this->akreditasiService->getKriteria()->json('data') ?? [];
            foreach ($kriterias as $krit) {
                if ($kriteriaId && (string) $krit['id'] !== (string) $kriteriaId) {
                    continue;
                }
                if (! empty($krit['subkriteria'])) {
                    foreach ($krit['subkriteria'] as $sub) {
                        $sub['kriteria'] = $krit;
                        $subkriterias[] = $sub;
                    }
                }
            }
        } catch (\Throwable) {
            $subkriterias = [];
        }

        return view('pages.superadmin.subkriteria', [
            'subkriterias' => $subkriterias,
            'kriterias' => $kriterias,
        ]);
    }
}
