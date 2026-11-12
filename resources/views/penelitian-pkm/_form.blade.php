@php
    $nilai = fn ($key) => old($key, $kegiatan[$key] ?? '');
    $input = 'h-[52px] w-full rounded-[8px] border border-transparent bg-[#D9D9D9] px-5 text-sm text-black placeholder-[#6B6B6B] focus:border-[#075B91] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30';
    $label = 'mb-2 block text-base font-bold text-black';
    $statusOptions = array_values(array_unique(array_filter(array_merge(['Pengajuan', 'Berjalan', 'Selesai', 'Ditolak'], [$nilai('status_kegiatan')]))));
@endphp

<div class="mx-auto max-w-6xl pb-8">
    <h2 class="mb-6 text-center text-4xl font-extrabold text-black">{{ $judul }}</h2>

    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700" role="alert">
            <ul class="list-inside list-disc">
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" class="space-y-5">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div>
            <label for="judul_kegiatan" class="{{ $label }}">Judul {{ $halaman }}</label>
            <input id="judul_kegiatan" type="text" name="judul_kegiatan" value="{{ $nilai('judul_kegiatan') }}" placeholder="Masukkan Judul kegiatan {{ $halaman }}" class="{{ $input }}" required>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            
            <div>
                <label for="status_kegiatan" class="{{ $label }}">Status Kegiatan</label>
                <select id="status_kegiatan" name="status_kegiatan" class="{{ $input }}" required>
                    <option value="">Pilih status kegiatan</option>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" @selected($nilai('status_kegiatan') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="lama_kegiatan" class="{{ $label }}">Lama Kegiatan</label>
                <input id="lama_kegiatan" type="text" name="lama_kegiatan" value="{{ $nilai('lama_kegiatan') }}" placeholder="Contoh: 12 Bulan" class="{{ $input }}">
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <div>
                <label for="tahun_usulan" class="{{ $label }}">Tahun Usulan</label>
                <input id="tahun_usulan" type="number" name="tahun_usulan" value="{{ $nilai('tahun_usulan') }}" placeholder="Isi tahun usulan" class="{{ $input }}" required>
            </div>
            <div>
                <label for="tahun_kegiatan" class="{{ $label }}">Tahun Kegiatan</label>
                <input id="tahun_kegiatan" type="number" name="tahun_kegiatan" value="{{ $nilai('tahun_kegiatan') }}" placeholder="Isi tahun kegiatan" class="{{ $input }}" required>
            </div>
            <div>
                <label for="tahun_pelaksanaan" class="{{ $label }}">Tahun Pelaksanaan</label>
                <input id="tahun_pelaksanaan" type="number" name="tahun_pelaksanaan" value="{{ $nilai('tahun_pelaksanaan') }}" placeholder="Isi tahun pelaksanaan" class="{{ $input }}" required>
            </div>
        </div>

        <div>
            <label for="lokasi_kegiatan" class="{{ $label }}">Lokasi Kegiatan</label>
            <input id="lokasi_kegiatan" type="text" name="lokasi_kegiatan" value="{{ $nilai('lokasi_kegiatan') }}" placeholder="Contoh: Semarang, Jawa Tengah" class="{{ $input }}">
        </div>

        <div class="flex justify-end gap-3 pt-8">
            <a href="{{ route($rute . '.index') }}" class="inline-flex h-11 w-[190px] items-center justify-center rounded-[6px] bg-[#D9D9D9] text-sm font-bold text-black transition hover:bg-gray-300">Kembali</a>
            <button type="submit" class="inline-flex h-11 w-[190px] items-center justify-center rounded-[6px] bg-additional text-sm font-bold text-white transition hover:bg-primary">Simpan</button>
        </div>
    </form>
</div>