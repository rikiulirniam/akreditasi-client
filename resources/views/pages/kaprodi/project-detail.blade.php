@extends('layouts.app')

@section('title', 'Detail Project Akreditasi')

@section('content')
@php
    $sidebarUser = session('user', []);
    $userRole = strtolower((string) (($sidebarUser['role'] ?? null) ?: ($sidebarUser['role_name'] ?? '')));
    $isKaprodi = str_contains($userRole, 'kaprodi');
    $isAdmin = ! $isKaprodi && ! str_contains($userRole, 'reviewer');

    $id = $project['id'] ?? null;
    $namaProject = $project['nama_project'] ?? 'Sarjana Terapan Test';
    $versiNomor = $project['versi_instrumen']['nomor_versi'] ?? '2.1';
    $jenjangNama = $project['jenjang']['nama_jenjang'] ?? 'Sarjana Terapan';
    $jenisNama = $project['jenis_instrumen']['nama_jenis'] ?? ($project['status_project'] ?? 'Unggul');
    $tahunTarget = $project['tahun_target_submit'] ?? 2026;
    $statusRaw = strtolower((string) ($project['status_project'] ?? 'draft'));
    $isInProgress = $statusRaw === 'in_progress' || $statusRaw === 'pengisian' || str_contains($statusRaw, 'progress');
    $statusLabel = $isInProgress ? 'In Progress' : 'Draft';
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
            <p class="text-gray-500 text-xs sm:text-sm font-medium">{{ $jenisNama }} · Tahun Target Submit: {{ $tahunTarget }}</p>
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
                *Penilaian formal borang dilakukan oleh Tim Reviewer.
            </p>
        </div>
    </div>

    <!-- 5 Kotak Statistik Kecil -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Kriteria</div>
            <div class="text-primary text-2xl font-extrabold leading-none">9</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Subkriteria</div>
            <div class="text-primary text-2xl font-extrabold leading-none">18</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Indikator</div>
            <div class="text-primary text-2xl font-extrabold leading-none">36</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Jenis Bukti</div>
            <div class="text-primary text-2xl font-extrabold leading-none">6</div>
        </div>
        <div class="bg-white border border-[#EAEAEA] p-5 rounded-2xl shadow-xs">
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Status</div>
            <div class="text-primary text-sm font-extrabold leading-none uppercase {{ $isInProgress ? 'text-amber-600' : 'text-slate-600' }}">
                {{ $statusLabel }}
            </div>
        </div>
    </div>

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

    <!-- Bagian Matriks Kriteria & Monitoring Borang -->
    <div class="mt-8 bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
        <div class="p-6 border-b border-[#EAEAEA] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-extrabold text-primary">Matriks Kriteria & Monitoring Borang</h3>
                <p class="text-gray-500 text-xs sm:text-sm mt-1">Pantau kriteria borang, subkriteria, dan kelengkapan bukti dokumen per indikator.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                    9 Kriteria · 36 Indikator
                </span>
            </div>
        </div>

        <div class="flex flex-col">
            @foreach($kriteriaList as $index => $item)
                @php
                    $cId = strtolower($item['code']);
                    $isOpen = $item['isOpen'];
                    $isLast = $loop->last;
                @endphp

                <div class="flex flex-col {{ ! $isLast ? 'border-b border-[#EAEAEA]' : '' }}">
                    <!-- Header Accordion Kriteria -->
                    <div
                        onclick="toggleAccordion('{{ $cId }}')"
                        class="flex items-center justify-between p-4 sm:p-5 hover:bg-gray-50 transition-colors group cursor-pointer bg-white select-none"
                    >
                        <div class="flex items-center gap-3.5">
                            <span class="text-additional text-xs sm:text-sm font-extrabold w-9 sm:w-10 text-center bg-additional/10 py-1.5 rounded-lg shrink-0">
                                {{ $item['code'] }}
                            </span>
                            <span id="title-{{ $cId }}" class="text-base font-bold transition-colors {{ $isOpen ? 'text-primary' : 'text-gray-800 group-hover:text-primary' }}">
                                {{ $item['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 sm:gap-6 shrink-0">
                            <span class="text-gray-400 text-xs font-bold" id="badge-{{ $cId }}">Terpantau</span>
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gray-100 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-colors">
                                <span id="icon-{{ $cId }}" class="text-lg font-bold leading-none mb-0.5">
                                    {{ $isOpen ? '−' : '+' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Konten Expand Subkriteria & Indikator -->
                    <div id="content-{{ $cId }}" class="{{ $isOpen ? 'flex' : 'hidden' }} flex-col bg-white">
                        @foreach($item['subkriteria'] as $sub)
                            <!-- Subkriteria Header -->
                            <div class="px-5 py-2.5 border-t border-b border-[#EAEAEA] bg-gray-50/70">
                                <span class="text-gray-500 text-xs font-bold tracking-wider uppercase">
                                    {{ $sub['code'] }} · {{ $sub['name'] }}
                                </span>
                            </div>

                            <!-- List Indikator -->
                            @foreach($sub['indikator'] as $ind)
                                <div class="flex flex-col border-b border-[#EAEAEA] last:border-b-0">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between px-5 py-3.5 gap-4">
                                        <div class="flex-1 text-xs sm:text-sm font-medium text-gray-800">
                                            <span class="text-additional font-bold mr-1.5">{{ $ind['code'] }}</span>
                                            {{ $ind['name'] }}
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0 select-none">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Borang Tersedia
                                            </span>
                                        </div>
                                    </div>
                                    <div class="px-5 pb-3">
                                        <p class="text-gray-400 text-xs font-normal">
                                            {{ $ind['desc'] }} · Bobot <span class="text-gray-600 font-bold">{{ $ind['bobot'] }}</span>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endforeach
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
</script>
@endsection
