@extends('layouts.app')

@section('title', 'Detail Project Akreditasi')

@section('content')
@php
    $displayScalar = function (mixed $value, mixed $fallback = '-'): mixed {
        if (is_array($value)) {
            foreach (['nama', 'nama_prodi', 'nama_program_studi', 'nama_jenjang', 'nama_jenis', 'nomor_versi', 'nama_versi', 'nama_project', 'name', 'label', 'value'] as $key) {
                if (isset($value[$key]) && ! is_array($value[$key])) {
                    return $value[$key];
                }
            }
            return $fallback;
        }
        if ($value === null || $value === '') {
            return $fallback;
        }
        return is_scalar($value) ? $value : $fallback;
    };

    $sidebarUser = session('user', []);
    $userRole = strtolower((string) (($sidebarUser['role'] ?? null) ?: ($sidebarUser['role_name'] ?? '')));
    $isKaprodi = str_contains($userRole, 'kaprodi');
    $isAdmin = ! $isKaprodi && ! str_contains($userRole, 'reviewer');

    $compositeOverview = is_array($compositeOverview ?? null) ? $compositeOverview : [];
    $overviewData = $compositeOverview;
    $profile = $overviewData['profil_project'] ?? $project;
    $profile = is_array($profile) ? $profile : [];

    $id = $displayScalar($profile['id'] ?? $project['id'] ?? $projectId ?? null);
    $namaProject = $displayScalar($profile['nama_project'] ?? $profile['nama'] ?? $profile['name'] ?? null);
    $programStudi = $displayScalar($profile['program_studi'] ?? null, 'Belum tersedia');
    $versiNomor = $displayScalar($profile['versi_instrumen'] ?? null);
    $jenjangNama = $displayScalar($profile['jenjang'] ?? null);
    $jenisNama = $displayScalar($profile['jenis_instrumen'] ?? null);
    $tahunTarget = $displayScalar($profile['tahun_target_submit'] ?? $profile['tahun'] ?? $profile['periode'] ?? null);
    $statusRaw = strtolower((string) $displayScalar($profile['status_project'] ?? $project['status_project'] ?? null, ''));
    $isInProgress = $statusRaw === 'in_progress' || $statusRaw === 'pengisian' || str_contains($statusRaw, 'progress');
    $statusLabel = $displayScalar($profile['status_project'] ?? $project['status_project'] ?? null, 'Belum tersedia');
    $progressSummary = is_array($overviewData['progress'] ?? null) ? $overviewData['progress'] : [];
    $criterionItems = is_array($overviewData['progress_9_kriteria'] ?? null) ? array_values($overviewData['progress_9_kriteria']) : [];
    $evidenceItems = is_array($overviewData['ringkasan_evidence_per_kriteria'] ?? null) ? array_values($overviewData['ringkasan_evidence_per_kriteria']) : [];
    $scoring = is_array($overviewData['simulasi_skor'] ?? null) ? $overviewData['simulasi_skor'] : [];
    $overallProgress = is_numeric($progressSummary['progres_total_persen'] ?? null) ? (float) $progressSummary['progres_total_persen'] : null;
    $totalIndicators = is_numeric($progressSummary['total_indikator'] ?? null) ? (int) $progressSummary['total_indikator'] : null;
    $filledIndicators = is_numeric($progressSummary['indikator_terisi_bukti'] ?? null) ? (int) $progressSummary['indikator_terisi_bukti'] : null;
    $picSummary = is_array($picSummary ?? null) ? $picSummary : null;
@endphp

