@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

{{-- 1. Nama Jurnal --}}
<div>
    <label for="{{ $p }}_nama_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nama Jurnal
    </label>
    <input
        type="text"
        name="nama_jurnal"
        id="{{ $p }}_nama_jurnal"
        placeholder="Isi Nama Jurnal"
        value="{{ $d['nama_jurnal'] ?? ($d['judul_publikasi'] ?? ($d['judul'] ?? '')) }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 2. ISSN --}}
<div>
    <label for="{{ $p }}_issn_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        ISSN
    </label>
    <input
        type="text"
        name="issn"
        id="{{ $p }}_issn_jurnal"
        placeholder="Isi ISSN Jurnal"
        value="{{ $d['issn'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 3. Volume --}}
<div>
    <label for="{{ $p }}_volume_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Volume
    </label>
    <input
        type="text"
        name="volume"
        id="{{ $p }}_volume_jurnal"
        placeholder="Isi Volume Jurnal"
        value="{{ $d['volume'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 4. Tanggal Terbit --}}
<div>
    <label for="{{ $p }}_tanggal_terbit_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Terbit
    </label>
    <input
        type="text"
        name="tanggal_terbit"
        id="{{ $p }}_tanggal_terbit_jurnal"
        placeholder="Isi tanggal terbit jurnal"
        value="{{ $d['tanggal_terbit'] ?? '' }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 5. DOI --}}
<div>
    <label for="{{ $p }}_doi_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        DOI
    </label>
    <input
        type="text"
        name="doi"
        id="{{ $p }}_doi_jurnal"
        placeholder="Isi DOI Jurnal"
        value="{{ $d['doi'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 6. Nomor Edisi Jurnal --}}
<div>
    <label for="{{ $p }}_nomor_edisi_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nomor Edisi Jurnal
    </label>
    <input
        type="text"
        name="nomor_edisi"
        id="{{ $p }}_nomor_edisi_jurnal"
        placeholder="Isi nomor edisi jurnal"
        value="{{ $d['nomor_edisi'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 7. Total Halaman Jurnal --}}
<div>
    <label for="{{ $p }}_total_halaman_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Total Halaman Jurnal
    </label>
    <input
        type="number"
        min="1"
        name="total_halaman"
        id="{{ $p }}_total_halaman_jurnal"
        placeholder="Isi total halaman jurnal"
        value="{{ $d['total_halaman'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 8. Indeksasi --}}
<div>
    <label for="{{ $p }}_indeksasi_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Indeksasi
    </label>
    <div class="relative">
        <select
            name="indeksasi"
            id="{{ $p }}_indeksasi_jurnal"
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['indeksasi']) ? 'selected' : '' }}>Pilih Indeksasi Jurnal</option>
            @foreach(['SCOPUS', 'Web of Science', 'IEEE Xplore', 'ERIC', 'SINTA', 'Google Scholar'] as $opt)
                <option value="{{ $opt }}" {{ ($d['indeksasi'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 9. Tautan Jurnal (G. Drive) --}}
<div>
    <label for="{{ $p }}_url_jurnal" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan Jurnal (G. Drive)
    </label>
    <input
        type="text"
        name="url_publikasi"
        id="{{ $p }}_url_jurnal"
        placeholder="Unggah Jurnal dalam bentuk tautan G.Drive"
        value="{{ $d['url_publikasi'] ?? ($d['url'] ?? '') }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
