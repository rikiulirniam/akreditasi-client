{{--
    Partial: Form fields untuk Publikasi
    @param string $prefix  'create' | 'edit'
    @param array  $data    Data awal (untuk edit)
--}}
@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

{{-- Judul Publikasi --}}
<div>
    <label for="{{ $p }}_judul_publikasi" class="block text-xs font-bold text-gray-900 mb-1.5">
        Judul Publikasi <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        name="judul_publikasi"
        id="{{ $p }}_judul_publikasi"
        placeholder="Masukkan judul publikasi"
        value="{{ $d['judul_publikasi'] ?? ($d['judul'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400"
    >
</div>

{{-- Nama Penerbit / Jurnal --}}
<div>
    <label for="{{ $p }}_nama_penerbit_pub" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nama Penerbit / Jurnal <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        name="nama_penerbit"
        id="{{ $p }}_nama_penerbit_pub"
        placeholder="Misal: Jurnal Teknologi Informasi"
        value="{{ $d['nama_penerbit'] ?? ($d['penerbit'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400"
    >
</div>

{{-- Jenis Publikasi --}}
<div>
    <label for="{{ $p }}_jenis_publikasi" class="block text-xs font-bold text-gray-900 mb-1.5">
        Jenis Publikasi <span class="text-red-500">*</span>
    </label>
    <div class="relative">
        <select
            name="jenis_publikasi"
            id="{{ $p }}_jenis_publikasi"
            required
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['jenis_publikasi']) ? 'selected' : '' }}>Pilih jenis publikasi</option>
            @foreach(['Jurnal','Prosiding','Seminar','Produk','Laporan Pengabdian','Poster','Buletin','Newsletter'] as $opt)
                <option value="{{ $opt }}" {{ ($d['jenis_publikasi'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </span>
    </div>
</div>

{{-- Tanggal Terbit --}}
<div>
    <label for="{{ $p }}_tanggal_terbit_pub" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Terbit <span class="text-red-500">*</span>
    </label>
    <input
        type="date"
        name="tanggal_terbit"
        id="{{ $p }}_tanggal_terbit_pub"
        value="{{ $d['tanggal_terbit'] ?? '' }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none"
    >
</div>

{{-- URL Publikasi (opsional) --}}
<div>
    <label for="{{ $p }}_url_publikasi" class="block text-xs font-bold text-gray-900 mb-1.5">
        URL / Tautan Publikasi
    </label>
    <input
        type="url"
        name="url_publikasi"
        id="{{ $p }}_url_publikasi"
        placeholder="https://..."
        value="{{ $d['url_publikasi'] ?? ($d['url'] ?? '') }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-400"
    >
</div>
