@php
    $d = $data ?? [];
    $p = $prefix ?? 'create';
@endphp

{{-- 1. Nama Produk --}}
<div>
    <label for="{{ $p }}_nama_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Nama Produk
    </label>
    <input
        type="text"
        name="nama_produk"
        id="{{ $p }}_nama_produk"
        placeholder="Isi Nama Produk"
        value="{{ $d['nama_produk'] ?? ($d['judul_luaran'] ?? ($d['judul'] ?? '')) }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 2. Instansi / Penyelenggara --}}
<div>
    <label for="{{ $p }}_penerbit_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Instansi / Penyelenggara
    </label>
    <input
        type="text"
        name="penerbit_produk"
        id="{{ $p }}_penerbit_produk"
        placeholder="Isi Instansi atau Penyelenggara Produk"
        value="{{ $d['penerbit_produk'] ?? ($d['nama_penerbit'] ?? '') }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 3. Deskripsi Singkat --}}
<div>
    <label for="{{ $p }}_deskripsi_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Deskripsi Produk
    </label>
    <textarea
        name="deskripsi"
        id="{{ $p }}_deskripsi_produk"
        rows="3"
        placeholder="Deskripsi singkat mengenai produk, prototipe, atau perangkat lunak"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500 resize-none"
    >{{ $d['deskripsi'] ?? '' }}</textarea>
</div>

{{-- 4. Tanggal Terbit / Rilis --}}
<div>
    <label for="{{ $p }}_tanggal_terbit_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Rilis / Peluncuran
    </label>
    <input
        type="text"
        name="tanggal_terbit"
        id="{{ $p }}_tanggal_terbit_produk"
        placeholder="Isi tanggal rilis produk"
        value="{{ $d['tanggal_terbit'] ?? '' }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>

{{-- 5. Tautan Produk (G. Drive / Repository) --}}
<div>
    <label for="{{ $p }}_url_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan Produk (G. Drive / Repository)
    </label>
    <input
        type="text"
        name="url_produk"
        id="{{ $p }}_url_produk"
        placeholder="Unggah Bukti Produk dalam bentuk tautan G.Drive"
        value="{{ $d['url_produk'] ?? ($d['url'] ?? '') }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