<div class="space-y-6 pb-10">

    <!-- Toast Notification Component -->
    <x-toast />

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm font-semibold text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    @if ($apiError)
        <div role="alert" class="flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold text-amber-900">Ringkasan project belum dapat dimuat</p>
                <p class="mt-0.5 text-xs text-amber-800">{{ $apiError }}</p>
            </div>
            @if ($id)
                <button type="button" id="retry-composite" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-amber-300 bg-white px-4 py-2 text-xs font-bold text-amber-900 hover:bg-amber-100">
                    Retry
                </button>
            @endif
        </div>
    @endif

    @php
        $criteriaCards = [];
        foreach ($criterionItems as $criterion) {
            if (! is_array($criterion)) {
                continue;
            }
            $criterionProgress = is_numeric($criterion['progres_persen'] ?? null)
                ? max(0, min(100, (float) $criterion['progres_persen']))
                : null;
            $criteriaCards[] = [
                'code' => $displayScalar($criterion['kode_kriteria'] ?? null, 'Kriteria'),
                'name' => $displayScalar($criterion['nama_kriteria'] ?? null, 'Data belum tersedia'),
                'progress' => $criterionProgress,
                'total' => is_numeric($criterion['total_indikator'] ?? null) ? (int) $criterion['total_indikator'] : null,
                'filled' => is_numeric($criterion['indikator_terisi_bukti'] ?? null) ? (int) $criterion['indikator_terisi_bukti'] : null,
            ];
        }
    @endphp

    @if (! $apiError)
        <section class="grid gap-4 lg:grid-cols-[minmax(0,1.35fr)_minmax(280px,.65fr)]" aria-label="Ringkasan composite project">
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wider text-additional">Profil project</p>
                        <h2 class="mt-1 text-xl font-extrabold text-primary">{{ $namaProject }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $programStudi }}</p>
                    </div>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-bold text-slate-700">{{ $statusLabel }}</span>
                </div>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div><dt class="text-xs text-slate-400">Jenjang pendidikan</dt><dd class="mt-0.5 text-sm font-bold text-slate-700">{{ $jenjangNama }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Jenis instrumen</dt><dd class="mt-0.5 text-sm font-bold text-slate-700">{{ $jenisNama }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Versi instrumen</dt><dd class="mt-0.5 text-sm font-bold text-slate-700">{{ $versiNomor }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Tahun / periode</dt><dd class="mt-0.5 text-sm font-bold text-slate-700">{{ $tahunTarget }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-400">Identitas project</dt><dd class="mt-0.5 break-all text-sm font-bold text-slate-700">{{ $id !== null ? $id : '-' }}</dd></div>
                </dl>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-additional">Progress keseluruhan</p>
                <p class="mt-3 text-3xl font-extrabold text-primary">{{ $overallProgress !== null ? $overallProgress.'%' : 'Belum tersedia' }}</p>
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-slate-100">
                    @if ($overallProgress !== null)
                        <div class="h-full rounded-full bg-primary" style="width: {{ max(0, min(100, $overallProgress)) }}%"></div>
                    @endif
                </div>
                <p class="mt-3 text-xs text-slate-500">
                    {{ $displayScalar($progressSummary['kriteria'] ?? null) }} kriteria · {{ $filledIndicators !== null ? $filledIndicators : '-' }} dari {{ $totalIndicators !== null ? $totalIndicators : '-' }} indikator terisi bukti
                </p>
            </article>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5" aria-label="Progress kriteria">
            <h2 class="text-base font-extrabold text-primary">Progress {{ count($criteriaCards) }} Kriteria LAM-INFOKOM</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($criteriaCards as $criterion)
                    <article class="min-w-0 rounded-xl border border-slate-200 p-4">
                        <p class="text-xs font-extrabold text-additional">{{ $criterion['code'] }}</p>
                        <p class="mt-1 truncate text-sm font-bold text-slate-700" title="{{ $criterion['name'] }}">{{ $criterion['name'] }}</p>
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                            @if ($criterion['progress'] !== null)
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $criterion['progress'] }}%"></div>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center justify-between gap-2 text-xs">
                            <span class="text-slate-500">
                                {{ $criterion['filled'] !== null && $criterion['total'] !== null ? $criterion['filled'].'/'.$criterion['total'].' indikator terisi' : 'Data belum tersedia' }}
                            </span>
                            <span class="font-bold text-slate-700">{{ $criterion['progress'] !== null ? $criterion['progress'].'%' : '-' }}</span>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">Data progress kriteria belum tersedia dari server.</p>
                @endforelse
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3" aria-label="Ringkasan evidence, plotting, dan scoring">
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-extrabold text-primary">Evidence per Kriteria</h2>
                <div class="mt-3 space-y-2">
                    @forelse ($evidenceItems as $evidenceItem)
                        @if (is_array($evidenceItem))
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-2 text-xs last:border-0">
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-700">{{ $displayScalar($evidenceItem['kode_kriteria'] ?? null, 'Kriteria') }} · {{ $displayScalar($evidenceItem['nama_kriteria'] ?? null, 'Data belum tersedia') }}</p>
                                    <p class="mt-0.5 text-slate-500">{{ $displayScalar($evidenceItem['jumlah_evidence'] ?? null) }} evidence / {{ $displayScalar($evidenceItem['total_indikator'] ?? null) }} indikator</p>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-xs text-slate-500">Ringkasan evidence belum tersedia.</p>
                    @endforelse
                </div>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-extrabold text-primary">Ringkasan PIC</h2>
                <p class="mt-1 text-[11px] text-slate-500">Jumlah indikator project secara keseluruhan; modal Readiness Check menghitung subset indikator kritis.</p>
                @if ($picSummary !== null)
                    <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-xs text-slate-400">Indikator seluruh project dengan PIC</dt><dd class="mt-1 font-bold text-slate-700">{{ $picSummary['assigned'] }}</dd></div>
                        <div><dt class="text-xs text-slate-400">Indikator seluruh project tanpa PIC</dt><dd class="mt-1 font-bold text-slate-700">{{ $picSummary['unassigned'] ?? 'Belum tersedia' }}</dd></div>
                    </dl>
                @else
                    <p class="mt-3 text-xs text-slate-500">Data PIC belum tersedia.</p>
                @endif
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-extrabold text-primary">Simulasi skor</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs text-slate-400">Total skor</dt><dd class="mt-1 font-bold text-slate-700">{{ $displayScalar($scoring['total_skor'] ?? null) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Predikat</dt><dd class="mt-1 font-bold text-slate-700">{{ $displayScalar($scoring['predikat'] ?? null) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Dinilai</dt><dd class="mt-1 font-bold text-slate-700">{{ $displayScalar($scoring['jumlah_dinilai'] ?? null) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Belum dinilai</dt><dd class="mt-1 font-bold text-slate-700">{{ $displayScalar($scoring['jumlah_belum_dinilai'] ?? null) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Jumlah total</dt><dd class="mt-1 font-bold text-slate-700">{{ $displayScalar($scoring['jumlah_total'] ?? null) }}</dd></div>
                    <div><dt class="text-xs text-slate-400">Ambang Kriteria D</dt><dd class="mt-1 font-bold text-slate-700">{{ $displayScalar($scoring['threshold_kriteria_d'] ?? null) }}</dd></div>
                    <div class="col-span-2"><dt class="text-xs text-slate-400">Kelengkapan penilaian</dt><dd class="mt-1 font-bold text-slate-700">{{ is_numeric($scoring['persentase_kelengkapan'] ?? null) ? $scoring['persentase_kelengkapan'].'%' : 'Belum tersedia' }}</dd></div>
                </dl>
            </article>
        </section>
    @endif

    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Project Akreditasi</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Detail parameter instrumen, capaian kriteria, dan monitoring kelengkapan borang akreditasi.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(Route::has('kaprodi.plotting'))
            <a
                href="{{ route('kaprodi.plotting', ['project_id' => $id]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-bold transition-colors cursor-pointer shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
                Plotting Tim
            </a>
            @endif

            <!-- Tombol Edit Project -->
            <button
                type="button"
                onclick="openEditModal({{ json_encode($project) }})"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-colors cursor-pointer border border-blue-200"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                Edit Project
            </button>

            <a
                href="{{ route('kaprodi.project') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition-colors cursor-pointer"
            >
                &larr; Kembali ke Daftar Project
            </a>
        </div>
    </div>

    <!-- Breadcrumb -->
    <div class="flex items-center text-xs gap-1.5 font-medium">
        <a href="{{ route('kaprodi.project') }}" class="text-gray-400 hover:text-additional transition-colors">Project Akreditasi</a>
        <span class="text-gray-300">›</span>
        <span class="text-primary font-bold">{{ $namaProject }}</span>
    </div>

    <!-- 2 Kotak Utama (Informasi & Status Monitoring) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Informasi Project -->
        <div class="md:col-span-2 bg-white rounded-2xl border border-[#EAEAEA] p-6 flex flex-col justify-center shadow-xs">
            <div class="text-additional text-[10px] font-bold tracking-widest uppercase mb-2">
                LAM INFOKOM · V{{ $versiNomor }} · {{ strtoupper($jenjangNama) }}
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-primary mb-1 tracking-tight">{{ $namaProject }}</h2>
            <p class="text-gray-500 text-xs sm:text-sm font-medium">{{ $programStudi }} · {{ $jenisNama }} · Tahun Target Submit: {{ $tahunTarget }}</p>
        </div>

        <!-- Status & Monitoring Pengerjaan -->
        <div class="md:col-span-1 bg-white rounded-2xl border border-[#EAEAEA] p-6 flex flex-col justify-center shadow-xs">
            <div class="text-gray-400 text-xs font-bold mb-1 uppercase tracking-wider">Status Project</div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-extrabold {{ $isInProgress ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-300' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ $isInProgress ? 'bg-amber-500 animate-pulse' : 'bg-slate-400' }}"></span>
                    {{ $statusLabel }}
                </span>
            </div>

            @if ($isKaprodi && $id)
                <form action="{{ route('kaprodi.project.update-status', $id) }}" method="POST" class="mt-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_project" value="{{ $isInProgress ? 'draft' : 'in_progress' }}">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold transition-all shadow-xs cursor-pointer {{ $isInProgress ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300' : 'bg-amber-500 hover:bg-amber-600 text-white' }}">
                        @if ($isInProgress)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Kembalikan ke Draft
                        @else
                            
                            Mulai Pengerjaan (In Progress)
                        @endif
                    </button>
                </form>
            @endif

            <p class="text-[11px] text-gray-400 mt-2">
                Penilaian indikator dilaksanakan oleh Kaprodi dan Administrator.
            </p>
        </div>
    </div>

    <!-- Ringkasan berdasarkan response Composite Overview -->
    @php
        $evidenceCount = null;
        if ($evidenceItems !== []) {
            $evidenceCount = 0;
            foreach ($evidenceItems as $evidenceItem) {
                if (is_array($evidenceItem) && is_numeric($evidenceItem['jumlah_evidence'] ?? null)) {
                    $evidenceCount += (int) $evidenceItem['jumlah_evidence'];
                }
            }
        }
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Kriteria</div>
            <div class="text-primary text-2xl font-extrabold leading-none">{{ $apiError ? '-' : count($criteriaCards) }}</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Indikator</div>
            <div class="text-primary text-2xl font-extrabold leading-none">{{ $totalIndicators ?? '-' }}</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Evidence</div>
            <div class="text-primary text-2xl font-extrabold leading-none">{{ $evidenceCount ?? '-' }}</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Status</div>
            <div class="text-primary text-sm font-extrabold leading-none uppercase {{ $isInProgress ? 'text-amber-600' : 'text-slate-600' }}">
                {{ $statusLabel }}
            </div>
        </div>
    </div>

    @php
        $narrativeSubkriteria = [];
        foreach (($overviewData['subkriteria'] ?? $overviewData['subkriterias'] ?? []) as $subkriteria) {
            if (is_array($subkriteria)) {
                $narrativeSubkriteria[] = $subkriteria;
            }
        }
    @endphp
    @if ($id && $narrativeSubkriteria !== [])
        <section class="rounded-2xl border border-slate-200 bg-white p-5" aria-labelledby="narrative-template-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="narrative-template-title" class="text-base font-extrabold text-primary">Template Narasi Subkriteria</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Draft dari data live backend; angka kuantitatif tidak dapat diubah melalui narasi.</p>
                </div>
            </div>
            <div class="mt-4 grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                <div>
                    <label for="narrative-subkriteria" class="mb-1.5 block text-xs font-bold text-slate-700">Subkriteria</label>
                    <select id="narrative-subkriteria" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        @foreach ($narrativeSubkriteria as $subkriteria)
                            <option value="{{ $subkriteria['id'] ?? '' }}">{{ $subkriteria['kode_subkriteria'] ?? $subkriteria['kode'] ?? '-' }} — {{ $subkriteria['nama_subkriteria'] ?? $subkriteria['nama'] ?? 'Subkriteria' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div id="narrative-facts" class="mb-3 rounded-xl border border-sky-100 bg-sky-50 p-3 text-xs text-sky-900">Pilih subkriteria untuk memuat template.</div>
                    <label for="narrative-text" class="mb-1.5 block text-xs font-bold text-slate-700">Narasi deskriptif</label>
                    <textarea id="narrative-text" rows="8" class="block w-full resize-y rounded-xl border border-slate-200 px-3 py-3 text-sm leading-relaxed text-slate-700 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" placeholder="Memuat draft narasi..."></textarea>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
                        <p id="narrative-status" class="text-xs text-slate-500" aria-live="polite"></p>
                        <div class="flex items-center gap-2">
                            <button type="button" id="narrative-cancel" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                            <button type="button" id="narrative-save" class="rounded-xl bg-primary px-4 py-2 text-xs font-bold text-white hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-60">Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Legacy sample matrix is intentionally disabled; only Composite Overview data is rendered.
    @php
        // Ambil data kriteria 1 (Visi, Misi, Tujuan, dan Strategi) dari API jika ada
        $apiC1 = collect($apiKriteria ?? [])->first(function ($item) {
            $nama = strtolower($item['nama_kriteria'] ?? '');
            return str_contains($nama, 'visi') || str_contains($nama, 'vmts');
        });

        $kriteriaList = [
            [
                'code' => 'C1',
                'name' => !empty($apiC1['nama_kriteria']) ? $apiC1['nama_kriteria'] : 'Visi, Misi, Tujuan, dan Strategi',
                'isOpen' => true,
                'subkriteria' => [
                    [
                        'code' => 'C1.1',
                        'name' => 'Kejelasan & kerealistisan VMTS',
                        'indikator' => [
                            [
                                'code' => 'C1.1.1',
                                'name' => 'Ketersediaan dokumen formal — kejelasan & kerealistisan vmts',
                                'desc' => 'Kelengkapan, konsistensi, dan ketercapaian ketersediaan dokumen formal.',
                                'bobot' => '2.6',
                            ],
                            [
                                'code' => 'C1.1.2',
                                'name' => 'Tingkat ketercapaian target — kejelasan & kerealistisan vmts',
                                'desc' => 'Kelengkapan, konsistensi, dan ketercapaian tingkat ketercapaian target.',
                                'bobot' => '2.6',
                            ],
                        ],
                    ],
                    [
                        'code' => 'C1.2',
                        'name' => 'Mekanisme penyusunan & sosialisasi',
                        'indikator' => [
                            [
                                'code' => 'C1.2.1',
                                'name' => 'Rasio dan kecukupan — mekanisme penyusunan & sosialisasi',
                                'desc' => 'Kesesuaian dan keterlibatan seluruh pemangku kepentingan.',
                                'bobot' => '2.0',
                            ],
                            [
                                'code' => 'C1.2.2',
                                'name' => 'Monitoring dan evaluasi berkala — mekanisme penyusunan & sosialisasi',
                                'desc' => 'Ketersediaan bukti monev pelaksanaan dan pencapaian visi misi.',
                                'bobot' => '2.0',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C2',
                'name' => 'Tata Kelola, Tata Pamong, dan Kerjasama',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C2.1',
                        'name' => 'Sistem Tata Pamong & Kepemimpinan',
                        'indikator' => [
                            [
                                'code' => 'C2.1.1',
                                'name' => 'Efektivitas kepemimpinan operasional, organisasi, dan publik',
                                'desc' => 'Akuntabilitas kelembagaan tata pamong UPPS.',
                                'bobot' => '3.0',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C3',
                'name' => 'Mahasiswa',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C3.1',
                        'name' => 'Kualitas Input & Layanan Mahasiswa',
                        'indikator' => [
                            [
                                'code' => 'C3.1.1',
                                'name' => 'Rasio pendaftar terhadap daya tampung program studi',
                                'desc' => 'Daya tarik dan rasio selektivitas mahasiswa baru.',
                                'bobot' => '2.5',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C4',
                'name' => 'Sumber Daya Manusia',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C4.1',
                        'name' => 'Kualifikasi & Kecukupan Dosen Tetap',
                        'indikator' => [
                            [
                                'code' => 'C4.1.1',
                                'name' => 'Kualifikasi akademik dan jabatan fungsional dosen',
                                'desc' => 'Persentase DTPS berpendidikan S3 dan Lektor Kepala/Guru Besar.',
                                'bobot' => '3.5',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C5',
                'name' => 'Keuangan, Sarana, dan Prasarana',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C5.1',
                        'name' => 'Kecukupan dan Keberlanjutan Finansial',
                        'indikator' => [
                            [
                                'code' => 'C5.1.1',
                                'name' => 'Alokasi dana operasional per mahasiswa per tahun',
                                'desc' => 'Realisasi biaya operasional pendidikan.',
                                'bobot' => '3.0',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C6',
                'name' => 'Pendidikan',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C6.1',
                        'name' => 'Kurikulum & Pembelajaran',
                        'indikator' => [
                            [
                                'code' => 'C6.1.1',
                                'name' => 'Kesesuaian kurikulum dengan perkembangan IPTEK dan industri',
                                'desc' => 'Keterlibatan stakeholders dalam peninjauan kurikulum.',
                                'bobot' => '3.5',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C7',
                'name' => 'Penelitian',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C7.1',
                        'name' => 'Relevansi & Produktivitas Penelitian',
                        'indikator' => [
                            [
                                'code' => 'C7.1.1',
                                'name' => 'Jumlah publikasi ilmiah dosen dan mahasiswa per tahun',
                                'desc' => 'Publikasi pada jurnal terakreditasi dan bereputasi.',
                                'bobot' => '2.5',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C8',
                'name' => 'Pengabdian kepada Masyarakat (PkM)',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C8.1',
                        'name' => 'Dampak & Manfaat PkM bagi Masyarakat',
                        'indikator' => [
                            [
                                'code' => 'C8.1.1',
                                'name' => 'Penerapan hasil penelitian dalam kegiatan PkM',
                                'desc' => 'Kegiatan pengabdian masyarakat yang terintegrasi pembelajaran.',
                                'bobot' => '2.5',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C9',
                'name' => 'Luaran dan Capaian Tridharma',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C9.1',
                        'name' => 'Prestasi & Kelulusan Tepat Waktu',
                        'indikator' => [
                            [
                                'code' => 'C9.1.1',
                                'name' => 'Persentase kelulusan tepat waktu dan rata-rata IPK lulusan',
                                'desc' => 'Waktu tunggu kerja lulusan mendapatkan pekerjaan pertama yang relevan.',
                                'bobot' => '4.0',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    @endphp
    --}}

    @php
        $compositeKriteria = $criterionItems;
        $kriteriaList = [];
        foreach ($compositeKriteria as $criterion) {
            if (! is_array($criterion)) {
                continue;
            }

            $kriteriaList[] = [
                'code' => $displayScalar($criterion['kode_kriteria'] ?? null, 'Kriteria'),
                'name' => $displayScalar($criterion['nama_kriteria'] ?? null, 'Data belum tersedia'),
                'progress' => is_numeric($criterion['progres_persen'] ?? null) ? max(0, min(100, (float) $criterion['progres_persen'])) : null,
                'filled' => is_numeric($criterion['indikator_terisi_bukti'] ?? null) ? (int) $criterion['indikator_terisi_bukti'] : null,
                'total' => is_numeric($criterion['total_indikator'] ?? null) ? (int) $criterion['total_indikator'] : null,
            ];
        }
    @endphp

    <!-- Bagian Matriks Kriteria & Monitoring Borang -->
    <div class="mt-8 bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
        <div class="p-6 border-b border-[#EAEAEA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-extrabold text-primary">Matriks Kriteria & Monitoring Borang</h3>
                <p class="text-gray-500 text-xs sm:text-sm mt-1">Pantau kriteria borang, subkriteria, dan kelengkapan bukti dokumen per indikator.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    {{ count($criteriaCards) }} Kriteria · {{ $totalIndicators ?? '-' }} Indikator
                </span>
            </div>
        </div>

        <div class="flex flex-col">
            @forelse($kriteriaList as $item)
                @php
                    $isLast = $loop->last;
                @endphp

                <div class="grid gap-3 border-b border-[#EAEAEA] p-4 sm:grid-cols-[minmax(0,1fr)_minmax(180px,.45fr)] sm:items-center {{ $isLast ? 'border-0' : '' }}">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800">
                            <span class="mr-2 text-additional">{{ $item['code'] }}</span>{{ $item['name'] }}
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $item['filled'] !== null && $item['total'] !== null ? $item['filled'].' dari '.$item['total'].' indikator terisi bukti' : 'Data jumlah indikator belum tersedia' }}
                        </p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="text-slate-500">Progress</span>
                            <span class="font-bold text-slate-700">{{ $item['progress'] !== null ? $item['progress'].'%' : 'Belum tersedia' }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100">
                            @if ($item['progress'] !== null)
                                <div class="h-full rounded-full bg-emerald-500" style="width: {{ $item['progress'] }}%"></div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="p-5 text-sm text-slate-500">Matriks kriteria belum tersedia dari Composite Overview.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- MODAL EDIT PROJECT -->
<div id="modal-edit-project" class="hidden fixed inset-0 z-50 bg-black/45 flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto p-6 sm:p-8 relative">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Edit Project Akreditasi</h2>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui informasi parameter project akreditasi.</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-normal transition-colors cursor-pointer">&times;</button>
        </div>

        <form id="formEditProject" method="POST" action="">
            @csrf
            @method('PUT')

            <input type="hidden" name="id_prodi" id="editProdiId" value="1">
            <input type="hidden" name="id_jenis_instrumen" id="editJenisId" value="1">

            <div class="space-y-4">
                <div>
                    <label for="editNamaProject" class="block text-xs font-bold text-gray-800 mb-1.5">Nama Project <span class="text-red-500">*</span></label>
                    <x-input
                        type="text"
                        name="nama_project"
                        id="editNamaProject"
                        required
                        class="text-xs"
                    />
                </div>

                <div>
                    <label for="editInstrumen" class="block text-xs font-bold text-gray-800 mb-1.5">
                        Instrumen <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="id_instrumen"
                        id="editInstrumen"
                        onchange="syncInstrumenData(this, 'edit')"
                        required
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        @forelse ($instrumenList ?? [] as $ins)
                            <option value="{{ $ins['id'] }}"
                                data-versi="{{ $ins['id_versi_instrumen'] ?? 1 }}"
                                data-jenjang="{{ $ins['id_jenjang'] ?? 1 }}"
                                data-jenis="{{ $ins['id_jenis_instrumen'] ?? 1 }}">
                                {{ $ins['formatted_label'] }}
                            </option>
                        @empty
                            <option value="1" data-versi="1" data-jenjang="1" data-jenis="1">LAM INFOKOM - v2.1 - Sarjana Terapan - APS</option>
                        @endforelse
                    </select>
                    <input type="hidden" name="id_versi_instrumen" id="editVersiId" value="1">
                    <input type="hidden" name="id_jenjang" id="editJenjangId" value="1">
                    <input type="hidden" name="id_jenis_instrumen" id="editJenisId" value="1">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label for="editTahun" class="block text-xs font-bold text-gray-800 mb-1.5">Tahun Target Submit</label>
                        <input
                            type="number"
                            name="tahun_target_submit"
                            id="editTahun"
                            required
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm text-gray-900 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1.5">Status Project</label>
                        @if ($isKaprodi)
                            <div class="flex flex-wrap gap-2 pt-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_project" value="draft" id="editStatusDraft" class="peer sr-only">
                                    <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-4 py-2 rounded-xl peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">Draft</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="status_project" value="in_progress" id="editStatusInProgress" class="peer sr-only">
                                    <div class="bg-slate-100 text-gray-600 text-xs font-semibold px-4 py-2 rounded-xl peer-checked:bg-primary peer-checked:text-white hover:bg-slate-200 transition-colors">In Progress</div>
                                </label>
                            </div>
                        @else
                            <div class="p-3 bg-gray-50 rounded-xl text-xs text-gray-500 font-medium border border-gray-200">
                                Status project dikelola oleh Kaprodi (<span id="editStatusReadonly" class="font-bold text-primary">Draft</span>).
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-gray-600 text-xs font-bold rounded-xl transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-primary hover:bg-additional text-white text-xs font-bold rounded-xl transition-colors cursor-pointer">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // --- SINKRONISASI INSTRUMEN DATA ---
    function syncInstrumenData(selectEl, prefix) {
        if (!selectEl) return;
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt) return;

        const vId = opt.getAttribute('data-versi') || '1';
        const jId = opt.getAttribute('data-jenjang') || '1';
        const kId = opt.getAttribute('data-jenis') || '1';

        const vInput = document.getElementById(prefix + 'VersiId');
        const jInput = document.getElementById(prefix + 'JenjangId');
        const kInput = document.getElementById(prefix + 'JenisId');

        if (vInput) vInput.value = vId;
        if (jInput) jInput.value = jId;
        if (kInput) kInput.value = kId;
    }

    // --- FUNGSI ACCORDION ---
    function toggleAccordion(id) {
        const content = document.getElementById('content-' + id);
        const icon = document.getElementById('icon-' + id);
        const title = document.getElementById('title-' + id);

        if (!content || !icon) return;

        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            content.classList.add('flex');
            icon.innerText = '−';
            if (title) {
                title.classList.add('text-primary');
                title.classList.remove('text-gray-800');
            }
        } else {
            content.classList.add('hidden');
            content.classList.remove('flex');
            icon.innerText = '+';
            if (title) {
                title.classList.remove('text-primary');
                title.classList.add('text-gray-800');
            }
        }
    }

    // --- FUNGSI MODAL EDIT ---
    function openEditModal(item) {
        const modal = document.getElementById('modal-edit-project');
        const form = document.getElementById('formEditProject');
        if (!modal || !form || !item) return;

        form.action = "{{ url('kaprodi/project') }}/" + item.id;
        document.getElementById('editNamaProject').value = item.nama_project || '';
        document.getElementById('editTahun').value = item.tahun_target_submit || '2026';
        
        if (document.getElementById('editInstrumen')) {
            const select = document.getElementById('editInstrumen');
            let matched = false;

            if (item.id_instrumen) {
                select.value = item.id_instrumen;
                matched = select.value == item.id_instrumen;
            }

            if (!matched) {
                const targetVersi = item.id_versi_instrumen || (item.versi_instrumen?.id);
                for (let i = 0; i < select.options.length; i++) {
                    const opt = select.options[i];
                    if (targetVersi && opt.getAttribute('data-versi') == targetVersi) {
                        select.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
            }

            syncInstrumenData(select, 'edit');
        }

        const status = (item.status_project || 'draft').toLowerCase();
        const isProg = status === 'in_progress' || status === 'pengisian' || status.includes('progress');
        const draftRadio = document.getElementById('editStatusDraft');
        const inProgRadio = document.getElementById('editStatusInProgress');
        if (draftRadio && inProgRadio) {
            if (isProg) {
                inProgRadio.checked = true;
            } else {
                draftRadio.checked = true;
            }
        }
        const statusReadonly = document.getElementById('editStatusReadonly');
        if (statusReadonly) {
            statusReadonly.innerText = isProg ? 'In Progress' : 'Draft';
        }

        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        const modal = document.getElementById('modal-edit-project');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    const compositeProjectId = {{ $id !== null ? json_encode((string) $id) : 'null' }};
    const retryButton = document.getElementById('retry-composite');
    if (retryButton && compositeProjectId) {
        retryButton.addEventListener('click', async () => {
            retryButton.disabled = true;
            retryButton.textContent = 'Memuat...';
            try {
                const response = await fetch(`{{ url('/kaprodi/project') }}/${encodeURIComponent(compositeProjectId)}/composite-overview`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) throw new Error('Retry gagal');
                window.location.reload();
            } catch (error) {
                retryButton.disabled = false;
                retryButton.textContent = 'Retry';
            }
        });
    }

    const narrativeSelect = document.getElementById('narrative-subkriteria');
    const narrativeText = document.getElementById('narrative-text');
    const narrativeFacts = document.getElementById('narrative-facts');
    const narrativeStatus = document.getElementById('narrative-status');
    const narrativeSave = document.getElementById('narrative-save');
    const narrativeCancel = document.getElementById('narrative-cancel');
    let originalNarrative = '';
    let narrativeDirty = false;

    async function loadNarrativeTemplate() {
        if (!narrativeSelect || !narrativeText || !compositeProjectId || !narrativeSelect.value) return;
        const subkriteriaId = narrativeSelect.value;
        narrativeStatus.textContent = 'Memuat template...';
        narrativeText.disabled = true;
        try {
            const templateUrl = `{{ url('/kaprodi/project') }}/${encodeURIComponent(compositeProjectId)}/subkriteria/${encodeURIComponent(subkriteriaId)}/narasi-template`;
            const response = await fetch(templateUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const body = await response.json();
            if (!response.ok) throw new Error(body.message || 'Template tidak dapat dimuat.');
            const data = body.data && typeof body.data === 'object' ? body.data : body;
            const draft = data.narasi ?? data.narrative ?? data.template_narasi ?? data.draft_narasi ?? data.template ?? '';
            const facts = data.data ?? data.statistik ?? data.fakta ?? data.quantitative_data ?? null;
            narrativeFacts.textContent = facts && typeof facts === 'object'
                ? 'Data kuantitatif berasal dari data akademik terintegrasi dan tidak dapat diubah melalui narasi.'
                : 'Data kuantitatif berasal dari data akademik terintegrasi dan tidak dapat diubah melalui narasi.';
            narrativeText.value = typeof draft === 'string' ? draft : '';
            originalNarrative = narrativeText.value;
            narrativeDirty = false;
            narrativeStatus.textContent = '';
        } catch (error) {
            narrativeText.value = '';
            narrativeStatus.textContent = error.message || 'Template tidak dapat dimuat.';
        } finally {
            narrativeText.disabled = false;
        }
    }

    if (narrativeSelect) {
        narrativeSelect.addEventListener('change', loadNarrativeTemplate);
        narrativeText.addEventListener('input', () => {
            narrativeDirty = narrativeText.value !== originalNarrative;
            narrativeStatus.textContent = narrativeDirty ? `${narrativeText.value.length} karakter · Perubahan belum disimpan` : '';
        });
        narrativeCancel.addEventListener('click', () => {
            if (narrativeDirty && !window.confirm('Batalkan perubahan narasi yang belum disimpan?')) return;
            narrativeText.value = originalNarrative;
            narrativeDirty = false;
            narrativeStatus.textContent = 'Perubahan dibatalkan.';
        });
        narrativeSave.addEventListener('click', async () => {
            if (!compositeProjectId || !narrativeSelect.value) return;
            narrativeSave.disabled = true;
            narrativeSave.textContent = 'Menyimpan...';
            narrativeStatus.textContent = '';
            try {
                const saveUrl = `{{ url('/kaprodi/project') }}/${encodeURIComponent(compositeProjectId)}/subkriteria/${encodeURIComponent(narrativeSelect.value)}/narasi`;
                const response = await fetch(saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ narasi: narrativeText.value }),
                });
                const body = await response.json();
                if (!response.ok) throw new Error(body.message || 'Narasi gagal disimpan.');
                originalNarrative = narrativeText.value;
                narrativeDirty = false;
                narrativeStatus.textContent = body.message || 'Narasi berhasil disimpan.';
            } catch (error) {
                narrativeStatus.textContent = error.message || 'Narasi gagal disimpan.';
            } finally {
                narrativeSave.disabled = false;
                narrativeSave.textContent = 'Simpan';
            }
        });
        window.addEventListener('beforeunload', (event) => {
            if (!narrativeDirty) return;
            event.preventDefault();
            event.returnValue = '';
        });
        loadNarrativeTemplate();
    }
</script>
@endsection
