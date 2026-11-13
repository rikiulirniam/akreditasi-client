@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

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

{{-- 2. Lembaga Penerbit --}}
<div>
    <label for="{{ $p }}_nama_penerbit_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Lembaga Penerbit
    </label>
    <input
        type="text"
        name="nama_penerbit"
        id="{{ $p }}_nama_penerbit_hki"
        placeholder="Misal: Direktorat Jenderal Kekayaan Intelektual"
        value="{{ $d['nama_penerbit'] ?? 'Direktorat Jenderal Kekayaan Intelektual' }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 3. Jenis HKI --}}
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
            @foreach(['Hak Cipta', 'Paten', 'Paten Sederhana', 'Merek', 'Desain Industri', 'DTLST', 'Rahasia Dagang'] as $opt)
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

{{-- 4. Nomor Pendaftaran --}}
<div>
    <label for="{{ $p }}_nomor_pendaftaran_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nomor Pendaftaran / Sertifikat
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

{{-- 5. Status HKI --}}
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
            <option value="Terdaftar" {{ ($d['status_hki'] ?? '') === 'Terdaftar' ? 'selected' : '' }}>Terdaftar</option>
            <option value="Granted / Bersertifikat" {{ ($d['status_hki'] ?? '') === 'Granted / Bersertifikat' ? 'selected' : '' }}>Granted / Bersertifikat</option>
            <option value="Dalam Proses" {{ ($d['status_hki'] ?? '') === 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 6. Tanggal Terbit / Permohonan --}}
<div>
    <label for="{{ $p }}_tanggal_terbit_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Terbit / Permohonan
    </label>
    <input
        type="text"
        name="tanggal_terbit"
        id="{{ $p }}_tanggal_terbit_hki"
        placeholder="Isi tanggal terbit HKI"
        value="{{ $d['tanggal_terbit'] ?? '' }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 7. Tautan Dokumen HKI --}}
<div>
    <label for="{{ $p }}_url_hki" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan Dokumen HKI
    </label>
    <input
        type="text"
        name="url_hki"
        id="{{ $p }}_url_hki"
        placeholder="Unggah Dokumen HKI dalam bentuk tautan G.Drive"
        value="{{ $d['url_hki'] ?? ($d['url'] ?? '') }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
