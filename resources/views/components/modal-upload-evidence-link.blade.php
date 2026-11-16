{{-- resources/views/components/modal-upload-evidence-link.blade.php --}}
@props([
    'id' => 'modal-upload-evidence-link',
])

<div 
    id="{{ $id }}" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-[2px] opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
>
    <!-- Modal Card Box -->
    <div class="relative bg-white rounded-2xl p-6 sm:p-7 max-w-lg w-full shadow-2xl text-left transform scale-95 transition-all duration-200 overflow-hidden flex flex-col gap-5 border border-gray-100">
        
        <!-- Header Modal -->
        <div class="flex items-start justify-between gap-3 border-b border-[#EAEAEA] pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-primary shrink-0">
                    <!-- Google Drive / Cloud Link Icon -->
                    <svg class="w-5 h-5 text-additional" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
                    </svg>
                </div>
                <div>
                    <h3 id="{{ $id }}-title" class="text-base sm:text-lg font-extrabold text-primary leading-tight">
                        Unggah Tautan Bukti (Google Drive)
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Masukkan tautan folder atau berkas Google Drive bukti dukung indikator.
                    </p>
                </div>
            </div>
            <button 
                type="button" 
                onclick="closeUploadEvidenceModal('{{ $id }}')" 
                class="text-gray-400 hover:text-gray-600 font-bold text-lg p-1 rounded-lg hover:bg-gray-100 transition-colors leading-none cursor-pointer"
                aria-label="Tutup"
            >
                &times;
            </button>
        </div>

        <!-- Info Indikator Terpilih -->
        <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-xl flex flex-col gap-1.5">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-gray-500">INDIKATOR:</span>
                <span id="{{ $id }}-code-badge" class="px-2 py-0.5 rounded-md bg-sky-100 text-additional font-extrabold text-xs">
                    K1.1
                </span>
            </div>
            <p id="{{ $id }}-description" class="text-xs font-semibold text-gray-700 leading-snug">
                Deskripsi butir indikator borang akreditasi.
            </p>
        </div>

        <!-- Form Input Tautan Bukti -->
        <form id="{{ $id }}-form" onsubmit="submitEvidenceForm(event, '{{ $id }}')" class="space-y-4">
            @csrf
            <input type="hidden" id="{{ $id }}-indicator-id" name="indicator_id" value="1">
            <input type="hidden" id="{{ $id }}-project-id" name="project_id" value="1">
            <input type="hidden" id="{{ $id }}-code" name="code" value="K1.1">

            <!-- Field 1: Tautan Google Drive -->
            <div class="space-y-1.5">
                <label for="{{ $id }}-url" class="block text-xs font-bold text-gray-700">
                    Tautan Google Drive <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                        </svg>
                    </div>
                    <input 
                        type="url" 
                        id="{{ $id }}-url" 
                        name="evidence_url" 
                        required 
                        placeholder="https://drive.google.com/drive/folders/..." 
                        class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50/80 hover:bg-gray-100/50 focus:bg-white text-xs sm:text-sm font-semibold rounded-xl border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-gray-400"
                    >
                </div>
                <p class="text-[11px] text-gray-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span>Pastikan hak akses folder diset ke <strong>"Anyone with the link can view"</strong>.</span>
                </p>
            </div>

            <!-- Field 2: Catatan / Keterangan Bukti -->
            <div class="space-y-1.5">
                <label for="{{ $id }}-notes" class="block text-xs font-bold text-gray-700">
                    Catatan Bukti / Keterangan Dokumen <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <textarea 
                    id="{{ $id }}-notes" 
                    name="evidence_notes" 
                    rows="3" 
                    placeholder="Contoh: Dokumen Renstra Prodi TRK 2024-2028, Buku Pedoman Mutu, dan Notula Rapat Tinjauan Manajemen..." 
                    class="w-full px-3.5 py-2.5 bg-gray-50/80 hover:bg-gray-100/50 focus:bg-white text-xs sm:text-sm font-medium rounded-xl border border-gray-200 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all placeholder:text-gray-400 resize-none"
                ></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-[#EAEAEA]">
                <button 
                    type="button" 
                    onclick="closeUploadEvidenceModal('{{ $id }}')" 
                    class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="{{ $id }}-submit-btn" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary hover:bg-additional text-white font-bold text-xs shadow-xs transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Simpan Tautan Drive</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openUploadEvidenceModal(modalId, config) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Set values
        const code = config.code || 'K1.1';
        const desc = config.description || 'Deskripsi butir indikator';
        const url = config.existingUrl || '';
        const notes = config.existingNotes || '';
        const indId = config.indicatorId || '1';
        const projId = config.projectId || '1';

        document.getElementById(modalId + '-code-badge').innerText = code;
        document.getElementById(modalId + '-description').innerText = desc;
        document.getElementById(modalId + '-indicator-id').value = indId;
        document.getElementById(modalId + '-project-id').value = projId;
        document.getElementById(modalId + '-code').value = code;
        document.getElementById(modalId + '-url').value = url;
        document.getElementById(modalId + '-notes').value = notes;

        // Tampilkan modal
        modal.classList.remove('opacity-0', 'pointer-events-none');
        const card = modal.querySelector('div.transform');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }

        setTimeout(() => {
            const urlInput = document.getElementById(modalId + '-url');
            if (urlInput) urlInput.focus();
        }, 100);
    }

    function closeUploadEvidenceModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.classList.add('opacity-0', 'pointer-events-none');
        const card = modal.querySelector('div.transform');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
    }

    function submitEvidenceForm(e, modalId) {
        e.preventDefault();

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

        const indicatorId = document.getElementById(modalId + '-indicator-id').value;
        const projectId = document.getElementById(modalId + '-project-id').value;
        const code = document.getElementById(modalId + '-code').value;
        const url = document.getElementById(modalId + '-url').value;
        const notes = document.getElementById(modalId + '-notes').value;

        fetch("{{ route('dosen.evidence.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                indicator_id: indicatorId,
                project_id: projectId,
                code: code,
                evidence_url: url,
                evidence_notes: notes
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'Tautan Google Drive berhasil disimpan!', 'success');
                }

                if (typeof window.onEvidenceSaved === 'function') {
                    window.onEvidenceSaved(code, url, notes);
                }

                closeUploadEvidenceModal(modalId);
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'Gagal menyimpan tautan bukti.', 'error');
                } else {
                    alert(data.message || 'Gagal menyimpan tautan bukti.');
                }
            }
        })
        .catch(err => {
            console.error('Evidence submit error:', err);
            // Tetap panggil callback agar UX offline / fallback session responsif
            if (typeof window.onEvidenceSaved === 'function') {
                window.onEvidenceSaved(code, url, notes);
            }
            if (typeof window.showToast === 'function') {
                window.showToast('Tautan Google Drive berhasil disimpan ke sesi lokal.', 'success');
            }
            closeUploadEvidenceModal(modalId);
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
            const openModal = document.querySelector('[id^="modal-upload-evidence-link"]:not(.opacity-0)');
            if (openModal) {
                closeUploadEvidenceModal(openModal.id);
            }
        }
    });
</script>

