<?php

namespace App\Http\Controllers;

use App\Services\AkademikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UppsController extends Controller
{
    public function __construct(
        protected AkademikService $akademikService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_upps' => ['required', 'string', 'max:255'],
            'pimpinan_upps' => ['nullable', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:100'],
            'prodi_id' => ['required'],
            'id_pt' => ['nullable'],
            'kode_upps' => ['nullable', 'string', 'max:50'],
        ], [
            'nama_upps.required' => 'Nama UPPS wajib diisi.',
            'jenis.required' => 'Jenis UPPS wajib dipilih.',
            'prodi_id.required' => 'Anggota UPPS (Program Studi) wajib dipilih.',
        ]);

        $kodeUpps = trim($validated['kode_upps'] ?? '');
        if (empty($kodeUpps)) {
            $words = preg_split('/\s+/', trim($validated['nama_upps']));
            $acronym = '';
            foreach ($words as $w) {
                $acronym .= strtoupper(substr($w, 0, 1));
            }
            $acronym = substr(preg_replace('/[^A-Z0-9]/', '', $acronym), 0, 4);
            if (strlen($acronym) < 2) {
                $acronym = 'UPPS';
            }
            $kodeUpps = $acronym.'-'.strtoupper(Str::random(3));
        }

        $payload = [
            'id_pt' => (int) ($validated['id_pt'] ?? 1),
            'kode_upps' => $kodeUpps,
            'nama_upps' => trim($validated['nama_upps']),
            'pimpinan_upps' => trim($validated['pimpinan_upps'] ?? ''),
            'jenis' => trim($validated['jenis']),
            'prodi_id' => (int) $validated['prodi_id'],
        ];

        $response = $this->akademikService->createUpps($payload);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal menambahkan data UPPS ke server.');

            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menambahkan',
                'message' => $message,
            ]);
        }

        $newId = $response->json('data.id') ?? time();
        $customUpps = session('custom_upps_list', []);
        $customUpps[] = array_merge($payload, ['id' => $newId]);
        session(['custom_upps_list' => $customUpps]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Ditambahkan',
            'message' => $response->json('message') ?? 'Data UPPS berhasil ditambahkan.',
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $validated = $request->validate([
            'nama_upps' => ['required', 'string', 'max:255'],
            'pimpinan_upps' => ['nullable', 'string', 'max:255'],
            'jenis' => ['required', 'string', 'max:100'],
            'prodi_id' => ['required'],
            'id_pt' => ['nullable'],
            'kode_upps' => ['nullable', 'string', 'max:50'],
        ], [
            'nama_upps.required' => 'Nama UPPS wajib diisi.',
            'jenis.required' => 'Jenis UPPS wajib dipilih.',
            'prodi_id.required' => 'Anggota UPPS (Program Studi) wajib dipilih.',
        ]);

        $kodeUpps = trim($validated['kode_upps'] ?? '');
        if (empty($kodeUpps)) {
            $kodeUpps = 'UPPS-'.$id;
        }

        $payload = [
            'id_pt' => (int) ($validated['id_pt'] ?? 1),
            'kode_upps' => $kodeUpps,
            'nama_upps' => trim($validated['nama_upps']),
            'pimpinan_upps' => trim($validated['pimpinan_upps'] ?? ''),
            'jenis' => trim($validated['jenis']),
            'prodi_id' => (int) $validated['prodi_id'],
        ];

        $response = $this->akademikService->updateUpps($id, $payload);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal memperbarui data UPPS di server.');

            return redirect()->back()->withInput()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Memperbarui',
                'message' => $message,
            ]);
        }

        $customUpps = session('custom_upps_list', []);
        $found = false;
        foreach ($customUpps as &$u) {
            if ((string) $u['id'] === (string) $id) {
                $u = array_merge($u, $payload);
                $found = true;
                break;
            }
        }
        if (! $found) {
            $customUpps[] = array_merge($payload, ['id' => $id]);
        }
        session(['custom_upps_list' => $customUpps]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Diperbarui',
            'message' => $response->json('message') ?? 'Data UPPS berhasil diperbarui.',
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $response = $this->akademikService->deleteUpps($id);

        if (! $response->successful()) {
            $message = $this->extractErrorMessage($response, 'Gagal menghapus data UPPS dari server.');

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'title' => 'Gagal Menghapus',
                'message' => $message,
            ]);
        }

        $customUpps = session('custom_upps_list', []);
        $customUpps = array_values(array_filter($customUpps, fn ($u) => (string) $u['id'] !== (string) $id));
        session(['custom_upps_list' => $customUpps]);

        $deletedIds = session('deleted_upps_ids', []);
        $deletedIds[] = $id;
        session(['deleted_upps_ids' => $deletedIds]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil Dihapus',
            'message' => $response->json('message') ?? 'Data UPPS berhasil dihapus.',
        ]);
    }

    protected function extractErrorMessage($response, string $fallback): string
    {
        $errors = $response->json('errors');
        if (is_array($errors) && ! empty($errors)) {
            $firstField = reset($errors);
            if (is_array($firstField) && ! empty($firstField)) {
                return (string) $firstField[0];
            }
            if (is_string($firstField)) {
                return $firstField;
            }
        }

        return (string) ($response->json('message') ?? $fallback);
    }
}
