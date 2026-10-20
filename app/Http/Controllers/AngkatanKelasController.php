<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AngkatanKelasController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'angkatan_id' => ['required'],
            'kelas_id' => ['required'],
            'prodi_id' => ['required'],
            'tahun_akademik_id' => ['required'],
        ], [
            'angkatan_id.required' => 'Angkatan wajib ditentukan.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'prodi_id.required' => 'Program Studi wajib dipilih.',
            'tahun_akademik_id.required' => 'Tahun Akademik wajib dipilih.',
        ]);

        $payload = [
            'angkatan_id' => (int) $validated['angkatan_id'],
            'kelas_id' => (int) $validated['kelas_id'],
            'prodi_id' => (int) $validated['prodi_id'],
            'tahun_akademik_id' => (int) $validated['tahun_akademik_id'],
        ];

        $response = $this->akademikService->createAngkatanKelas($payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $createdId = $body['data']['id'] ?? time();
            $custom = session('custom_angkatan_kelas_list', []);
            $custom[] = array_merge($payload, ['id' => $createdId]);
            session(['custom_angkatan_kelas_list' => $custom]);

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Ditambahkan',
                'message' => $body['message'] ?? 'Kelas berhasil ditambahkan ke angkatan ini.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menambahkan kelas ke angkatan.');

        return redirect()->back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menambahkan',
            'message' => $errorMessage,
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'angkatan_id' => ['required'],
            'kelas_id' => ['required'],
            'prodi_id' => ['required'],
            'tahun_akademik_id' => ['required'],
        ], [
            'angkatan_id.required' => 'Angkatan wajib ditentukan.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'prodi_id.required' => 'Program Studi wajib dipilih.',
            'tahun_akademik_id.required' => 'Tahun Akademik wajib dipilih.',
        ]);

        $payload = [
            'angkatan_id' => (int) $validated['angkatan_id'],
            'kelas_id' => (int) $validated['kelas_id'],
            'prodi_id' => (int) $validated['prodi_id'],
            'tahun_akademik_id' => (int) $validated['tahun_akademik_id'],
        ];

        $response = $this->akademikService->updateAngkatanKelas($id, $payload);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_angkatan_kelas_list', []);
            $found = false;
            foreach ($custom as &$c) {
                if ((string) $c['id'] === (string) $id) {
                    $c = array_merge($c, $payload);
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $custom[] = array_merge($payload, ['id' => $id]);
            }
            session(['custom_angkatan_kelas_list' => $custom]);

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Diperbarui',
                'message' => $body['message'] ?? 'Data kelas angkatan berhasil diperbarui.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal memperbarui data kelas angkatan.');

        return redirect()->back()->withInput()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Memperbarui',
            'message' => $errorMessage,
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteAngkatanKelas($id);
        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? true)) {
            $custom = session('custom_angkatan_kelas_list', []);
            $custom = array_values(array_filter($custom, fn ($item) => (string) ($item['id'] ?? '') !== (string) $id));
            session(['custom_angkatan_kelas_list' => $custom]);

            $deleted = session('deleted_angkatan_kelas_ids', []);
            $deleted[] = $id;
            session(['deleted_angkatan_kelas_ids' => $deleted]);

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'title' => 'Berhasil Dihapus',
                'message' => $body['message'] ?? 'Kelas berhasil dihapus dari angkatan ini.',
            ]);
        }

        $errorMessage = $this->extractErrorMessage($response, 'Gagal menghapus kelas dari angkatan.');

        return redirect()->back()->with('toast', [
            'type' => 'error',
            'title' => 'Gagal Menghapus',
            'message' => $errorMessage,
        ]);
    }

    protected function extractErrorMessage($response, string $fallback): string
    {
        $body = $response->json();
        if (is_array($body) && ! empty($body['errors'])) {
            $firstError = collect($body['errors'])->flatten()->first();
            if ($firstError) {
                return (string) $firstError;
            }
        }

        return (string) ($body['message'] ?? $fallback);
    }
}
