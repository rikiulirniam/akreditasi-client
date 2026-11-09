<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\View\View;

class LembarKerjaController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(): View
    {
        $indikators = [];
        try {
            $indikators = $this->akreditasiService->getIndikators()->json('data') ?? [];
        } catch (\Throwable) {
            $indikators = [];
        }

        return view('pages.superadmin.lembar-kerja', [
            'indikators' => $indikators,
        ]);
    }
}
