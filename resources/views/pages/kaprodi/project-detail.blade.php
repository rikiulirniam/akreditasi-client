@extends('layouts.app')

@section('title', 'Detail Project Akreditasi')

@section('content')
<div class="space-y-6 pb-10">
    
    <!-- Toast Notification Component -->
    <x-toast />

    @php
        $namaProject = $project['nama_project'] ?? 'Sarjana Terapan Test';
        $versiNomor = $project['versi_instrumen']['nomor_versi'] ?? '2.1';
        $jenjangNama = $project['jenjang']['nama_jenjang'] ?? 'Sarjana Terapan';
        $jenisNama = $project['jenis_instrumen']['nama_jenis'] ?? ($project['status_project'] ?? 'Unggul');
        $tahunTarget = $project['tahun_target_submit'] ?? 2026;
    @endphp

    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight leading-tight">Project Akreditasi</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">Detail parameter instrumen, capaian kriteria, dan simulasi penilaian mandiri dari server akreditasi.</p>
        </div>
        <div class="flex items-center gap-3">
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

    <!-- 2 Kotak Utama (Informasi & Nilai) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-2xl border border-[#EAEAEA] p-6 flex flex-col justify-center shadow-xs">
            <div class="text-additional text-[10px] font-bold tracking-widest uppercase mb-2">
                LAM INFOKOM · V{{ $versiNomor }} · {{ strtoupper($jenjangNama) }}
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-primary mb-1 tracking-tight">{{ $namaProject }}</h2>
            <p class="text-gray-500 text-xs sm:text-sm font-medium">{{ $jenisNama }} · Tahun {{ $tahunTarget }}</p>
        </div>

        <div class="md:col-span-1 bg-white rounded-2xl border border-[#EAEAEA] p-6 flex flex-col justify-center shadow-xs">
            <div class="text-additional text-xs font-bold mb-1 uppercase tracking-wider">Nilai Simulasi</div>
            <div class="flex items-baseline gap-1 mb-2">
                <span id="score-display" class="text-4xl font-extrabold text-primary leading-none tracking-tight">0.0</span>
                <span class="text-base font-bold text-gray-400">/400</span>
            </div>
            <div class="text-xs font-medium">
                <span id="score-status" class="text-primary font-bold">Belum dinilai</span>
                <span id="score-counter" class="text-gray-400"> · 0/36 indikator</span>
            </div>
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
            <div class="text-gray-400 text-[10px] font-bold uppercase tracking-wider mb-2">Matriks</div>
            <div class="text-primary text-2xl font-extrabold leading-none">180</div>
        </div>
    </div>

    @php
        // Ambil data dari API atau gunakan fallback lengkap 9 kriteria
        $apiC1 = !empty($apiKriteria[0]) ? $apiKriteria[0] : null;

        $kriteriaList = [
            [
                'code' => $apiC1['kode_kriteria'] ?? 'C1',
                'name' => $apiC1['nama_kriteria'] ?? 'Visi, Misi, Tujuan, dan Strategi',
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
                'name' => 'Tata Pamong, Tata Kelola, dan Kerja Sama',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C2.1',
                        'name' => 'Sistem Tata Pamong & Kepemimpinan',
                        'indikator' => [
                            [
                                'code' => 'C2.1.1',
                                'name' => 'Ketersediaan dokumen formal tata pamong dan struktur organisasi',
                                'desc' => 'Kejelasan tugas pokok dan fungsi pada unit pengelola program studi.',
                                'bobot' => '3.0',
                            ],
                            [
                                'code' => 'C2.1.2',
                                'name' => 'Efektivitas sistem penjaminan mutu internal (SPMI)',
                                'desc' => 'Keterlaksanaan siklus PPEPP secara berkelanjutan.',
                                'bobot' => '3.5',
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
                        'name' => 'Rekrutmen & Layanan Kemahasiswaan',
                        'indikator' => [
                            [
                                'code' => 'C3.1.1',
                                'name' => 'Rasio pendaftar dan mahasiswa baru yang melakukan registrasi',
                                'desc' => 'Kualitas input calon mahasiswa baru dan daya tarik prodi.',
                                'bobot' => '2.5',
                            ],
                            [
                                'code' => 'C3.1.2',
                                'name' => 'Ketersediaan dan kepuasan layanan bimbingan karir serta penalaran',
                                'desc' => 'Ketersediaan fasilitas penunjang prestasi akademik & non-akademik.',
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
                        'name' => 'Profil & Kualifikasi Dosen',
                        'indikator' => [
                            [
                                'code' => 'C4.1.1',
                                'name' => 'Kecukupan dan kualifikasi akademik Dosen Tetap Program Studi (DTPS)',
                                'desc' => 'Kesesuaian bidang keahlian DTPS dengan kompetensi lulusan.',
                                'bobot' => '4.0',
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
                        'name' => 'Kecukupan Anggaran & Fasilitas Pembelajaran',
                        'indikator' => [
                            [
                                'code' => 'C5.1.1',
                                'name' => 'Kecukupan dana operasional, sarana laboratorium, dan lisensi perangkat lunak',
                                'desc' => 'Ketersediaan sarana prasarana penunjang praktikum mahasiswa.',
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
                                'name' => 'Kesesuaian Capaian Pembelajaran Lulusan (CPL) dengan standar KKNI/OBE',
                                'desc' => 'Evaluasi berkala kurikulum bersama alumni dan pengguna lulusan.',
                                'bobot' => '4.0',
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
                        'name' => 'Roadmap & Publikasi Riset',
                        'indikator' => [
                            [
                                'code' => 'C7.1.1',
                                'name' => 'Relevansi penelitian DTPS dengan peta jalan keilmuan program studi',
                                'desc' => 'Keterlibatan mahasiswa dalam penelitian dosen.',
                                'bobot' => '3.0',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'C8',
                'name' => 'Pengabdian kepada Masyarakat',
                'isOpen' => false,
                'subkriteria' => [
                    [
                        'code' => 'C8.1',
                        'name' => 'Penerapan Hasil Iptek ke Masyarakat',
                        'indikator' => [
                            [
                                'code' => 'C8.1.1',
                                'name' => 'Kuantitas dan kualitas kegiatan PkM yang melibatkan mahasiswa',
                                'desc' => 'Pemanfaatan hasil luaran PkM oleh mitra industri atau masyarakat.',
                                'bobot' => '3.0',
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

    <!-- Bagian Matriks Penilaian -->
    <div class="mt-8 bg-white rounded-2xl border border-[#EAEAEA] shadow-xs overflow-hidden">
        <div class="p-6 border-b border-[#EAEAEA]">
            <h3 class="text-lg font-extrabold text-primary">Matriks Penilaian</h3>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">Pilih kriteria borang, lalu tentukan skor tiap indikator (0 - 4).</p>
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
                            <span class="text-gray-500 text-xs font-bold" id="score-counter-{{ $cId }}">0/4</span>
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
                                        <div class="flex items-center gap-1.5 shrink-0 select-none">
                                            @for($s = 0; $s <= 4; $s++)
                                                <button 
                                                    type="button" 
                                                    onclick="selectScore(this, {{ $s }}, '{{ $cId }}')" 
                                                    class="score-btn w-9 h-9 rounded-lg border border-[#EAEAEA] text-gray-600 text-xs sm:text-sm font-bold hover:border-additional hover:text-additional transition-colors cursor-pointer"
                                                >
                                                    {{ $s }}
                                                </button>
                                            @endfor
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

<script>
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

    // --- FUNGSI SIMULASI PENILAIAN INDIKATOR ---
    const scores = {};

    function selectScore(button, score, cId) {
        const parent = button.parentElement;
        const buttons = parent.querySelectorAll('.score-btn');
        
        // Reset warna tombol di baris indikator ini
        buttons.forEach(btn => {
            btn.classList.remove('bg-additional', 'text-white', 'border-additional');
            btn.classList.add('border-[#EAEAEA]', 'text-gray-600');
        });

        // Set tombol aktif
        button.classList.add('bg-additional', 'text-white', 'border-additional');
        button.classList.remove('border-[#EAEAEA]', 'text-gray-600');

        // Simpan nilai
        const indRow = button.closest('.flex-col');
        const indCode = indRow?.querySelector('.text-additional')?.innerText || Math.random().toString();
        scores[indCode] = score;

        // Hitung ulang total nilai
        updateScoreSummary();
    }

    function updateScoreSummary() {
        const totalIndikator = Object.keys(scores).length;
        if (totalIndikator === 0) return;

        let sum = 0;
        for (const key in scores) {
            sum += scores[key];
        }

        // Skor skala 400
        const calculated = ((sum / (totalIndikator * 4)) * 400).toFixed(1);

        const scoreDisplay = document.getElementById('score-display');
        if (scoreDisplay) scoreDisplay.innerText = calculated;

        const scoreStatus = document.getElementById('score-status');
        if (scoreStatus) {
            scoreStatus.innerText = 'Sedang dinilai';
            scoreStatus.classList.add('text-emerald-600');
            scoreStatus.classList.remove('text-primary');
        }

        const scoreCounter = document.getElementById('score-counter');
        if (scoreCounter) {
            scoreCounter.innerText = ` · ${totalIndikator}/36 indikator`;
        }
    }
</script>
@endsection