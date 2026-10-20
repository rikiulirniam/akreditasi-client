<?php

namespace App\Http\Controllers;

use App\Models\Sertifikasi;
use App\Models\TenagaKependidikan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TenagaKependidikanController extends Controller
{
    /**
     * Display a listing of the resource with server-side search, filtering, and pagination.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $search = trim((string) $request->query('cari', $request->query('search', '')));
        $selectedJabatan = trim((string) $request->query('jabatan', ''));
        $selectedKualifikasi = trim((string) $request->query('kualifikasi', ''));

        $query = TenagaKependidikan::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($selectedJabatan !== '') {
            $query->where('jabatan', $selectedJabatan);
        }

        if ($selectedKualifikasi !== '') {
            $query->where('kualifikasi', $selectedKualifikasi);
        }

        $totalData = $query->count();

        $tenagaKependidikan = $query->orderBy('id', 'asc')
            ->paginate(8)
            ->withQueryString();

        if ($tenagaKependidikan->isEmpty() && $tenagaKependidikan->lastPage() > 0 && $tenagaKependidikan->currentPage() > $tenagaKependidikan->lastPage()) {
            return redirect()->to($tenagaKependidikan->url($tenagaKependidikan->lastPage()));
        }

        $jabatans = TenagaKependidikan::query()
            ->select('jabatan')
            ->distinct()
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->orderBy('jabatan')
            ->pluck('jabatan');

        $kualifikasis = TenagaKependidikan::query()
            ->select('kualifikasi')
            ->distinct()
            ->whereNotNull('kualifikasi')
            ->where('kualifikasi', '!=', '')
            ->orderBy('kualifikasi')
            ->pluck('kualifikasi');

        return view('tenaga-kependidikan.index', [
            'tenagaKependidikan' => $tenagaKependidikan,
            'totalData' => $totalData,
            'search' => $search,
            'selectedJabatan' => $selectedJabatan,
            'selectedKualifikasi' => $selectedKualifikasi,
            'jabatans' => $jabatans,
            'kualifikasis' => $kualifikasis,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tenaga-kependidikan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => ['required', 'string', 'max:50', 'unique:tenaga_kependidikan,nip'],
            'jabatan' => ['required', 'string', 'max:255'],
            'kualifikasi' => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'jabatan.required' => 'Jabatan wajib diisi.',
        ]);

        if (empty($validated['kualifikasi'])) {
            $validated['kualifikasi'] = '-';
        }

        TenagaKependidikan::create($validated);

        return redirect()->route('tenaga-kependidikan.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Tenaga Kependidikan berhasil ditambahkan.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, TenagaKependidikan $tenagaKependidikan): JsonResponse|View
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($tenagaKependidikan->load(['kualifikasis', 'sertifikasis']));
        }

        $tenagaKependidikan->load(['kualifikasis', 'sertifikasis']);

        return view('tenaga-kependidikan.show', [
            'tenagaKependidikan' => $tenagaKependidikan,
        ]);
    }

    /**
     * Store a newly created sertifikasi for the specified resource.
     */
    public function storeSertifikasi(Request $request, TenagaKependidikan $tenagaKependidikan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_sertifikasi' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'nomor_sertifikasi' => ['nullable', 'string', 'max:255'],
            'tahun' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'nama_sertifikasi.required' => 'Nama sertifikasi wajib diisi.',
            'penerbit.required' => 'Lembaga penerbit wajib diisi.',
        ]);

        $tenagaKependidikan->sertifikasis()->create($validated);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Sertifikasi berhasil ditambahkan.',
        ]);
    }

    /**
     * Remove the specified sertifikasi.
     */
    public function destroySertifikasi(TenagaKependidikan $tenagaKependidikan, Sertifikasi $sertifikasi): RedirectResponse
    {
        if ($sertifikasi->tenaga_kependidikan_id !== $tenagaKependidikan->id) {
            abort(403);
        }

        $sertifikasi->delete();

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Sertifikasi berhasil dihapus.',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, TenagaKependidikan $tenagaKependidikan): JsonResponse|View
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($tenagaKependidikan);
        }

        return view('tenaga-kependidikan.edit', [
            'item' => $tenagaKependidikan,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TenagaKependidikan $tenagaKependidikan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nip' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tenaga_kependidikan', 'nip')->ignore($tenagaKependidikan->id),
            ],
            'jabatan' => ['required', 'string', 'max:255'],
            'kualifikasi' => ['nullable', 'string', 'max:255'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar pada data lain.',
            'jabatan.required' => 'Jabatan wajib diisi.',
        ]);

        if (empty($validated['kualifikasi'])) {
            $validated['kualifikasi'] = $tenagaKependidikan->kualifikasi ?: '-';
        }

        $tenagaKependidikan->update($validated);

        return redirect()->route('tenaga-kependidikan.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Tenaga Kependidikan berhasil diperbarui.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TenagaKependidikan $tenagaKependidikan): RedirectResponse
    {
        $tenagaKependidikan->delete();

        return redirect()->route('tenaga-kependidikan.index')->with('toast', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Data Tenaga Kependidikan berhasil dihapus.',
        ]);
    }
}
