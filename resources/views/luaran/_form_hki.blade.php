@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

<div>
    <label for="{{ $p }}_id_dosen" class="block text-xs font-bold text-gray-900 mb-1.5">
        Dosen Penanggung Jawab <span class="text-red-500">*</span>
    </label>
    <select
        name="id_dosen"
        id="{{ $p }}_id_dosen"
        required
        {{ empty($dosenOptions) ? 'disabled' : '' }}
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none"
    >
        <option value="">Pilih Dosen</option>
        @foreach($dosenOptions as $dosen)
            <option value="{{ $dosen['id'] }}" {{ (string) ($d['id_dosen'] ?? $selectedDosenId ?? '') === (string) $dosen['id'] ? 'selected' : '' }}>{{ $dosen['name'] }}</option>
        @endforeach
    </select>
    @if($dosenLoadError)
        <p class="mt-1 text-xs text-red-600">Daftar dosen gagal dimuat atau belum tersedia. Muat ulang halaman sebelum mengirim.</p>
    @endif
</div>

{{-- 1. Judul HKI / Ciptaan --}}
<div>
    <label for="{{ $p }}_judul_hak_cipta" class="block text-xs font-bold text-gray-900 mb-1.5">
        Judul HKI / Ciptaan
    </label>
    <input
        type="text"
        name="judul_hak_cipta"
        id="{{ $p }}_judul_hak_cipta"
        placeholder="Isi Judul HKI / Hak Cipta"
        value="{{ $d['judul_hak_cipta'] ?? ($d['judul_luaran'] ?? ($d['judul'] ?? '')) }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 2. Jenis HKI --}}
<div>
    <label for="{{ $p }}_jenis_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Jenis HKI
    </label>
    <div class="relative">
        <select
            name="jenis_hki"
            id="{{ $p }}_jenis_hki"
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['jenis_hki']) ? 'selected' : '' }}>Pilih Jenis HKI</option>
            @foreach(['Hak Cipta', 'Paten', 'Paten Sederhana', 'Desain Industri', 'Rahasia Dagang', 'Merek/DTLST'] as $opt)
                <option value="{{ $opt }}" {{ ($d['jenis_hki'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 3. Nomor Pendaftaran --}}
<div>
    <label for="{{ $p }}_nomor_pendaftaran_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nomor Pendaftaran
    </label>
    <input
        type="text"
        name="nomor_pendaftaran_hki"
        id="{{ $p }}_nomor_pendaftaran_hki"
        placeholder="Isi nomor pendaftaran HKI"
        value="{{ $d['nomor_pendaftaran_hki'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 4. Nomor Sertifikat --}}
<div>
    <label for="{{ $p }}_nomor_sertifikat_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nomor Sertifikat
    </label>
    <input
        type="text"
        name="nomor_sertifikat_hki"
        id="{{ $p }}_nomor_sertifikat_hki"
        placeholder="Isi nomor sertifikat HKI"
        value="{{ $d['nomor_sertifikat_hki'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 5. Tanggal Pendaftaran --}}
<div>
    <label for="{{ $p }}_tanggal_pendaftaran_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Pendaftaran
    </label>
    <input
        type="date"
        name="tanggal_pendaftaran_hki"
        id="{{ $p }}_tanggal_pendaftaran_hki"
        value="{{ $d['tanggal_pendaftaran_hki'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none"
    >
</div>

{{-- 6. Tanggal Terbit Sertifikat --}}
<div>
    <label for="{{ $p }}_tanggal_terbit_sertifikat_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Terbit
    </label>
    <input
        type="date"
        name="tanggal_terbit_sertifikat_hki"
        id="{{ $p }}_tanggal_terbit_sertifikat_hki"
        value="{{ $d['tanggal_terbit_sertifikat_hki'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none"
    >
</div>

{{-- 7. Pemegang Hak --}}
<div>
    <label for="{{ $p }}_pemegang_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Pemegang Hak
    </label>
    <input
        type="text"
        name="pemegang_hki"
        id="{{ $p }}_pemegang_hki"
        placeholder="Nama pemegang hak"
        value="{{ $d['pemegang_hki'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 8. Status HKI --}}
<div>
    <label for="{{ $p }}_status_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Status HKI
    </label>
    <div class="relative">
        <select
            name="status_hki"
            id="{{ $p }}_status_hki"
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['status_hki']) ? 'selected' : '' }}>Pilih Status HKI</option>
            @foreach(['Draft', 'Terdaftar/Dalam Proses', 'Sertifikat Terbit/Granted', 'Ditolak'] as $opt)
                <option value="{{ $opt }}" {{ ($d['status_hki'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 9. Tautan HKI --}}
<div>
    <label for="{{ $p }}_url_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan HKI G.Drive
    </label>
    <input
        type="url"
        name="url_luaran"
        id="{{ $p }}_url_hki"
        placeholder="Unggah Dokumen HKI dalam bentuk tautan G.Drive"
        value="{{ $d['tautan_hki'] ?? ($d['url_hki'] ?? ($d['url_luaran'] ?? ($d['url'] ?? ''))) }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
