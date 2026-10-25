<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService
    ) {}

    public function index(): View
    {
        $projects = [];
        $lembagas = [];
        $versis = [];

        try {
            $projects = $this->akreditasiService->getProjects()->json('data') ?? [];
            $lembagas = $this->akreditasiService->getLembagaAkreditasi()->json('data') ?? [];
            $versis = $this->akreditasiService->getVersiInstrumen()->json('data') ?? [];
        } catch (\Throwable) {
            $projects = [];
        }

        $currentYear = (int) date('Y');
        $tahunBerjalanCount = 0;
        foreach ($projects as $project) {
            $targetYear = (int) ($project['tahun_target_submit'] ?? $project['tahun_akreditasi'] ?? 0);
            if ($targetYear === $currentYear) {
                $tahunBerjalanCount++;
            }
        }

        return view('pages.kaprodi.project', [
            'projects' => $projects,
            'totalProjects' => count($projects),
            'tahunBerjalanCount' => $tahunBerjalanCount,
            'lembagas' => $lembagas,
            'versis' => $versis,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_project' => 'required|string|max:255',
            'id_prodi' => 'nullable|integer',
            'id_versi_instrumen' => 'nullable|integer',
            'id_jenjang' => 'nullable|integer',
            'id_jenis_instrumen' => 'nullable|integer',
            'tahun_target_submit' => 'nullable|integer|digits:4',
            'status_project' => 'nullable|string|max:50',
        ]);

        $payload = [
            'id_prodi' => $validated['id_prodi'] ?? session('user.id_prodi', 1),
            'id_versi_instrumen' => $validated['id_versi_instrumen'] ?? 1,
            'id_jenjang' => $validated['id_jenjang'] ?? 1,
            'id_jenis_instrumen' => $validated['id_jenis_instrumen'] ?? 1,
            'nama_project' => $validated['nama_project'],
            'tahun_target_submit' => (int) ($validated['tahun_target_submit'] ?? date('Y')),
            'status_project' => $validated['status_project'] ?? 'draft',
        ];

        try {
            $response = $this->akreditasiService->createProject($payload);
            if ($response->successful()) {
                return redirect()->route('kaprodi.project')
                    ->with('success', 'Project akreditasi berhasil dibuat.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal membuat project akreditasi.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(int|string $id): RedirectResponse
    {
        try {
            $response = $this->akreditasiService->deleteProject($id);
            if ($response->successful()) {
                return redirect()->route('kaprodi.project')
                    ->with('success', 'Project akreditasi berhasil dihapus.');
            }

            return back()->with('error', (string) ($response->json('message') ?? 'Gagal menghapus project akreditasi.'));
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function detail(Request $request): View
    {
        $id = $request->query('id', 1);
        $project = null;

        try {
            $response = $this->akreditasiService->getProject($id);
            if ($response->successful()) {
                $project = $response->json('data');
            } else {
                // Fallback to finding in list of projects
                $projects = $this->akreditasiService->getProjects()->json('data') ?? [];
                foreach ($projects as $p) {
                    if ((string) ($p['id'] ?? '') === (string) $id) {
                        $project = $p;
                        break;
                    }
                }
            }
        } catch (\Throwable) {
            $project = null;
        }

        return view('pages.kaprodi.project-detail', [
            'project' => $project,
        ]);
    }
}
