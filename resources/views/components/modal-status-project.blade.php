<!-- MODAL LIFECYCLE STATUS & VERIFIKASI SK AKREDITASI -->
<div id="modal-status-project" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4 backdrop-blur-xs select-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl max-h-[92vh] overflow-y-auto p-6 sm:p-8 relative">
        <!-- Header -->
        <div class="flex justify-between items-start mb-5 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ubah Status Project Akreditasi
                </h2>
                <p class="text-xs text-gray-500 mt-1">Perbarui tahapan status lifecycle project dan lengkapi verifikasi SK.</p>
            </div>
            <button type="button" onclick="closeStatusModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-normal transition-colors cursor-pointer leading-none">&times;</button>
        </div>

        <!-- Project Info Banner -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 mb-5 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Project Terpilih</div>
                <div id="statusModalProjectName" class="text-sm font-extrabold text-primary leading-tight mt-0.5">Nama Project</div>
            </div>
            <div class="text-right">
                <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Status Saat Ini</div>
                <span id="statusModalCurrentBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold mt-0.5 bg-slate-200 text-slate-700">
                    Draft
                </span>
            </div>
        </div>

        <form id="formStatusProject" method="POST" action="">
            @csrf
            @method('PATCH')

            <!-- Pilihan Status Lifecycle -->
            <div class="space-y-2.5 mb-5">
                <label class="block text-xs font-bold text-gray-800 tracking-wide uppercase">
                    Pilih Status Lifecycle <span class="text-rose-500">*</span>
                </label>

                <!-- 1. Draft -->
                <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 bg-white hover:bg-slate-50/70 transition-all cursor-pointer has-checked:border-primary has-checked:bg-primary/5">
                    <input type="radio" name="status_project" value="Draft" class="status-radio-option mt-1 text-primary focus:ring-primary" onchange="evaluateStatusProtection()">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Draft</span>
                            <span class="text-xs font-bold text-gray-800">Penyusunan Awal</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Project dalam tahap inisiasi dan belum memasuki pengisian instrumen aktif.</p>
                    </div>
                </label>

                <!-- 2. On Progress -->
                <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 bg-white hover:bg-slate-50/70 transition-all cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-50/40">
                    <input type="radio" name="status_project" value="On Progress" class="status-radio-option mt-1 text-amber-600 focus:ring-amber-500" onchange="evaluateStatusProtection()">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">On Progress</span>
                            <span class="text-xs font-bold text-gray-800">Sedang Berjalan</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Pengisian lembar kerja instrumen, pembagian tugas tim, dan pengumpulan bukti aktif.</p>
                    </div>
                </label>

                <!-- 3. Pending -->
                <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 bg-white hover:bg-slate-50/70 transition-all cursor-pointer has-checked:border-orange-500 has-checked:bg-orange-50/40">
                    <input type="radio" name="status_project" value="Pending" class="status-radio-option mt-1 text-orange-600 focus:ring-orange-500" onchange="evaluateStatusProtection()">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-orange-100 text-orange-800 border border-orange-200">Pending</span>
                            <span class="text-xs font-bold text-gray-800">Ditangguhkan</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Aktivitas project ditangguhkan sementara menunggu evaluasi atau kelengkapan prasyarat.</p>
                    </div>
                </label>

                <!-- 4. Cancel -->
                <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 bg-white hover:bg-slate-50/70 transition-all cursor-pointer has-checked:border-rose-500 has-checked:bg-rose-50/40">
                    <input type="radio" name="status_project" value="Cancel" class="status-radio-option mt-1 text-rose-600 focus:ring-rose-500" onchange="evaluateStatusProtection()">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Cancel</span>
                            <span class="text-xs font-bold text-gray-800">Dibatalkan</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Project akreditasi dihentikan dan tidak dilanjutkan ke tahap berikutnya.</p>
                    </div>
                </label>

                <!-- 5. Selesai (Verifikasi SK Opsional) -->
                <label class="flex items-start gap-3 p-3 rounded-2xl border border-gray-200 bg-white hover:bg-slate-50/70 transition-all cursor-pointer has-checked:border-emerald-600 has-checked:bg-emerald-50/40">
                    <input type="radio" name="status_project" value="Selesai" id="statusOptionSelesai" class="status-radio-option mt-1 text-emerald-600 focus:ring-emerald-500" onchange="evaluateStatusProtection()">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Selesai</span>
                            <span class="text-xs font-bold text-gray-800">Selesai (Verifikasi SK Opsional)</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-0.5">Project telah tervalidasi tuntas. Nomor SK dan Tanggal Verifikasi bersifat opsional dan dapat dilengkapi jika sudah diterbitkan.</p>
                    </div>
                </label>
            </div>

            <!-- SECTION INPUT SK: KHUSUS STATUS SELESAI (OPSIONAL) -->
            <div id="section-verifikasi-sk" class="hidden transition-all duration-200 space-y-4 p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 mb-5">
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-emerald-900">Verifikasi SK Akreditasi (Opsional)</h4>
                        <p class="text-[11px] text-emerald-700 mt-0.5 leading-relaxed">
                            Data Surat Keputusan (SK) resmi dan tanggal verifikasi akreditasi bersifat opsional dan dapat dilengkapi kemudian jika sudah diterbitkan.
                        </p>
                    </div>
                </div>

                <!-- Input Nomor SK -->
                <div>
                    <label for="statusNomorSk" class="block text-xs font-bold text-gray-800 mb-1">
                        Nomor SK Akreditasi <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <input 
                        type="text" 
                        name="nomor_sk_akreditasi" 
                        id="statusNomorSk" 
                        placeholder="Contoh: 0048/SK/LAM-INFOKOM/Akred/ST/2026 (Opsional)"
                        class="block w-full rounded-xl border border-gray-300 bg-white p-2.5 text-xs text-gray-900 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    />
                </div>

                <!-- Input Tanggal Verifikasi -->
                <div>
                    <label for="statusTanggalVerifikasi" class="block text-xs font-bold text-gray-800 mb-1">
                        Tanggal Verifikasi Akreditasi <span class="text-gray-400 font-normal">(Opsional)</span>
                    </label>
                    <input 
                        type="date" 
                        name="tanggal_verifikasi_akreditasi" 
                        id="statusTanggalVerifikasi" 
                        class="block w-full rounded-xl border border-gray-300 bg-white p-2.5 text-xs text-gray-900 focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    />
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex justify-end items-center gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="button" 
                    onclick="closeStatusModal()" 
                    class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="btnSubmitStatus" 
                    class="px-5 py-2.5 rounded-xl bg-primary hover:bg-additional text-white text-xs font-bold transition shadow-sm cursor-pointer disabled:bg-slate-300 disabled:text-slate-500 disabled:cursor-not-allowed disabled:shadow-none"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Buka dialog modal ubah status project
    function openStatusModal(item) {
        if (!item || !item.id) return;
        const modal = document.getElementById('modal-status-project');
        const form = document.getElementById('formStatusProject');
        const nameEl = document.getElementById('statusModalProjectName');
        const badgeEl = document.getElementById('statusModalCurrentBadge');
        if (!modal || !form) return;

        form.action = "{{ url('kaprodi/project') }}/" + item.id + "/status";
        if (nameEl) nameEl.textContent = item.nama_project || 'Project Akreditasi';

        const rawStatus = (item.status_project || 'draft').toLowerCase();
        let displayStatus = 'Draft';
        let badgeClass = 'bg-slate-200 text-slate-700';

        if (rawStatus === 'in_progress' || rawStatus === 'on_progress' || rawStatus === 'on progress' || rawStatus.includes('progress')) {
            displayStatus = 'On Progress';
            badgeClass = 'bg-amber-100 text-amber-800';
        } else if (rawStatus === 'pending') {
            displayStatus = 'Pending';
            badgeClass = 'bg-orange-100 text-orange-800';
        } else if (rawStatus === 'cancel') {
            displayStatus = 'Cancel';
            badgeClass = 'bg-rose-100 text-rose-800';
        } else if (rawStatus === 'selesai') {
            displayStatus = 'Selesai';
            badgeClass = 'bg-emerald-100 text-emerald-800';
        }

        if (badgeEl) {
            badgeEl.textContent = displayStatus;
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold mt-0.5 ' + badgeClass;
        }

        // Set radio yang sesuai berdasarkan value Title Case
        const radios = document.querySelectorAll('.status-radio-option');
        let matched = false;
        radios.forEach(radio => {
            const radioVal = radio.value.toLowerCase();
            const isProgress = (radioVal === 'on progress') && (rawStatus === 'in_progress' || rawStatus === 'on_progress' || rawStatus === 'on progress' || rawStatus.includes('progress'));
            if (radioVal === rawStatus || isProgress) {
                radio.checked = true;
                matched = true;
            } else {
                radio.checked = false;
            }
        });
        if (!matched && radios.length > 0) {
            radios[0].checked = true;
        }

        // Isi form SK jika sebelumnya sudah ada
        const nomorSkInput = document.getElementById('statusNomorSk');
        const tglInput = document.getElementById('statusTanggalVerifikasi');
        if (nomorSkInput) {
            nomorSkInput.value = item.nomor_sk_akreditasi || item.nomor_sk || '';
        }
        if (tglInput) {
            tglInput.value = item.tanggal_verifikasi_akreditasi || item.tanggal_sk || '';
        }

        modal.classList.remove('hidden');
        evaluateStatusProtection();
    }

    function closeStatusModal() {
        const modal = document.getElementById('modal-status-project');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Evaluasi form status: jika opsi Selesai dipilih, tampilkan input SK opsional
    function evaluateStatusProtection() {
        const selectedRadio = document.querySelector('.status-radio-option:checked');
        const sectionSk = document.getElementById('section-verifikasi-sk');
        const submitBtn = document.getElementById('btnSubmitStatus');

        if (!submitBtn) return;

        // Tombol submit selalu aktif karena SK bersifat murni opsional
        submitBtn.disabled = false;

        if (selectedRadio && selectedRadio.value === 'Selesai') {
            if (sectionSk) sectionSk.classList.remove('hidden');
        } else {
            if (sectionSk) sectionSk.classList.add('hidden');
        }
    }
</script>
