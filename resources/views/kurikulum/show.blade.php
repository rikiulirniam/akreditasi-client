@extends('layouts.app')

@section('title', 'Detail Kurikulum')

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Detail Kurikulum</h2>
        </div>
        <div>
            <a
                href="{{ route('kurikulum.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary hover:bg-additional text-white text-xs font-bold shadow-xs transition cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Kurikulum
            </a>
        </div>
    </div>

    <!-- Main Kurikulum Card (Sesuai Gambar 2) -->
    <div class="bg-white rounded-2xl border border-white-bone shadow-xs p-6 sm:p-8">
        <!-- Card Header: Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">
                {{ $kurikulum['nama_kurikulum'] ?? 'Kurikulum Merdeka 2024' }}
            </h3>
            
            <div class="flex items-center gap-2.5">
                @haspermission('kurikulum.delete')
                <!-- Hapus Data Button -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    data-confirm-delete
                    data-action="{{ route('kurikulum.destroy', $kurikulum['id']) }}"
                    data-title="Hapus Kurikulum?"
                    data-message="Apakah Anda yakin ingin menghapus kurikulum '{{ $kurikulum['nama_kurikulum'] }}'? Tindakan ini bersifat permanen dan data tidak dapat dikembalikan."
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Data
                </button>
                @endhaspermission

                @haspermission('kurikulum.update')
                <!-- Edit Data Button -->
                <a
                    href="{{ route('kurikulum.edit', $kurikulum['id']) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#EAB308] hover:bg-[#CA8A04] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Data
                </a>
                @endhaspermission
            </div>
        </div>

        <hr class="border-gray-200 my-5">

        <!-- Card Body: 2 Columns Info -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs">
            <!-- Kolom Kiri -->
            <div class="space-y-4">
                <!-- SK Kurikulum -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">SK Kurikulum:</span> {{ $kurikulum['sk_kurikulum'] ?? '-' }}
                    </p>
                </div>

                <!-- Program Studi -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Program Studi:</span> {{ $kurikulum['nama_prodi'] ?? ($prodiDetail['nama_prodi'] ?? '-') }}
                    </p>
                </div>

                <!-- Berlaku Hingga -->
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <p class="text-gray-700">
                        <span class="font-bold text-gray-900">Berlaku Hingga:</span> {{ $kurikulum['berlaku_sampai'] ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Kolom Kanan: CPL -->
            <div class="flex items-start gap-3">
                <span class="text-gray-500 shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <p class="text-gray-700 leading-relaxed">
                    <span class="font-bold text-gray-900">CPL:</span> {{ $kurikulum['cpl_deskripsi'] ?? 'Menjadi perguruan tinggi terapan berstandar nasional dan internasional yang mampu bersaing, akuntabel, berkarakter, dan beretika dalam bidang teknologi dan bisnis.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Section Daftar Mata Kuliah (Sesuai Gambar 2) -->
    <div class="space-y-3 pt-2">
        <h3 class="text-base font-bold text-gray-900">Daftar Mata Kuliah</h3>

        <!-- Search Bar Mata Kuliah -->
        <div class="relative max-w-xs">
            <input
                type="text"
                id="searchMataKuliahInput"
                placeholder="Cari Mata Kuliah"
                class="w-full px-4 py-2 pr-10 rounded-lg bg-[#E5E7EB] border-none text-xs font-medium text-gray-800 placeholder:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary/20"
            >
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </span>
        </div>

        <!-- Table Mata Kuliah -->
        <div class="overflow-x-auto bg-white rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 text-xs font-bold text-gray-900">
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Kode Mata Kuliah</th>
                        <th class="py-3 px-4">Nama Mata Kuliah</th>
                        <th class="py-3 px-4 w-28">SKS</th>
                        <th class="py-3 px-4 w-20 text-center">CPL</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-xs">
                    @forelse($mataKuliahList as $index => $mk)
                        <tr class="hover:bg-gray-50/60 transition mk-row">
                            <td class="py-3.5 px-4 text-gray-700 font-medium">
                                {{ $index + 1 }}.
                            </td>
                            <td class="py-3.5 px-4 text-gray-900 font-medium mk-kode">
                                {{ $mk['kode_mk'] ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-900 font-medium mk-nama">
                                {{ $mk['nama_mk'] ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-900 font-medium">
                                {{ $mk['sks'] ?? 3 }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button
                                    type="button"
                                    class="open-cpl-modal-btn text-primary hover:text-additional transition p-1 cursor-pointer"
                                    title="Pilih Kategori CPL Mata Kuliah"
                                    data-id="{{ $mk['id'] ?? $mk['kode_mk'] }}"
                                    data-kode="{{ $mk['kode_mk'] ?? '' }}"
                                    data-nama="{{ $mk['nama_mk'] ?? '' }}"
                                    data-kategori="{{ json_encode(array_values($mk['kategori_cpl'] ?? [])) }}"
                                >
                                    <x-icon.eye class="w-5 h-5 inline-block" />
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                                Tidak ada data mata kuliah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit CPL Mata Kuliah (Background gelap tanpa blur) -->
<div
    id="cplDetailModal"
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 transition-opacity duration-200 hidden"
>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 sm:p-8 space-y-5 transform transition-all">
        <!-- Modal Title -->
        <div class="text-center">
            <h3 class="text-base font-bold text-gray-900">
                CPL Mata Kuliah
            </h3>
            <p id="modalSubtitleMk" class="text-xs font-semibold text-primary mt-1 line-clamp-1">
                <!-- Diisi via JS: Kode & Nama Mata Kuliah -->
            </p>
        </div>

        <!-- Form Multiple Select Kategori CPL (Tanpa Deskripsi) -->
        <form id="cplEditForm" method="POST" action="{{ route('kurikulum.update-mk-cpl', $kurikulum['id']) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="mata_kuliah_id" id="modalInputMataKuliahId" value="">

            <div>
                <label class="block text-xs font-bold text-gray-900 mb-2.5">
                    Kategori CPL <span class="text-gray-400 font-normal">(bisa pilih lebih dari satu)</span>
                </label>

                <!-- Checkbox List Kategori CPL -->
                <div class="space-y-2 max-h-64 overflow-y-auto pr-1" id="cplCheckboxContainer">
                    @foreach($kategoriCplList ?? ['Sikap', 'Keterampilan Umum', 'Keterampilan Khusus', 'Pengetahuan'] as $kat)
                        <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-primary/50 hover:bg-primary/5 cursor-pointer transition select-none cpl-card-label">
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    name="kategori_cpl[]"
                                    value="{{ $kat }}"
                                    class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary/20 cursor-pointer cpl-checkbox"
                                >
                                <span class="text-xs font-bold text-gray-800">{{ $kat }}</span>
                            </div>
                            <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">CPL</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Tombol Batal & Simpan -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                <button
                    type="button"
                    id="closeCplModalBtn"
                    class="px-5 py-2.5 rounded-lg bg-[#E5E7EB] hover:bg-gray-300 text-gray-900 font-bold text-xs transition cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="submitCplBtn"
                    class="px-6 py-2.5 rounded-lg bg-primary hover:bg-additional text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center gap-2"
                >
                    <span id="submitCplText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script Modal Edit Multiple CPL & Search Filter -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Search Mata Kuliah
        const searchInput = document.getElementById('searchMataKuliahInput');
        const rows = document.querySelectorAll('.mk-row');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const keyword = this.value.toLowerCase().trim();
                rows.forEach(row => {
                    const kode = row.querySelector('.mk-kode')?.textContent.toLowerCase() || '';
                    const nama = row.querySelector('.mk-nama')?.textContent.toLowerCase() || '';
                    if (!keyword || kode.includes(keyword) || nama.includes(keyword)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Modal CPL Mata Kuliah
        const modal = document.getElementById('cplDetailModal');
        const form = document.getElementById('cplEditForm');
        const modalSubtitle = document.getElementById('modalSubtitleMk');
        const inputMkId = document.getElementById('modalInputMataKuliahId');
        const closeBtn = document.getElementById('closeCplModalBtn');
        const submitBtn = document.getElementById('submitCplBtn');
        const submitText = document.getElementById('submitCplText');
        const checkboxes = document.querySelectorAll('.cpl-checkbox');

        let activeRowBtn = null;

        function updateCardHighlight(checkbox) {
            const label = checkbox.closest('.cpl-card-label');
            if (!label) return;
            if (checkbox.checked) {
                label.classList.add('border-primary/60', 'bg-blue-50/50');
                label.classList.remove('border-gray-200');
            } else {
                label.classList.remove('border-primary/60', 'bg-blue-50/50');
                label.classList.add('border-gray-200');
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                updateCardHighlight(this);
            });
        });

        document.querySelectorAll('.open-cpl-modal-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                activeRowBtn = this;
                const id = this.getAttribute('data-id') || '';
                const kode = this.getAttribute('data-kode') || '';
                const nama = this.getAttribute('data-nama') || '';
                
                let kategori = [];
                try {
                    const rawKategori = this.getAttribute('data-kategori');
                    if (rawKategori) {
                        kategori = JSON.parse(rawKategori);
                        if (!Array.isArray(kategori)) {
                            kategori = [kategori];
                        }
                    }
                } catch (e) {
                    kategori = [];
                }

                if (modalSubtitle) {
                    modalSubtitle.textContent = (kode ? kode + ' - ' : '') + nama;
                }
                if (inputMkId) inputMkId.value = id;

                // Set status checked setiap checkbox
                checkboxes.forEach(cb => {
                    cb.checked = kategori.includes(cb.value);
                    updateCardHighlight(cb);
                });

                if (modal) modal.classList.remove('hidden');
            });
        });

        // Submit form via AJAX
        if (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                if (submitBtn) submitBtn.disabled = true;
                if (submitText) submitText.textContent = 'Menyimpan...';

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        const checkedValues = Array.from(document.querySelectorAll('.cpl-checkbox:checked')).map(cb => cb.value);

                        if (activeRowBtn) {
                            activeRowBtn.setAttribute('data-kategori', JSON.stringify(checkedValues));
                        }

                        if (modal) modal.classList.add('hidden');

                        if (typeof window.createToast === 'function') {
                            window.createToast({
                                type: 'success',
                                title: 'Berhasil Diperbarui',
                                message: result.message || 'Kategori CPL Mata Kuliah berhasil diperbarui.',
                            });
                        }
                    } else {
                        // Fallback submit standar
                        form.submit();
                    }
                } catch (error) {
                    // Fallback jika fetch error
                    form.submit();
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                    if (submitText) submitText.textContent = 'Simpan';
                }
            });
        }

        if (closeBtn && modal) {
            closeBtn.addEventListener('click', function () {
                modal.classList.add('hidden');
            });
        }

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    });
</script>
@endsection
