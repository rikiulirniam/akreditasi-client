@extends('layouts.app')

@section('title', 'Detail Lulusan')

@section('content')
@php
    $nama           = $lulusan['nama_mahasiswa'] ?? '-';
    $nim            = $lulusan['nim_display'] ?? $lulusan['nim'] ?? '-';
    $jenjang        = $lulusan['jenjang_prodi'] ?? '-';
    $tahunMasuk     = $lulusan['tahun_masuk'] ?? '-';
    $rawTgl         = $lulusan['tanggal_kelulusan'] ?? '';
    $tglLulus       = !empty($rawTgl) ? \Carbon\Carbon::parse($rawTgl)->translatedFormat('d F Y') : '-';
    $masaStudi      = $lulusan['masa_studi_bulan'] ?? null;
    $batasMasaStudi = $lulusan['batas_masa_studi_bulan'] ?? null;
    $masaTunggu     = $lulusan['masa_tunggu_bulan'] ?? null;
    $tempatKerja    = $lulusan['tempat_kerja'] ?? null;
    $ipk            = $lulusan['ipk_riil'] ?? null;
    $totalSks       = $lulusan['total_sks'] ?? null;
    $lulusTepatWaktu = $lulusan['lulus_tepat_waktu'] ?? null;
    $prodi          = $lulusan['nama_prodi'] ?? $lulusan['prodi'] ?? '-';

    // Badge warna kelulusan
    if ($lulusTepatWaktu === true) {
        $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        $badgeLabel = 'Tepat Waktu';
        $badgeIcon  = 'm4.5 12.75 6 6 9-13.5';
    } elseif ($lulusTepatWaktu === false) {
        $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
        $badgeLabel = 'Terlambat';
        $badgeIcon  = 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
    } else {
        $badgeClass = 'bg-gray-100 text-gray-500 border-gray-200';
        $badgeLabel = 'N/A';
        $badgeIcon  = null;
    }
@endphp

