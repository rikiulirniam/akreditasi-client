@php
    $nilai = fn ($key) => old($key, $kegiatan[$key] ?? '');
    $input = 'h-[52px] w-full rounded-[8px] border border-transparent bg-[#D9D9D9] px-5 text-sm text-black placeholder-[#6B6B6B] focus:border-[#075B91] focus:outline-none focus:ring-2 focus:ring-[#075B91]/30';
    $label = 'mb-2 block text-base font-bold text-black';
    $statusOptions = array_values(array_unique(array_filter(array_merge(['Pengajuan', 'Berjalan', 'Selesai', 'Ditolak'], [$nilai('status_kegiatan')]))));
    $opsiDosen = fn ($dipilih) => collect($dosenList)->map(fn ($nama, $id) => '<option value="' . e($id) . '"' . ((string) $dipilih === (string) $id ? ' selected' : '') . '>' . e($nama) . '</option>')->implode('');
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
                <input id="tahun_usulan" type="number" min="1900" max="2099" step="1" name="tahun_usulan" value="{{ $nilai('tahun_usulan') }}" placeholder="Isi tahun usulan" class="{{ $input }}" required>
            </div>
            <div>
                <label for="tahun_kegiatan" class="{{ $label }}">Tahun Kegiatan</label>
                <input id="tahun_kegiatan" type="number" min="1900" max="2099" step="1" name="tahun_kegiatan" value="{{ $nilai('tahun_kegiatan') }}" placeholder="Isi tahun kegiatan" class="{{ $input }}" required>
            </div>
            <div>
                <label for="tahun_pelaksanaan" class="{{ $label }}">Tahun Pelaksanaan</label>
                <input id="tahun_pelaksanaan" type="number" min="1900" max="2099" step="1" name="tahun_pelaksanaan" value="{{ $nilai('tahun_pelaksanaan') }}" placeholder="Isi tahun pelaksanaan" class="{{ $input }}" required>
            </div>
        </div>

        <div>
            <label for="lokasi_kegiatan" class="{{ $label }}">Lokasi Kegiatan</label>
            <input id="lokasi_kegiatan" type="text" name="lokasi_kegiatan" value="{{ $nilai('lokasi_kegiatan') }}" placeholder="Contoh: Semarang, Jawa Tengah" class="{{ $input }}">
        </div>
        <div class="space-y-4 rounded-[8px] bg-[#EEF3F7] p-5">
            <h3 class="text-base font-bold text-black">Anggota Kegiatan</h3>

            @if(empty($dosenList))
                <p class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-700">Daftar dosen belum dapat dimuat. Muat ulang halaman ini.</p>
            @endif

            <div>
                <label for="ketua" class="{{ $label }}">Ketua</label>
                <select id="ketua" name="ketua" class="{{ $input }}" required>
                    <option value="">Pilih ketua</option>
                    {!! $opsiDosen(old('ketua', $anggota['ketua'] ?? '')) !!}
                </select>
            </div>

            <div>
                <span class="{{ $label }}">Anggota Dosen</span>
                <div id="anggota-dosen-list" class="space-y-3">
                    @foreach(old('anggota_dosen', $anggota['dosen'] ?? []) as $terpilih)
                        <div class="flex items-center gap-3" data-anggota-baris>
                            <select name="anggota_dosen[]" class="{{ $input }}">
                                <option value="">Pilih dosen</option>
                                {!! $opsiDosen($terpilih) !!}
                            </select>
                            <button type="button" data-anggota-hapus class="inline-flex h-[52px] shrink-0 items-center rounded-[8px] border border-red-300 px-4 text-sm font-bold text-red-600 transition hover:bg-red-50">Hapus</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="anggota-dosen-tambah" class="mt-3 inline-flex h-10 items-center rounded-[8px] bg-[#01568D] px-4 text-sm font-bold text-white transition hover:bg-[#012650]">+ Tambah Anggota</button>
            </div>

            <input type="hidden" name="anggota_lain" value="{{ old('anggota_lain', json_encode($anggota['lain'] ?? [])) }}">
        </div>

        <template id="anggota-dosen-template">
            <div class="flex items-center gap-3" data-anggota-baris>
                <select name="anggota_dosen[]" class="{{ $input }}">
                    <option value="">Pilih dosen</option>
                    {!! $opsiDosen('') !!}
                </select>
                <button type="button" data-anggota-hapus class="inline-flex h-[52px] shrink-0 items-center rounded-[8px] border border-red-300 px-4 text-sm font-bold text-red-600 transition hover:bg-red-50">Hapus</button>
            </div>
        </template>
        <div class="flex justify-end gap-3 pt-8">
            <a href="{{ route($rute . '.index') }}" class="inline-flex h-11 w-[190px] items-center justify-center rounded-[6px] bg-[#D9D9D9] text-sm font-bold text-black transition hover:bg-gray-300">Kembali</a>
            <button type="submit" class="inline-flex h-11 w-[190px] items-center justify-center rounded-[6px] bg-additional text-sm font-bold text-white transition hover:bg-primary">Simpan</button>
        </div>
    </form>
</div>
<script>
    (function () {
        const daftar = document.getElementById('anggota-dosen-list');
        const templat = document.getElementById('anggota-dosen-template');

        document.getElementById('anggota-dosen-tambah').addEventListener('click', () => {
            daftar.appendChild(templat.content.cloneNode(true));
        });

        daftar.addEventListener('click', (event) => {
            const tombol = event.target.closest('[data-anggota-hapus]');
            if (tombol) tombol.closest('[data-anggota-baris]').remove();
        });
    })();
</script>