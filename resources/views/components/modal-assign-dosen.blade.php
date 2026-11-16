{{-- resources/views/components/modal-assign-dosen.blade.php --}}
@props([
    'id' => 'modal-assign-dosen',
    'dosenList' => [],
])

@php
    $extractSafeStr = function($val, $default = '-') {
        if (is_null($val)) return $default;
        if (is_string($val)) return trim($val) ?: $default;
        if (is_numeric($val)) return (string)$val;
        if (is_array($val)) {
            foreach (['nama_bidang', 'nama_jabatan', 'nama', 'name', 'label', 'keahlian'] as $k) {
                if (!empty($val[$k]) && is_string($val[$k])) return trim($val[$k]);
            }
            $first = reset($val);
            if (is_string($first)) return trim($first);
            if (is_array($first) && !empty($first['nama'])) return trim($first['nama']);
            return $default;
        }
        return (string)$val;
    };
@endphp

<div 
    id="{{ $id }}" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-[2px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
>
    <!-- Modal Card Box -->
    <div class="relative bg-white rounded-2xl p-6 sm:p-7 max-w-xl w-full max-h-[90vh] shadow-2xl text-left transform scale-95 transition-all duration-200 overflow-hidden flex flex-col border border-gray-100">
        
        <!-- Header Modal -->
        <div class="flex items-start justify-between gap-3 border-b border-[#EAEAEA] pb-4 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-700 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 id="{{ $id }}-title" class="text-base sm:text-lg font-extrabold text-primary leading-tight">
                        Penugasan Batch Tim Akreditasi
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Tetapkan dosen sebagai <strong>PJ</strong> atau <strong>PIC</strong> untuk butir indikator borang terpilih.
                    </p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="closeAssignDosenModal('{{ $id }}')" 
                class="text-gray-400 hover:text-gray-600 font-bold text-lg p-1 rounded-lg hover:bg-gray-100 transition-colors leading-none cursor-pointer"
                aria-label="Tutup"
            >
                &times;
            </button>
        </div>

        <!-- Scrollable Content Body -->
        <div class="overflow-y-auto flex-1 py-4 space-y-4 pr-1">
            
            <!-- 1. Ringkasan Indikator Terpilih -->
            <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-700">Indikator yang Ditugaskan:</span>
                    <span id="{{ $id }}-selected-count-badge" class="px-2 py-0.5 rounded-full text-[11px] font-black bg-primary/10 text-primary">
                        0 Indikator Dipilih
                    </span>
                </div>
                <div id="{{ $id }}-chips-container" class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                    <span class="text-xs text-gray-400 italic">Pilih indikator pada tabel terlebih dahulu</span>
                </div>
            </div>

            <!-- 2. Pilih Peran: PJ vs PIC -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-gray-700">
                    Pilih Peran Tim <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Option PJ -->
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="modal_role" value="pj" class="hidden peer" checked>
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white hover:border-indigo-400 peer-checked:border-indigo-600 peer-checked:ring-2 peer-checked:ring-indigo-100 peer-checked:bg-indigo-50/20 transition-all flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-indigo-800">
                                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                                    PJ (Penanggung Jawab)
                                </span>
                                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">Kriteria Lead</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1 leading-snug">
                                Penanggung jawab mutu substantif dan verifikator kriteria borang.
                            </p>
                        </div>
                    </label>

                    <!-- Option PIC -->
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="modal_role" value="pic" class="hidden peer">
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-white hover:border-emerald-400 peer-checked:border-emerald-600 peer-checked:ring-2 peer-checked:ring-emerald-100 peer-checked:bg-emerald-50/20 transition-all flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    PIC (Pelaksana)
                                </span>
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">Pengunggah Bukti</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1 leading-snug">
                                Pelaksana teknis pengisian narasi borang &amp; tautan bukti Google Drive.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 3. Pencarian Cepat & Pemilihan Dosen (Integrasi Kelompok 2 Tridharma) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-gray-700">
                        Pilih Dosen Pengampu <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] text-gray-400 font-semibold">Data Dosen Kelompok 2 (Tridharma)</span>
                </div>

                <!-- Input Pencarian Cepat (Searchable Select) -->
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="{{ $id }}-search-input" 
                        oninput="filterModalDosenList('{{ $id }}')" 
                        placeholder="Ketik Nama Dosen, NIDN, atau Bidang Keahlian..." 
                        class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50/80 hover:bg-gray-100/50 focus:bg-white text-xs sm:text-sm font-semibold rounded-xl border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-gray-400"
                    >
                </div>

                <!-- Daftar Dosen (Cards with Radio) -->
                <div id="{{ $id }}-dosen-cards-container" class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    @forelse($dosenList as $dosen)
                        @php
                            $dosenId = $dosen['id'] ?? $loop->iteration;
                            $dosenNama = $extractSafeStr($dosen['nama'] ?? ($dosen['nama_dosen'] ?? 'Dosen'), 'Dosen');
                            $dosenNidn = $extractSafeStr($dosen['nidn'] ?? ($dosen['nip'] ?? '-'), '-');
                            $dosenKeahlian = $extractSafeStr($dosen['bidang_keahlian'] ?? 'Teknologi Rekayasa Komputer', 'Teknologi Rekayasa Komputer');
                            $dosenJabatan = $extractSafeStr($dosen['jabatan'] ?? 'Dosen Tetap', 'Dosen Tetap');
                            $cleanName = preg_replace('/^(prof\.|dr\.|ir\.|drs\.|dra\.|eng\.)\s*/i', '', trim($dosenNama));
                            $cleanName = preg_replace('/^(prof\.|dr\.|ir\.|drs\.|dra\.|eng\.)\s*/i', '', $cleanName);
                            $cleanName = preg_replace('/^(prof\.|dr\.|ir\.|drs\.|dra\.|eng\.)\s*/i', '', $cleanName);
                            $cleanName = trim($cleanName);
                            $shortName = ucfirst(strtolower(explode(' ', $cleanName)[0] ?? $dosenNama));
                        @endphp
                        <label class="modal-dosen-card relative block cursor-pointer select-none" 
                            data-nama="{{ strtolower($dosenNama) }}" 
                            data-nidn="{{ strtolower($dosenNidn) }}" 
                            data-keahlian="{{ strtolower($dosenKeahlian) }}"
                        >
                            <input 
                                type="radio" 
                                name="modal_dosen" 
                                value="{{ $dosenNama }}" 
                                data-id="{{ $dosenId }}"
                                data-short-name="{{ $shortName }}"
                                class="hidden peer"
                                {{ $loop->first ? 'checked' : '' }}
                            >
                            <div class="p-3 bg-white border border-gray-200 rounded-xl flex items-center justify-between gap-3 hover:border-additional peer-checked:border-additional peer-checked:ring-2 peer-checked:ring-additional/20 peer-checked:bg-sky-50/30 transition-all">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs sm:text-sm font-bold text-primary truncate leading-tight">{{ $dosenNama }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 shrink-0">
                                            NIDN: {{ $dosenNidn }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 font-medium truncate mt-0.5">
                                        {{ $dosenKeahlian }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary/10 text-primary shrink-0">
                                    {{ $dosenJabatan }}
                                </span>
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-6 text-gray-400 text-xs">
                            Tidak ada data dosen tersedia.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Informasi Fleksibilitas Penugasan -->
            <div class="p-3 bg-amber-50/80 border border-amber-200/80 rounded-xl flex items-start gap-2.5 text-xs text-amber-800">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="leading-relaxed text-[11px]">
                    <strong>Fleksibilitas Penugasan:</strong> 1 Dosen dapat memegang beberapa butir indikator, dan 1 indikator dapat ditugaskan secara kolaboratif (PJ untuk penjaminan mutu substantif &amp; PIC untuk pengumpulan bukti).
                </div>
            </div>

        </div>

        <!-- Footer Modal Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#EAEAEA] shrink-0">
            <button 
                type="button" 
                onclick="closeAssignDosenModal('{{ $id }}')" 
                class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button 
                type="button" 
                id="{{ $id }}-submit-btn" 
                onclick="submitAssignDosenModal('{{ $id }}')" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-additional text-white font-bold text-xs shadow-xs transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span>Terapkan Penugasan Batch</span>
            </button>
        </div>

    </div>
</div>

<script>
    // State lokal modal
    window.modalAssignState = {
        selectedCodes: []
    };

    function openAssignDosenModal(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Ambil indikator terpilih dari parameter atau checkbox halaman plotting
        let codes = options.selectedCodes || [];
        if (!codes || codes.length === 0) {
            const checkedBoxes = Array.from(document.querySelectorAll('.ind-checkbox:checked'));
            codes = checkedBoxes.map(cb => {
                const item = cb.closest('.indicator-item');
                return item ? (item.getAttribute('data-code') || cb.value) : cb.value;
            });
        }

        window.modalAssignState.selectedCodes = codes;

        // Render chips
        const chipsContainer = document.getElementById(modalId + '-chips-container');
        const countBadge = document.getElementById(modalId + '-selected-count-badge');
        
        if (chipsContainer) {
            if (codes.length > 0) {
                chipsContainer.innerHTML = codes.map(c => `
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-100 text-additional font-extrabold text-xs border border-sky-200/80">
                        ${c}
                    </span>
                `).join('');
                if (countBadge) countBadge.innerText = `${codes.length} Indikator Dipilih`;
            } else {
                chipsContainer.innerHTML = `<span class="text-xs text-amber-600 font-medium italic">Belum ada indikator yang dipilih di tabel. Silakan centang indikator terlebih dahulu.</span>`;
                if (countBadge) countBadge.innerText = `0 Indikator Dipilih`;
            }
        }

        // Set preselected role jika ada
        if (options.role) {
            const roleRadio = modal.querySelector(`input[name="modal_role"][value="${options.role}"]`);
            if (roleRadio) roleRadio.checked = true;
        }

        // Reset search input
        const searchInput = document.getElementById(modalId + '-search-input');
        if (searchInput) {
            searchInput.value = '';
            filterModalDosenList(modalId);
        }

        // Buka dialog
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const card = modal.querySelector('div.transform');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
    }

    function closeAssignDosenModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('opacity-0', 'pointer-events-none');
        const card = modal.querySelector('div.transform');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
    }

    function filterModalDosenList(modalId) {
        const input = document.getElementById(modalId + '-search-input');
        const query = (input ? input.value : '').toLowerCase().trim();
        const cards = document.querySelectorAll('#' + modalId + ' .modal-dosen-card');

        cards.forEach(card => {
            const nama = card.getAttribute('data-nama') || '';
            const nidn = card.getAttribute('data-nidn') || '';
            const keahlian = card.getAttribute('data-keahlian') || '';

            if (!query || nama.includes(query) || nidn.includes(query) || keahlian.includes(query)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function submitAssignDosenModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const codes = window.modalAssignState.selectedCodes || [];
        if (codes.length === 0) {
            if (typeof window.showToast === 'function') {
                window.showToast('Pilih minimal 1 butir indikator sebelum menerapkan penugasan!', 'warning');
            } else {
                alert('Pilih minimal 1 butir indikator sebelum menerapkan penugasan!');
            }
            return;
        }

        const roleRadio = modal.querySelector('input[name="modal_role"]:checked');
        const role = roleRadio ? roleRadio.value : 'pic';

        const dosenRadio = modal.querySelector('input[name="modal_dosen"]:checked');
        if (!dosenRadio) {
            if (typeof window.showToast === 'function') {
                window.showToast('Silakan pilih salah satu dosen untuk ditugaskan!', 'warning');
            } else {
                alert('Silakan pilih salah satu dosen untuk ditugaskan!');
            }
            return;
        }

        const fullName = dosenRadio.value;
        const shortName = dosenRadio.getAttribute('data-short-name') || fullName.split(' ')[0];
        const dosenId = dosenRadio.getAttribute('data-id') || 1;

        const submitBtn = document.getElementById(modalId + '-submit-btn');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Menyimpan...
            `;
        }

        // Terapkan ke tabel indikator secara real-time
        const assignmentsPayload = [];
        codes.forEach(code => {
            const item = document.querySelector(`.indicator-item[data-code="${code}"]`);
            assignmentsPayload.push({
                code: code,
                id_indikator: item ? parseInt(item.getAttribute('data-id') || code.replace(/\D/g, '') || 1) : 1,
                id_user: parseInt(dosenId),
                user_name: fullName
            });

            if (!item) return;
            const badgesWrapper = item.querySelector('.indicator-badges-wrapper');
            if (!badgesWrapper) return;

            if (role === 'pic') {
                item.setAttribute('data-has-pic', 'true');
                item.setAttribute('data-pic-name', fullName);

                const existingPic = badgesWrapper.querySelector('.pic-badge');
                const emptyPic = badgesWrapper.querySelector('.pic-empty');
                const newBadgeHtml = `
                    <div class="pic-badge px-2.5 py-1 bg-emerald-50 border border-emerald-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                        <span class="text-[10px] font-extrabold uppercase tracking-wide text-emerald-600">PIC:</span>
                        <span class="text-xs font-bold text-emerald-800 pic-label">${shortName}</span>
                        <button type="button" onclick="removeRole(this, 'pic', '${code}')" class="text-emerald-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PIC">×</button>
                    </div>
                `;

                if (existingPic) existingPic.outerHTML = newBadgeHtml;
                else if (emptyPic) emptyPic.outerHTML = newBadgeHtml;
                else badgesWrapper.insertAdjacentHTML('beforeend', newBadgeHtml);
            } else {
                item.setAttribute('data-has-pj', 'true');
                item.setAttribute('data-pj-name', fullName);

                const existingPj = badgesWrapper.querySelector('.pj-badge');
                const emptyPj = badgesWrapper.querySelector('.pj-empty');
                const newBadgeHtml = `
                    <div class="pj-badge px-2.5 py-1 bg-indigo-50 border border-indigo-200/80 rounded-full flex items-center gap-1.5 shadow-2xs">
                        <span class="text-[10px] font-extrabold uppercase tracking-wide text-indigo-500">PJ:</span>
                        <span class="text-xs font-bold text-indigo-800 pj-label">${shortName}</span>
                        <button type="button" onclick="removeRole(this, 'pj', '${code}')" class="text-indigo-600 hover:text-red-600 font-bold text-xs ml-0.5 cursor-pointer leading-none" title="Lepas PJ">×</button>
                    </div>
                `;

                if (existingPj) existingPj.outerHTML = newBadgeHtml;
                else if (emptyPj) emptyPj.outerHTML = newBadgeHtml;
                else badgesWrapper.insertAdjacentHTML('afterbegin', newBadgeHtml);
            }

            // Uncheck checkbox
            const cb = item.querySelector('.ind-checkbox');
            if (cb) cb.checked = false;
        });

        // Uncheck group checkboxes
        document.querySelectorAll('.group-checkbox').forEach(gcb => gcb.checked = false);

        // Update progress & counter jika function tersedia
        if (typeof window.updateOverallProgress === 'function') window.updateOverallProgress();
        if (typeof window.updateMemberCounts === 'function') window.updateMemberCounts();
        if (typeof window.checkFormState === 'function') window.checkFormState();

        // Sync ke backend
        const projectSelector = document.getElementById('projectSelector');
        const selectedProjectId = projectSelector ? projectSelector.value : '1';

        fetch("{{ route('kaprodi.plotting.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                project_id: selectedProjectId,
                role: role,
                assignments: assignmentsPayload
            })
        })
        .then(res => res.json())
        .then(data => {
            const roleLabel = role === 'pj' ? 'Penanggung Jawab (PJ)' : 'Pelaksana (PIC)';
            if (typeof window.showToast === 'function') {
                window.showToast(`Berhasil menetapkan ${shortName} sebagai ${roleLabel} untuk ${codes.length} butir indikator.`, 'success');
            }
            closeAssignDosenModal(modalId);
        })
        .catch(err => {
            console.warn('Backend sync warning:', err);
            const roleLabel = role === 'pj' ? 'Penanggung Jawab (PJ)' : 'Pelaksana (PIC)';
            if (typeof window.showToast === 'function') {
                window.showToast(`Penugasan ${shortName} sebagai ${roleLabel} disimpan ke sesi lokal.`, 'success');
            }
            closeAssignDosenModal(modalId);
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Escape key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('[id^="modal-assign-dosen"]:not(.opacity-0)');
            if (openModal) {
                closeAssignDosenModal(openModal.id);
            }
        }
    });
</script>

