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

{{-- 2. Jenis Produk --}}
<div>
    <label for="{{ $p }}_jenis_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Jenis Produk
    </label>
    <div class="relative">
        <select
            name="jenis_produk"
            id="{{ $p }}_jenis_produk"
            required
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['jenis_produk']) ? 'selected' : '' }}>Pilih Jenis Produk</option>
            @foreach(['Software/Web', 'Mobile', 'Hardware', 'Embedded system'] as $opt)
                <option value="{{ $opt }}" {{ ($d['jenis_produk'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 3. Status Pengembangan --}}
<div>
    <label for="{{ $p }}_status_pengembangan" class="block text-xs font-bold text-gray-900 mb-1.5">
        Status Pengembangan saat ini
    </label>
    <div class="relative">
        <select
            name="status_pengembangan"
            id="{{ $p }}_status_pengembangan"
            required
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['status_pengembangan']) ? 'selected' : '' }}>Pilih Status Pengembangan</option>
            @foreach(['Desain', 'Prototipe', 'Uji coba', 'Produksi', 'Komersialisasi'] as $opt)
                <option value="{{ $opt }}" {{ ($d['status_pengembangan'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 4. Tingkat Kesiapterapan --}}
<div>
    <label for="{{ $p }}_tingkat_kesiapanterapan" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tingkat Kesiapterapan
    </label>
    <div class="relative">
        <select
            name="tingkat_kesiapanterapan"
            id="{{ $p }}_tingkat_kesiapanterapan"
            required
            class="w-full appearance-none px-4 py-2.5 pr-10 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-700 focus:ring-2 focus:ring-primary/20 focus:outline-none cursor-pointer"
        >
            <option value="" disabled {{ empty($d['tingkat_kesiapanterapan']) ? 'selected' : '' }}>Pilih TKT</option>
            @foreach(['TKT 1', 'TKT 2', 'TKT 3', 'TKT 4', 'TKT 5', 'TKT 6', 'TKT 7', 'TKT 8', 'TKT 9'] as $opt)
                <option value="{{ $opt }}" {{ ($d['tingkat_kesiapanterapan'] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
            @endforeach
        </select>
        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-primary">
            <svg class="w-5 h-5 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
            </svg>
        </span>
    </div>
</div>

{{-- 5. Deskripsi Singkat --}}
<div>
    <label for="{{ $p }}_deskripsi_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Deskripsi Produk
    </label>
    <textarea
        name="deskripsi_produk"
        id="{{ $p }}_deskripsi_produk"
        rows="3"
        placeholder="Deskripsi singkat mengenai produk, prototipe, atau perangkat lunak"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500 resize-none"
    >{{ $d['deskripsi_produk'] ?? ($d['deskripsi'] ?? '') }}</textarea>
</div>

{{-- 6. Tanggal Pembuatan --}}
<div>
    <label for="{{ $p }}_tanggal_pembuatan" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tanggal Pembuatan Produk
    </label>
    <input
        type="date"
        name="tanggal_pembuatan"
        id="{{ $p }}_tanggal_pembuatan"
        value="{{ $d['tanggal_pembuatan'] ?? '' }}"
        required
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none"
    >
</div>

{{-- 7. Tautan Produk --}}
<div>
    <label for="{{ $p }}_url_produk" class="block text-xs font-bold text-gray-900 mb-1.5">
        Tautan Produk
    </label>
    <input
        type="url"
        name="url_luaran"
        id="{{ $p }}_url_produk"
        placeholder="Tempel tautan produk atau repository"
        value="{{ $d['tautan_produk'] ?? ($d['url_produk'] ?? ($d['url_luaran'] ?? ($d['url'] ?? ''))) }}"
        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-primary/20 focus:outline-none placeholder:text-gray-500"
    >
</div>
