@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

{{-- 1. Nama Seminar --}}
<div>
    <label for="{{ $p }}_nama_seminar" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nama Seminar
    </label>
    <input
        type="text"
        name="nama_seminar"
        id="{{ $p }}_nama_seminar"
        placeholder="Isi Nama Seminar"
        value="{{ $d['nama_seminar'] ?? ($d['judul_publikasi'] ?? ($d['judul'] ?? '')) }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 2. Nama Prosiding --}}
<div>
    <label for="{{ $p }}_nama_prosiding" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nama Prosiding
    </label>
    <input
        type="text"
        name="nama_prosiding"
        id="{{ $p }}_nama_prosiding"
        placeholder="Isi Nama Prosiding"
        value="{{ $d['nama_prosiding'] ?? ($d['nama_penerbit'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 3. ISBN --}}
<div>
    <label for="{{ $p }}_isbn_prosiding" class="block text-xs font-bold text-gray-900 mb-1.5">
        ISBN
    </label>
    <input
        type="text"
        name="isbn"
        id="{{ $p }}_isbn_prosiding"
        placeholder="Isi ISBN Prosiding"
        value="{{ $d['isbn'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 4. Penyelenggara --}}
<div>
    <label for="{{ $p }}_penyelenggara_seminar" class="block text-xs font-bold text-gray-900 mb-1.5">
        Penyelenggara
    </label>
    <input
        type="text"
        name="penyelenggara"
        id="{{ $p }}_penyelenggara_seminar"
        placeholder="Isi Nama Penyelenggara"
        value="{{ $d['penyelenggara'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 5. Tanggal Seminar --}}
<div>
    <label for="{{ $p }}_tanggal_seminar" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Seminar
    </label>
    <input
        type="text"
        name="tanggal_seminar"
        id="{{ $p }}_tanggal_seminar"
        placeholder="Isi Tanggal Seminar"
        value="{{ $d['tanggal_seminar'] ?? ($d['tanggal_terbit'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 6. Lokasi Seminar --}}
<div>
    <label for="{{ $p }}_lokasi_seminar" class="block text-xs font-bold text-gray-900 mb-1.5">
        Lokasi Seminar
    </label>
    <input
        type="text"
        name="lokasi_seminar"
        id="{{ $p }}_lokasi_seminar"
        placeholder="Isi Lokasi Seminar"
        value="{{ $d['lokasi_seminar'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 7. Total Halaman Prosiding --}}
<div>
    <label for="{{ $p }}_total_halaman_prosiding" class="block text-xs font-bold text-gray-900 mb-1.5">
        Total Halaman Prosiding
    </label>
    <input
        type="number"
        min="1"
        name="total_halaman"
        id="{{ $p }}_total_halaman_prosiding"
        placeholder="Isi total halaman prosiding"
        value="{{ $d['total_halaman'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 8. DOI Prosiding --}}
<div>
    <label for="{{ $p }}_doi_prosiding" class="block text-xs font-bold text-gray-900 mb-1.5">
        DOI Prosiding
    </label>
    <input
        type="text"
        name="doi"
        id="{{ $p }}_doi_prosiding"
        placeholder="Isi No DOI Prosiding"
        value="{{ $d['doi'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 9. Tautan Seminar (G. Drive) --}}
<div>
    <label for="{{ $p }}_url_seminar" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan Seminar (G. Drive)
    </label>
    <input
        type="text"
        name="url_publikasi"
        id="{{ $p }}_url_seminar"
        placeholder="Unggah File Seminar dalam bentuk tautan G.Drive"
        value="{{ $d['url_publikasi'] ?? ($d['url'] ?? '') }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
