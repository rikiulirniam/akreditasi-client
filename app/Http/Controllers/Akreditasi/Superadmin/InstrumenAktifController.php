<?php

namespace App\Http\Controllers\Akreditasi\Superadmin;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\View\View;

class InstrumenAktifController extends Controller
{
    protected AkreditasiService $akreditasiService;

    public function __construct(AkreditasiService $akreditasiService)
    {
        $this->akreditasiService = $akreditasiService;
    }

    /**
     * Menampilkan hierarki instrumen aktif LAM-INFOKOM 2.1 TRK.
     */
    public function index(): View
    {
        $instrumen = null;
        $error = null;

        try {
            $response = $this->akreditasiService->getActiveStaticInstrumen();

            if ($response->successful()) {
                $instrumen = $response->json('data');
            } else {
                $error = $response->json('message')
                    ?? 'Gagal mengambil data instrumen aktif dari server.';
            }
        } catch (\Throwable $e) {
            $error = 'Tidak dapat terhubung ke server. Silakan coba lagi nanti.';
        }

        if (! is_array($instrumen) || empty($instrumen)) {
            try {
                $instrumen = $this->getActiveInstrumenHierarchyFromMasterData();
            } catch (\Throwable) {
                $instrumen = null;
            }

            if (is_array($instrumen) && ! empty($instrumen)) {
                $error = null;
            }
        }

        if (! is_array($instrumen) || empty($instrumen)) {
            $error = $error ?? 'Data hierarki instrumen aktif tidak ditemukan.';
        } else {
            $hierarchy = $instrumen['hierarchy'] ?? $instrumen['hierarki'] ?? null;

            if (! isset($instrumen['kriteria']) && is_array($hierarchy)) {
                $instrumen['kriteria'] = $hierarchy['kriteria'] ?? $hierarchy;
            }
        }

        return view('pages.superadmin.instrumen-aktif', compact(
            'instrumen',
            'error'
        ));
    }

    /**
     * Builds the active instrument hierarchy from the master data endpoints.
     *
     * @return array<string, mixed>|null
     */
    private function getActiveInstrumenHierarchyFromMasterData(): ?array
    {
        $versiResponse = $this->akreditasiService->getVersiInstrumen();
        $instrumenResponse = $this->akreditasiService->getInstrumens();

        if (! $versiResponse->successful() || ! $instrumenResponse->successful()) {
            return null;
        }

        $versis = $versiResponse->json('data');
        $instrumens = $instrumenResponse->json('data');

        if (! is_array($versis) || ! is_array($instrumens)) {
            return null;
        }

        foreach ($versis as $versi) {
            if (! is_array($versi) || ! $this->isVersiAktif($versi)) {
                continue;
            }

            foreach ($instrumens as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $versiId = $item['id_versi_instrumen']
                    ?? $item['versi_instrumen']['id']
                    ?? null;

                if ((string) $versiId !== (string) ($versi['id'] ?? '')) {
                    continue;
                }

                $instrumenId = $item['id'] ?? null;

                if ($instrumenId === null) {
                    continue;
                }

                $hierarchyResponse = $this->akreditasiService->getInstrumenHierarchy($instrumenId);

                if (! $hierarchyResponse->successful()) {
                    continue;
                }

                $hierarchy = $hierarchyResponse->json('data');

                if (! is_array($hierarchy) || empty($hierarchy)) {
                    continue;
                }

                try {
                    $detailResponse = $this->akreditasiService->getInstrumen($instrumenId);
                    $detail = $detailResponse->successful() ? $detailResponse->json('data') : [];
                } catch (\Throwable) {
                    $detail = [];
                }

                if (isset($hierarchy['instrumen']) && is_array($hierarchy['instrumen'])) {
                    $hierarchy = array_replace($hierarchy['instrumen'], $hierarchy);
                }

                return array_replace(is_array($detail) ? $detail : [], $hierarchy);
            }
        }

        return null;
    }

    /**
     * Determines whether a version is active itself or for at least one jenjang.
     *
     * @param  array<string, mixed>  $versi
     */
    private function isVersiAktif(array $versi): bool
    {
        if (array_key_exists('status_aktif', $versi)) {
            return filter_var($versi['status_aktif'], FILTER_VALIDATE_BOOLEAN);
        }

        foreach ($versi['jenjangs'] ?? [] as $jenjang) {
            if (is_array($jenjang) && ! empty($jenjang['pivot']['status_aktif'])) {
                return true;
            }
        }

        return false;
    }
}