<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Detail Lulusan</h2>
        </div>
        <div>
            <a
                href="{{ route('lulusan.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Lulusan
            </a>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Card Header: Nama & Badge Kelulusan -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ $nama }}</h3>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="font-mono text-sm text-gray-500 font-semibold">{{ $nim }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-700">
                        {{ $jenjang }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                @if($lulusTepatWaktu !== null)
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $badgeClass }} shadow-xs">
                        @if($badgeIcon)
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $badgeIcon }}" />
                            </svg>
                        @endif
                        {{ $badgeLabel }}
                    </span>
                @endif

                @if(!empty($lulusan['id']) || !empty($lulusan['nim']))
                    <button
                        type="button"
                        class="open-edit-lulusan-btn inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[#EAB308] hover:bg-[#CA8A04] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                        data-action="{{ route('lulusan.update', $lulusan['id'] ?? $lulusan['nim']) }}"
                        data-nama="{{ $nama }}"
                        data-nim="{{ $nim }}"
                        data-tahun="{{ $tahunMasuk }}"
                        data-jenis="{{ $lulusan['jenis_pendaftaran'] ?? 'baru' }}"
                        data-kelulusan="{{ !empty($rawTgl) ? \Carbon\Carbon::parse($rawTgl)->format('Y-m-d') : '' }}"
                        data-masa-studi="{{ $masaStudi ?? '' }}"
                        data-masa-tunggu="{{ $masaTunggu ?? '' }}"
                        data-tempat-kerja="{{ $tempatKerja ?? '' }}"
                    >
                        <x-icon.edit class="w-4 h-4" />
                        Edit Data
                    </button>
                @endif
            </div>
        </div>

        <hr class="border-gray-200 my-5">

        <!-- Info Grid: 2 Columns -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs">
            <!-- Kolom Kiri -->
            <div class="space-y-4">
                <!-- Program Studi -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0-.36.03-.715" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Program Studi:</span>
                        {{ $prodi }}
                    </p>
                </div>

                <!-- Tahun Masuk -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tahun Masuk:</span>
                        {{ $tahunMasuk }}
                    </p>
                </div>

                <!-- Tanggal Lulus -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tanggal Lulus:</span>
                        {{ $tglLulus }}
                    </p>
                </div>

                <!-- Masa Studi -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Masa Studi:</span>
                        @if($masaStudi !== null)
                            <span class="ml-1">{{ $masaStudi }} bln</span>
                            @if($batasMasaStudi !== null)
                                <span class="text-gray-400">/ {{ $batasMasaStudi }} bln</span>
                            @endif
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>

                <!-- IPK -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">IPK:</span>
                        @if($ipk !== null)
                            <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ (float)$ipk >= 3.5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-800' }}">
                                {{ number_format((float)$ipk, 2) }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="space-y-4">
                <!-- Masa Tunggu -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Masa Tunggu Kerja:</span>
                        @if($masaTunggu !== null)
                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ (int)$masaTunggu <= 6 ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $masaTunggu }} bln
                            </span>
                            <span class="ml-1 text-gray-400">{{ (int)$masaTunggu <= 6 ? '(Memenuhi standar)' : '(Di atas 6 bln)' }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>

                <!-- Tempat Kerja -->
                <div class="flex items-start gap-3">
                    <span class="text-gray-500 shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Tempat Kerja:</span>
                        @if(!empty($tempatKerja))
                            <span class="ml-1 font-semibold text-gray-800">{{ $tempatKerja }}</span>
                        @else
                            <span class="ml-1 text-gray-400 italic">Belum Bekerja / Belum Diisi</span>
                        @endif
                    </p>
                </div>

                <!-- Total SKS -->
                @if($totalSks !== null)
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2 2 0 002-2V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Total SKS:</span>
                        <span class="ml-1">{{ $totalSks }} SKS</span>
                    </p>
                </div>
                @endif

                <!-- Status Kelulusan -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Status Kelulusan:</span>
                        @if($lulusTepatWaktu !== null)
                            <span class="ml-1 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $badgeClass }}">
                                {{ $badgeLabel }}
                            </span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editModal = document.getElementById('editLulusanModal');
        const editForm = document.getElementById('editLulusanForm');
        const editNama = document.getElementById('editNamaInput');
        const editNim = document.getElementById('editNimInput');
        const editTahun = document.getElementById('editTahunInput');
        const editJenis = document.getElementById('editJenisInput');
        const editKelulusan = document.getElementById('editKelulusanInput');
        const editMasaStudi = document.getElementById('editMasaStudiInput');
        const editMasaTunggu = document.getElementById('editMasaTungguInput');
        const editTempatKerja = document.getElementById('editTempatKerjaInput');

        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.lulusan-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.lulusan-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        function populateAndShowEdit(data) {
            if (editForm) editForm.action = data.action || '';
            if (editNama) editNama.value = data.nama || '';
            if (editNim) editNim.value = data.nim || '';
            if (editTahun) editTahun.value = data.tahun || '';
            if (editJenis) {
                editJenis.value = data.jenis || 'baru';
                if (!editJenis.value && data.jenis) {
                    for (let opt of editJenis.options) {
                        if (opt.value.toLowerCase() === data.jenis.toLowerCase()) {
                            opt.selected = true;
                            break;
                        }
                    }
                }
            }
            if (editKelulusan) editKelulusan.value = data.kelulusan || '';
            if (editMasaStudi) editMasaStudi.value = data.masaStudi || '48';
            if (editMasaTunggu) editMasaTunggu.value = data.masaTunggu || '';
            if (editTempatKerja) editTempatKerja.value = data.tempatKerja || '';

            showModal(editModal);
            setTimeout(() => editKelulusan?.focus(), 100);
        }

        document.querySelectorAll('.open-edit-lulusan-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                populateAndShowEdit({
                    action: this.getAttribute('data-action'),
                    nama: this.getAttribute('data-nama'),
                    nim: this.getAttribute('data-nim'),
                    tahun: this.getAttribute('data-tahun'),
                    jenis: this.getAttribute('data-jenis'),
                    kelulusan: this.getAttribute('data-kelulusan'),
                    masaStudi: this.getAttribute('data-masa-studi'),
                    masaTunggu: this.getAttribute('data-masa-tunggu'),
                    tempatKerja: this.getAttribute('data-tempat-kerja')
                });
            });
        });

        document.querySelectorAll('.close-lulusan-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        if (editModal) {
            editModal.addEventListener('click', function (e) {
                if (e.target === editModal) hideModal(editModal);
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(editModal);
            }
        });
    });
</script>

@include('lulusan.partials.edit-modal')
@endsection
