@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

{{-- 1. Judul Buku --}}
<div>
    <label for="{{ $p }}_judul_buku" class="block text-xs font-bold text-gray-900 mb-1.5">
        Judul Buku
    </label>
    <input
        type="text"
        name="judul_buku"
        id="{{ $p }}_judul_buku"
        placeholder="Isi Judul Buku"
        value="{{ $d['judul_buku'] ?? ($d['judul'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 2. ISBN --}}
<div>
    <label for="{{ $p }}_isbn" class="block text-xs font-bold text-gray-900 mb-1.5">
        ISBN
    </label>
    <input
        type="text"
        name="isbn"
        id="{{ $p }}_isbn"
        placeholder="Isi ISBN Buku"
        value="{{ $d['isbn'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 3. Penerbit Buku --}}
<div>
    <label for="{{ $p }}_nama_penerbit_buku" class="block text-xs font-bold text-gray-900 mb-1.5">
        Penerbit Buku
    </label>
    <input
        type="text"
        name="nama_penerbit"
        id="{{ $p }}_nama_penerbit_buku"
        placeholder="Isi Penerbit Buku"
        value="{{ $d['nama_penerbit'] ?? ($d['penerbit'] ?? ($d['penerbit_buku'] ?? '')) }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 4. Tahun Terbit --}}
<div>
    <label for="{{ $p }}_tahun_terbit_buku" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tahun Terbit
    </label>
    <input
        type="text"
        name="tahun_terbit"
        id="{{ $p }}_tahun_terbit_buku"
        placeholder="Masukkan tahun terbit buku"
        value="{{ $d['tahun_terbit'] ?? ($d['tahun'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 5. Jenis Buku --}}
<div>
    <label for="{{ $p }}_jenis_buku" class="block text-xs font-bold text-gray-900 mb-1.5">
        Jenis Buku
    </label>
    <div class="relative">
        <select
            name="jenis_buku"
            id="{{ $p }}_jenis_buku"
            required
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['jenis_buku']) ? 'selected' : '' }}>Pilih Jenis Buku</option>
            @foreach(['Buku Ajar', 'Buku Referensi', 'Buku Monograf', 'Buku Jurnal'] as $opt)
                <option value="{{ $opt }}" {{ ($d['jenis_buku'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 6. Bidang Ilmu --}}
<div>
    <label for="{{ $p }}_bidang_ilmu" class="block text-xs font-bold text-gray-900 mb-1.5">
        Bidang Ilmu
    </label>
    <input
        type="text"
        name="bidang_ilmu"
        id="{{ $p }}_bidang_ilmu"
        placeholder="Isi Bidang Ilmu"
        value="{{ $d['bidang_ilmu'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 7. Total Halaman Buku --}}
<div>
    <label for="{{ $p }}_total_halaman_buku" class="block text-xs font-bold text-gray-900 mb-1.5">
        Total Halaman Buku
    </label>
    <input
        type="number"
        min="1"
        name="total_halaman"
        id="{{ $p }}_total_halaman_buku"
        placeholder="Isi total halaman buku"
        value="{{ $d['total_halaman'] ?? '' }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 8. Tautan Buku --}}
<div>
    <label for="{{ $p }}_url_buku" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan Buku
    </label>
    <input
        type="text"
        name="url_buku"
        id="{{ $p }}_url_buku"
        placeholder="Unggah Buku dalam bentuk tautan G.Drive"
        value="{{ $d['url_buku'] ?? ($d['url'] ?? '') }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
