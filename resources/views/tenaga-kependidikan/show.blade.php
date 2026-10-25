@extends('layouts.app')

@section('meta_title', 'Detail Tenaga Kependidikan')

@section('title')
    <span class="inline-flex items-center gap-2.5 sm:gap-3.5 flex-wrap">
        <a href="{{ route('tenaga-kependidikan.index') }}" class="hover:text-additional transition text-black">Tenaga Pendidikan</a>
        <svg class="w-5 h-5 sm:w-7 sm:h-7 stroke-[3] text-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <span>Detail</span>
    </span>
@endsection

@section('subtitle', $tenagaKependidikan['nama'])

@section('content')
<div class="space-y-4 pb-2">
    @if($detailApiError)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-medium text-amber-900" role="alert">
            {{ $detailApiError }}
        </div>
    @endif

    {{-- Top Grid: Identitas + Riwayat Pendidikan --}}
    <div class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-12" style="min-height: 420px;">

        {{-- Identitas --}}
        <section class="overflow-hidden rounded-[8px] bg-[#EEF3F7] lg:col-span-8 flex flex-col" aria-label="Identitas tenaga kependidikan">
            <div class="px-6 pt-6 pb-0">
                <h2 class="text-2xl font-bold leading-tight text-black sm:text-[26px]">
                    {{ $tenagaKependidikan['nama'] ?: 'Nama belum tersedia' }}
                </h2>
                <p class="mt-1 text-sm font-medium text-gray-500">
                    {{ $tenagaKependidikan['jabatan'] ?: 'Belum diisi' }}
                </p>
            </div>

            <div class="mt-6 border-t border-[#BAC7D5] divide-y divide-[#BAC7D5]">
                <div class="flex h-[46px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['bidang_keahlian'] ?: 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[46px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v9A2.25 2.25 0 0 1 18.75 16.5H5.25A2.25 2.25 0 0 1 3 14.25v-9ZM8.25 21h7.5M12 16.5V21m-5.25 0h10.5" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['kualifikasi'] !== '-' ? $tenagaKependidikan['kualifikasi'] : 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[46px] items-center gap-3 px-6 text-xs font-medium text-black">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.75 12 13.5l9-6.75M4.5 4.5h15A1.5 1.5 0 0 1 21 6v12a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['email'] ?: 'Belum diisi' }}</span>
                </div>
                <div class="flex h-[46px] items-center gap-3 px-6 text-xs font-medium text-black border-b border-[#BAC7D5]">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h7.5M8.25 10.5h7.5m-7.5 3.75h4.5M6 3.75h9.75L19.5 7.5v12.75A1.5 1.5 0 0 1 18 21.75H6A1.5 1.5 0 0 1 4.5 20.25V5.25A1.5 1.5 0 0 1 6 3.75Z" /></svg>
                    <span class="truncate">{{ $tenagaKependidikan['status'] ?: 'Belum diisi' }}</span>
                </div>
            </div>

            {{-- Spacer: mengisi sisa tinggi agar card sepanjang Riwayat Pendidikan --}}
            <div class="flex-1"></div>
        </section>

        {{-- Riwayat Pendidikan --}}
        <section class="overflow-hidden rounded-[8px] bg-[#EEF3F7] lg:col-span-4 flex flex-col shadow-xs" aria-labelledby="qualificationHeading">
            <div class="flex h-[47px] items-center justify-start bg-[#002B49] px-5 shrink-0">
                <h3 id="qualificationHeading" class="text-base font-semibold text-white tracking-wide">Riwayat Pendidikan</h3>
            </div>

            {{-- Daftar riwayat pendidikan --}}
            <div class="flex-1 overflow-y-auto divide-y divide-[#BAC7D5] px-4 sm:px-5">
                @forelse($kualifikasis as $qualification)
                    @php
                        $rawJenjang = !empty($qualification['jenjang']) ? $qualification['jenjang'] : '';
                        $rawGelar   = !empty($qualification['gelar']) ? $qualification['gelar'] : '';
                        $isJenjangCode = in_array(strtoupper($rawGelar), ['D3', 'D4', 'S1', 'D4/S1', 'S2', 'S3', 'PROFESI']);
                        $jenjang   = $rawJenjang !== '' ? $rawJenjang : ($isJenjangCode ? $rawGelar : 'S1');
                        $gelar     = ($rawGelar !== '' && ! $isJenjangCode) ? $rawGelar : ($jenjang === 'S3' ? 'Dr' : ($jenjang === 'S2' ? 'M.Kom' : ($jenjang === 'D4/S1' || $jenjang === 'S1' ? 'S.Kom' : '')));
                        $institusi = !empty($qualification['institusi']) ? $qualification['institusi'] : (!empty($qualification['universitas']) ? $qualification['universitas'] : '');
                        $prodi     = !empty($qualification['program_studi']) ? $qualification['program_studi'] : '';
                        $tahun     = !empty($qualification['tahun_lulus']) ? $qualification['tahun_lulus'] : '';
                    @endphp
                    <article class="flex items-center gap-3.5 py-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#FFC400] text-black">
                            <svg class="h-5 w-5 fill-current text-black" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 3 1 9l11 6 9-4.91V17h2V9L12 3Zm-7 10.18v4L12 21l7-3.82v-4L12 17l-7-3.82Z"/>
                            </svg>
                        </span>
                        <div class="min-w-0 flex-1 leading-snug">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center rounded bg-[#002B49] px-2 py-0.5 text-[10px] sm:text-xs font-bold text-white">{{ $jenjang }}</span>
                                @if($gelar !== '')
                                    <span class="sr-only">{{ $gelar }}</span>
                                @endif
                            </div>
                            @if($institusi !== '')
                                <p class="truncate text-xs font-normal text-gray-700 mt-1">{{ $institusi }}</p>
                            @endif
                            @if($prodi !== '' || $tahun !== '')
                                <p class="truncate text-xs font-bold text-black mt-0.5">
                                    {{ $prodi }}{{ ($prodi !== '' && $tahun !== '') ? ' - Lulus '.$tahun : ($tahun !== '' ? 'Lulus '.$tahun : '') }}
                                </p>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center text-gray-500">
                        <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        <p class="text-xs font-medium text-gray-600">Belum ada data riwayat pendidikan</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Klik 'Tambah Pendidikan' di bawah untuk menambahkan</p>
                    </div>
                @endforelse
            </div>

            {{-- Tombol Tambah Pendidikan — mengarah ke halaman Pendidikan --}}
            <a
                href="{{ route('pendidikan.index', ['id_tendik' => $tenagaKependidikan['id']]) }}"
                class="w-full bg-[#002B49] hover:bg-[#003B66] h-[40px] px-4 text-white text-center flex items-center justify-center gap-2 transition duration-150 shrink-0"
                title="Tambah Pendidikan"
            >
                <svg class="w-4 h-4 text-white shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs font-medium tracking-wide">Tambah Pendidikan</span>
            </a>
        </section>
    </div>

    {{-- Sertifikasi --}}
    <section class="overflow-hidden rounded-[8px] bg-[#EEF3F7]" aria-labelledby="certificationHeading">
        <div class="flex h-[47px] items-center justify-between bg-[#002B49] px-4 sm:px-6">
            <h3 id="certificationHeading" class="text-base font-semibold text-white tracking-wide">Sertifikasi</h3>
            {{-- Tombol Tambah Sertifikasi --}}
            <button
                type="button"
                id="openAddSertifikasiModalBtn"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-[6px] bg-[#00629B] hover:bg-[#005180] text-white text-xs font-semibold transition shadow-sm cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                </svg>
                <span>Tambah Sertifikasi</span>
            </button>
        </div>

        @if($detailApiError)
            <p class="mx-4 mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900" role="alert">{{ $detailApiError }}</p>
        @endif

        @if(count($sertifikasis) > 0)
            {{-- List sertifikasi — setiap baris full-width, dipisah border --}}
            <div class="divide-y divide-[#BAC7D5]">
                @foreach($sertifikasis as $sertifikasi)
                    @php
                        $sertifikasiId = $sertifikasi['id'] ?? $sertifikasi['id_sertifikasi'] ?? null;
                        $tahunSertifikasi = $sertifikasi['tahun_sertifikasi'] ?? null;
                        $tahunKadaluwarsa = isset($sertifikasi['tanggal_kedaluwarsa'])
                            ? \Illuminate\Support\Str::before($sertifikasi['tanggal_kedaluwarsa'], '-')
                            : null;
                        $status    = $sertifikasi['status_sertifikasi'] ?? $sertifikasi['status'] ?? 'Aktif';
                        $isExpired = \Illuminate\Support\Str::lower($status) === 'kadaluarsa'
                                  || \Illuminate\Support\Str::lower($status) === 'expired'
                                  || \Illuminate\Support\Str::lower($status) === 'kedaluwarsa';
                    @endphp
                    <div class="flex items-center justify-between px-4 sm:px-6 py-3 hover:bg-black/[0.03] transition">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-black uppercase tracking-wide leading-tight">
                                {{ $sertifikasi['jenis_sertifikasi'] ?? 'Sertifikasi' }}
                            </p>
                            @if(!empty($sertifikasi['lembaga_penerbit']))
                                <p class="text-xs text-gray-500 mt-0.5">{{ $sertifikasi['lembaga_penerbit'] }}</p>
                            @endif
                            <div class="mt-1.5 flex flex-wrap gap-2">
                                @if($tahunSertifikasi)
                                    <span class="inline-flex items-center rounded-full bg-[#002B49] px-2.5 py-1 text-[11px] font-semibold text-[#FFC400]">
                                        Tahun Sertifikasi: {{ $tahunSertifikasi }}
                                    </span>
                                @endif
                                @if($tahunKadaluwarsa)
                                    <span class="inline-flex items-center rounded-full {{ $isExpired ? 'bg-red-100 text-red-700' : 'bg-[#002B49] text-[#FFC400]' }} px-2.5 py-1 text-[11px] font-semibold">
                                        Tahun Kadaluwarsa: {{ $tahunKadaluwarsa }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($sertifikasiId)
                            <div class="flex items-center gap-2.5 shrink-0 ml-4">
                                <a href="{{ route('sertifikasi.show', $sertifikasiId) }}" class="text-[#00629B] hover:text-[#002B49] p-1 transition" title="Lihat detail sertifikasi">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </a>
                                <a href="{{ route('sertifikasi.edit', $sertifikasiId) }}" class="text-amber-500 hover:text-amber-700 p-1 transition" title="Edit sertifikasi">
                                    <x-icon.edit class="h-4 w-4" />
                                </a>
                                <form method="POST" action="{{ route('tenaga-pendidikan.sertifikasi.destroy', [$tenagaKependidikan['id'], $sertifikasiId]) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus sertifikasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1 transition cursor-pointer" title="Hapus sertifikasi">
                                        <x-icon.delete class="h-4 w-4" />
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex py-28 sm:py-36 items-center justify-center text-center">
                <p class="text-3xl sm:text-4xl lg:text-[40px] font-bold text-black tracking-tight">Belum ada sertifikasi</p>
            </div>
        @endif
    </section>
</div>

{{-- Modal Tambah Pendidikan --}}
<div
    id="addPendidikanModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="addPendidikanModalTitle"
>
    <div class="relative w-full max-w-md mx-4 bg-white rounded-[10px] shadow-xl overflow-hidden">
        <div class="flex items-center justify-between h-[52px] bg-[#002B49] px-5">
            <h4 id="addPendidikanModalTitle" class="text-sm font-semibold text-white tracking-wide">Tambah Pendidikan</h4>
            <button type="button" id="closeAddPendidikanModalBtn" class="text-white/70 hover:text-white transition cursor-pointer" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('tenaga-kependidikan.pendidikan.store', $tenagaKependidikan['id']) }}"
            class="p-5 space-y-4"
            id="addPendidikanForm"
        >
            @csrf

            <div>
                <label for="pendidikan_jenjang" class="block text-xs font-semibold text-gray-700 mb-1">Jenjang <span class="text-red-500">*</span></label>
                <select name="jenjang" id="pendidikan_jenjang" required
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
                    <option value="" disabled selected>Isi Pendidikan Terakhir</option>
                    <option value="D4/S1">D4/S1</option>
                    <option value="S2">S2</option>
                    <option value="S3">S3</option>
                    <option value="D3">D3</option>
                    <option value="Profesi">Profesi</option>
                </select>
            </div>

            <div>
                <label for="pendidikan_gelar" class="block text-xs font-semibold text-gray-700 mb-1">Gelar</label>
                <input type="text" name="gelar" id="pendidikan_gelar" placeholder="Contoh: Dr, S.Kom, M.T"
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
            </div>

            <div>
                <label for="pendidikan_institusi" class="block text-xs font-semibold text-gray-700 mb-1">Institusi</label>
                <input type="text" name="institusi" id="pendidikan_institusi" placeholder="Contoh: Politeknik Negeri Semarang"
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
            </div>

            <div>
                <label for="pendidikan_program_studi" class="block text-xs font-semibold text-gray-700 mb-1">Program Studi</label>
                <input type="text" name="program_studi" id="pendidikan_program_studi" placeholder="Contoh: Teknik Informatika"
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
            </div>

            <div>
                <label for="pendidikan_tahun_lulus" class="block text-xs font-semibold text-gray-700 mb-1">Tahun Lulus</label>
                <input type="text" name="tahun_lulus" id="pendidikan_tahun_lulus" placeholder="Contoh: 2020"
                    maxlength="4" inputmode="numeric" pattern="\d{4}"
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
            </div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" id="cancelAddPendidikanBtn"
                    class="h-[38px] px-5 rounded-[6px] border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                    class="h-[38px] px-6 rounded-[6px] bg-[#002B49] hover:bg-[#003B66] text-xs font-semibold text-white transition cursor-pointer">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Sertifikasi --}}
<div
    id="addSertifikasiModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="addSertifikasiModalTitle"
>
    <div class="relative w-full max-w-lg mx-4 bg-white rounded-[10px] shadow-xl overflow-hidden">
        <div class="flex items-center justify-between h-[52px] bg-[#002B49] px-5">
            <h4 id="addSertifikasiModalTitle" class="text-sm font-semibold text-white tracking-wide">Tambah Sertifikasi</h4>
            <button type="button" id="closeAddSertifikasiModalBtn" class="text-white/70 hover:text-white transition cursor-pointer" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form
            method="POST"
            action="{{ route('tenaga-pendidikan.sertifikasi.store', $tenagaKependidikan['id']) }}"
            enctype="multipart/form-data"
            class="p-5 space-y-3.5"
            id="addSertifikasiForm"
        >
            @csrf

            <div>
                <label for="sertifikasi_jenis" class="block text-xs font-semibold text-gray-700 mb-1">Nama / Jenis Sertifikasi <span class="text-red-500">*</span></label>
                <input type="text" name="jenis_sertifikasi" id="sertifikasi_jenis" required placeholder="Contoh: Sertifikasi Kompetensi Jaringan"
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="sertifikasi_tahun" class="block text-xs font-semibold text-gray-700 mb-1">Tahun Sertifikasi <span class="text-red-500">*</span></label>
                    <input type="text" name="tahun_sertifikasi" id="sertifikasi_tahun" required placeholder="Contoh: 2025" maxlength="4" inputmode="numeric"
                        class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
                </div>
                <div>
                    <label for="sertifikasi_status" class="block text-xs font-semibold text-gray-700 mb-1">Status Sertifikasi</label>
                    <select name="status_sertifikasi" id="sertifikasi_status"
                        class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
                        <option value="Aktif" selected>Aktif</option>
                        <option value="Kedaluwarsa">Kedaluwarsa</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="sertifikasi_nomor" class="block text-xs font-semibold text-gray-700 mb-1">Nomor Sertifikat</label>
                    <input type="text" name="nomor_sertifikat" id="sertifikasi_nomor" placeholder="Contoh: CERT-2025-001"
                        class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
                </div>
                <div>
                    <label for="sertifikasi_lembaga" class="block text-xs font-semibold text-gray-700 mb-1">Lembaga Penerbit</label>
                    <input type="text" name="lembaga_penerbit" id="sertifikasi_lembaga" placeholder="Contoh: BNSP"
                        class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
                </div>
            </div>

            <div>
                <label for="sertifikasi_url" class="block text-xs font-semibold text-gray-700 mb-1">Tautan / Dokumen Drive</label>
                <input type="url" name="url_drive" id="sertifikasi_url" placeholder="https://drive.google.com/..."
                    class="w-full h-[38px] rounded-[6px] border border-gray-300 bg-white px-3 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#002B49]/30 transition">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => $tenagaKependidikan['id']]) }}" class="text-xs text-[#00629B] hover:underline font-semibold">
                    Buka Halaman Sertifikasi &rarr;
                </a>
                <div class="flex items-center gap-2">
                    <button type="button" id="cancelAddSertifikasiBtn"
                        class="h-[38px] px-4 rounded-[6px] border border-gray-300 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="h-[38px] px-5 rounded-[6px] bg-[#002B49] hover:bg-[#003B66] text-xs font-semibold text-white transition cursor-pointer">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function initModals() {
        // ── Modal Tambah Pendidikan ──
        const pendidikanModal   = document.getElementById('addPendidikanModal');
        const openPendidikanBtn   = document.getElementById('openAddPendidikanModalBtn');
        const closePendidikanBtn  = document.getElementById('closeAddPendidikanModalBtn');
        const cancelPendidikanBtn = document.getElementById('cancelAddPendidikanBtn');

        function openModal(modal) {
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(modal) {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        if (openPendidikanBtn && pendidikanModal) {
            openPendidikanBtn.addEventListener('click', () => openModal(pendidikanModal));
        }
        if (closePendidikanBtn && pendidikanModal) {
            closePendidikanBtn.addEventListener('click', () => closeModal(pendidikanModal));
        }
        if (cancelPendidikanBtn && pendidikanModal) {
            cancelPendidikanBtn.addEventListener('click', () => closeModal(pendidikanModal));
        }
        if (pendidikanModal) {
            pendidikanModal.addEventListener('click', (e) => {
                if (e.target === pendidikanModal) closeModal(pendidikanModal);
            });
        }

        // ── Modal Tambah Sertifikasi ──
        const sertifikasiModal   = document.getElementById('addSertifikasiModal');
        const openSertifikasiBtn   = document.getElementById('openAddSertifikasiModalBtn');
        const closeSertifikasiBtn  = document.getElementById('closeAddSertifikasiModalBtn');
        const cancelSertifikasiBtn = document.getElementById('cancelAddSertifikasiBtn');

        if (openSertifikasiBtn && sertifikasiModal) {
            openSertifikasiBtn.addEventListener('click', () => openModal(sertifikasiModal));
        }
        if (closeSertifikasiBtn && sertifikasiModal) {
            closeSertifikasiBtn.addEventListener('click', () => closeModal(sertifikasiModal));
        }
        if (cancelSertifikasiBtn && sertifikasiModal) {
            cancelSertifikasiBtn.addEventListener('click', () => closeModal(sertifikasiModal));
        }
        if (sertifikasiModal) {
            sertifikasiModal.addEventListener('click', (e) => {
                if (e.target === sertifikasiModal) closeModal(sertifikasiModal);
            });
        }

        // Escape closes any open modal
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (pendidikanModal && !pendidikanModal.classList.contains('hidden'))  closeModal(pendidikanModal);
            if (sertifikasiModal && !sertifikasiModal.classList.contains('hidden')) closeModal(sertifikasiModal);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModals);
    } else {
        initModals();
    }
})();
</script>
@endpush
@endsection
