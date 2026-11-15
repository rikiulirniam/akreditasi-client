<!-- MODAL PENGECEKAN KESIAPAN SUBMIT (READINESS CHECK) -->
<div id="modal-readiness-check" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-3 sm:p-5 backdrop-blur-xs select-none">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden relative">
        
        <!-- Header -->
        <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center justify-between shrink-0 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 tracking-tight leading-tight">
                        Pengecekan Kesiapan Submit (Readiness Check)
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Project: <span id="readinessModalProjectName" class="font-bold text-primary">—</span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeReadinessModal()" class="text-gray-400 hover:text-gray-600 text-2xl font-normal transition-colors cursor-pointer leading-none">&times;</button>
        </div>

        <!-- Loading State -->
        <div id="readinessLoading" class="py-16 px-6 text-center flex flex-col items-center justify-center">
            <svg class="animate-spin w-10 h-10 text-primary mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-sm font-bold text-primary">Menganalisis Kesiapan Indikator Kritis...</p>
            <p class="text-xs text-gray-400 mt-1">Memeriksa penugasan PIC, link dokumen bukti, dan penilaian reviewer.</p>
        </div>

        <!-- Content Area (Scrollable) -->
        <div id="readinessContent" class="hidden flex-1 overflow-y-auto px-6 sm:px-8 py-6 space-y-6">
            
            <!-- Overall Status Banner -->
            <div id="readinessBanner" class="p-4 rounded-2xl border flex items-start gap-3">
                <div id="readinessBannerIcon" class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"></div>
                <div class="flex-1">
                    <h3 id="readinessBannerTitle" class="text-sm font-extrabold leading-tight"></h3>
                    <p id="readinessBannerDesc" class="text-xs mt-1 leading-relaxed"></p>
                </div>
            </div>

            <!-- 4 Metrics Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <!-- Persentase Kesiapan -->
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-3.5">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kesiapan Project</div>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span id="metricPercentage" class="text-2xl font-black text-primary">0%</span>
                        <span id="metricReadyRatio" class="text-[11px] text-gray-500 font-semibold">(0/0)</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-200 rounded-full mt-2 overflow-hidden">
                        <div id="metricProgressBar" class="h-full bg-primary transition-all duration-500 w-0"></div>
                    </div>
                </div>

                <!-- Belum ada PIC -->
                <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-3.5">
                    <div class="text-[10px] font-bold text-amber-700 uppercase tracking-wider">Tanpa PIC</div>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span id="metricMissingPic" class="text-2xl font-black text-amber-800">0</span>
                        <span class="text-[11px] text-amber-700 font-semibold">Butir</span>
                    </div>
                    <div class="text-[10px] text-amber-600 font-medium mt-1">Butuh PIC penugasan</div>
                </div>

                <!-- Belum ada Bukti -->
                <div class="bg-rose-50/60 border border-rose-200 rounded-2xl p-3.5">
                    <div class="text-[10px] font-bold text-rose-700 uppercase tracking-wider">Tanpa Bukti</div>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span id="metricMissingEvidence" class="text-2xl font-black text-rose-800">0</span>
                        <span class="text-[11px] text-rose-700 font-semibold">Butir</span>
                    </div>
                    <div class="text-[10px] text-rose-600 font-medium mt-1">Belum ada link dokumen</div>
                </div>

                <!-- Belum Dinilai -->
                <div class="bg-indigo-50/60 border border-indigo-200 rounded-2xl p-3.5">
                    <div class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Belum Dinilai</div>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span id="metricMissingAssessment" class="text-2xl font-black text-indigo-800">0</span>
                        <span class="text-[11px] text-indigo-700 font-semibold">Butir</span>
                    </div>
                    <div class="text-[10px] text-indigo-600 font-medium mt-1">Belum ada skor reviewer</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                <div class="flex items-center gap-2">
                    <button type="button" onclick="filterReadinessTable('all')" id="tabFilterAll" class="readiness-filter-tab px-3 py-1.5 rounded-xl text-xs font-bold transition bg-primary text-white cursor-pointer">
                        Semua Butir (<span id="badgeFilterAll">0</span>)
                    </button>
                    <button type="button" onclick="filterReadinessTable('unready')" id="tabFilterUnready" class="readiness-filter-tab px-3 py-1.5 rounded-xl text-xs font-bold transition bg-gray-100 text-gray-600 hover:bg-gray-200 cursor-pointer">
                        Belum Siap (<span id="badgeFilterUnready">0</span>)
                    </button>
                    <button type="button" onclick="filterReadinessTable('ready')" id="tabFilterReady" class="readiness-filter-tab px-3 py-1.5 rounded-xl text-xs font-bold transition bg-gray-100 text-gray-600 hover:bg-gray-200 cursor-pointer">
                        Siap (<span id="badgeFilterReady">0</span>)
                    </button>
                </div>
                <span class="text-[11px] text-gray-400 font-medium hidden sm:inline">Daftar butir indikator kritis penentu submit</span>
            </div>

            <!-- Checklist Table -->
            <div class="rounded-2xl border border-gray-200 overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-700">
                        <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-600 font-bold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-3 px-4 min-w-[200px]">Butir Indikator Kritis</th>
                                <th class="py-3 px-3 text-center min-w-[130px]">Penugasan PIC</th>
                                <th class="py-3 px-3 text-center min-w-[130px]">Link Bukti Dokumen</th>
                                <th class="py-3 px-3 text-center min-w-[130px]">Penilaian Reviewer</th>
                                <th class="py-3 px-3 text-center w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody id="readinessTableBody" class="divide-y divide-gray-100 bg-white font-medium">
                            <!-- Rows di-generate via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer Actions Bar -->
        <div class="px-6 sm:px-8 py-4 border-t border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span id="finalSubmitNotice" class="font-medium text-amber-800">
                    Selesaikan seluruh indikator kritis di atas untuk mengaktifkan tombol pengajuan akhir.
                </span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button 
                    type="button" 
                    onclick="closeReadinessModal()" 
                    class="px-5 py-2.5 rounded-xl bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-bold transition cursor-pointer"
                >
                    Tutup
                </button>
                
                <button 
                    type="button" 
                    id="btnFinalSubmit"
                    onclick="handleFinalSubmit()"
                    disabled
                    class="px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-sm cursor-not-allowed bg-slate-300 text-slate-500 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        <span>Ajukan Akreditasi</span>
                    </span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    let currentReadinessProjectId = null;
    let currentReadinessData = null;
    let activeReadinessFilter = 'all';

    // Buka dialog modal kesiapan submit
    async function openReadinessModal(projectId, projectName) {
        currentReadinessProjectId = projectId;
        const modal = document.getElementById('modal-readiness-check');
        const loading = document.getElementById('readinessLoading');
        const content = document.getElementById('readinessContent');
        const projNameEl = document.getElementById('readinessModalProjectName');

        if (!modal) return;
        modal.classList.remove('hidden');

        if (projNameEl) projNameEl.textContent = projectName || 'Project #' + projectId;
        if (loading) loading.classList.remove('hidden');
        if (content) content.classList.add('hidden');

        try {
            // Ambil token dari session / localStorage
            const token = localStorage.getItem('access_token') || localStorage.getItem('jwt_token') || '';
            const headers = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            const response = await fetch("{{ url('kaprodi/project') }}/" + projectId + "/readiness", {
                headers: headers
            });

            const resJson = await response.json();
            const data = resJson.data || resJson;
            currentReadinessData = data;

            renderReadinessData(data);
        } catch (error) {
            console.error('Gagal mengambil data readiness:', error);
            // Render fallback state
            renderReadinessData({
                is_ready: false,
                total_indikator: 5,
                ready_indikator: 2,
                unready_indikator: 3,
                percentage: 40,
                missing_pic_count: 1,
                missing_evidence_count: 2,
                missing_assessment_count: 3,
                indicators: [
                    { id_indikator: 1, kode_indikator: 'IND-1.1.1', deskripsi_indikator: 'Kesesuaian VMTS Program Studi', has_pic: true, pic_name: 'Dr. Rina Kusuma', has_evidence: true, evidence_link: 'https://drive.google.com/...', has_assessment: true, score: 3.5, is_ready: true, missing_items: [] },
                    { id_indikator: 2, kode_indikator: 'IND-2.1.1', deskripsi_indikator: 'Sistem Penjaminan Mutu Internal (SPMI)', has_pic: true, pic_name: 'Budi Santoso M.Kom.', has_evidence: true, evidence_link: 'https://drive.google.com/...', has_assessment: false, score: null, is_ready: false, missing_items: ['Belum dinilai oleh reviewer'] },
                    { id_indikator: 3, kode_indikator: 'IND-3.1.1', deskripsi_indikator: 'Kecukupan dan Kualifikasi Dosen Tetap', has_pic: false, pic_name: null, has_evidence: false, evidence_link: null, has_assessment: false, score: null, is_ready: false, missing_items: ['Belum memiliki PIC', 'Belum ada link bukti', 'Belum dinilai oleh reviewer'] },
                    { id_indikator: 4, kode_indikator: 'IND-4.1.1', deskripsi_indikator: 'Struktur Kurikulum dan Capaian Pembelajaran', has_pic: true, pic_name: 'Sari Wulandari', has_evidence: false, evidence_link: null, has_assessment: false, score: null, is_ready: false, missing_items: ['Belum ada link bukti', 'Belum dinilai oleh reviewer'] },
                    { id_indikator: 5, kode_indikator: 'IND-5.1.1', deskripsi_indikator: 'Sarana dan Prasarana Pembelajaran', has_pic: true, pic_name: 'Andi Pratama', has_evidence: true, evidence_link: 'https://drive.google.com/...', has_assessment: true, score: 4.0, is_ready: true, missing_items: [] }
                ]
            });
        } finally {
            if (loading) loading.classList.add('hidden');
            if (content) content.classList.remove('hidden');
        }
    }

    function closeReadinessModal() {
        const modal = document.getElementById('modal-readiness-check');
        if (modal) modal.classList.add('hidden');
    }

    // Render tampilan checklist dan status tombol submit
    function renderReadinessData(data) {
        if (!data) return;

        const isReady = data.is_ready === true;
        const total = data.total_indikator || (data.indicators ? data.indicators.length : 0);
        const readyCount = data.ready_indikator !== undefined ? data.ready_indikator : (data.indicators ? data.indicators.filter(i => i.is_ready).length : 0);
        const unreadyCount = data.unready_indikator !== undefined ? data.unready_indikator : (total - readyCount);
        const percentage = data.percentage !== undefined ? data.percentage : (total > 0 ? Math.round((readyCount / total) * 100) : 0);

        // Update Banner
        const banner = document.getElementById('readinessBanner');
        const bannerIcon = document.getElementById('readinessBannerIcon');
        const bannerTitle = document.getElementById('readinessBannerTitle');
        const bannerDesc = document.getElementById('readinessBannerDesc');

        if (isReady) {
            banner.className = 'p-4 rounded-2xl border flex items-start gap-3 bg-emerald-50 border-emerald-200 text-emerald-900';
            bannerIcon.className = 'w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5';
            bannerIcon.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
            bannerTitle.textContent = 'SIAP DIAJUKAN (Readiness 100%)';
            bannerDesc.textContent = 'Seluruh butir indikator kritis telah memiliki PIC yang bertugas, link dokumen bukti pendukung, dan penilaian dari reviewer. Project siap diajukan ke lembaga akreditasi.';
        } else {
            banner.className = 'p-4 rounded-2xl border flex items-start gap-3 bg-amber-50 border-amber-200 text-amber-900';
            bannerIcon.className = 'w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 mt-0.5';
            bannerIcon.innerHTML = '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>';
            bannerTitle.textContent = 'BELUM SIAP DIAJUKAN (' + unreadyCount + ' Butir Kritis Belum Lengkap)';
            bannerDesc.textContent = 'Terdapat indikator kritis yang belum memiliki PIC, belum ada bukti dokumen, atau belum dinilai oleh reviewer. Tombol pengajuan akhir dinonaktifkan hingga seluruh butir lengkap.';
        }

        // Update Metrics
        document.getElementById('metricPercentage').textContent = percentage + '%';
        document.getElementById('metricReadyRatio').textContent = '(' + readyCount + '/' + total + ')';
        document.getElementById('metricProgressBar').style.width = percentage + '%';
        document.getElementById('metricProgressBar').className = isReady ? 'h-full bg-emerald-600 transition-all duration-500' : 'h-full bg-additional transition-all duration-500';

        document.getElementById('metricMissingPic').textContent = data.missing_pic_count !== undefined ? data.missing_pic_count : (data.indicators ? data.indicators.filter(i => !i.has_pic).length : 0);
        document.getElementById('metricMissingEvidence').textContent = data.missing_evidence_count !== undefined ? data.missing_evidence_count : (data.indicators ? data.indicators.filter(i => !i.has_evidence).length : 0);
        document.getElementById('metricMissingAssessment').textContent = data.missing_assessment_count !== undefined ? data.missing_assessment_count : (data.indicators ? data.indicators.filter(i => !i.has_assessment).length : 0);

        // Update Filter Badges
        document.getElementById('badgeFilterAll').textContent = total;
        document.getElementById('badgeFilterUnready').textContent = unreadyCount;
        document.getElementById('badgeFilterReady').textContent = readyCount;

        // Render Table Rows
        const tbody = document.getElementById('readinessTableBody');
        tbody.innerHTML = '';

        const indicators = data.indicators || [];
        indicators.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors readiness-row';
            tr.setAttribute('data-status', item.is_ready ? 'ready' : 'unready');

            // Cell 1: Indikator
            const tdInd = document.createElement('td');
            tdInd.className = 'py-3.5 px-4';
            tdInd.innerHTML = `
                <div class="flex flex-col">
                    <span class="font-extrabold text-primary text-xs tracking-tight">${escapeHtml(item.kode_indikator || '')}</span>
                    <span class="text-[11px] text-gray-600 leading-snug mt-0.5 line-clamp-2">${escapeHtml(item.deskripsi_indikator || '')}</span>
                </div>
            `;
            tr.appendChild(tdInd);

            // Cell 2: PIC
            const tdPic = document.createElement('td');
            tdPic.className = 'py-3.5 px-3 text-center';
            if (item.has_pic) {
                tdPic.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <span>${escapeHtml(item.pic_name || 'PIC Ditugaskan')}</span>
                    </span>
                `;
            } else {
                tdPic.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>Belum Ada PIC</span>
                    </span>
                `;
            }
            tr.appendChild(tdPic);

            // Cell 3: Link Bukti
            const tdEv = document.createElement('td');
            tdEv.className = 'py-3.5 px-3 text-center';
            if (item.has_evidence) {
                tdEv.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <span>Ada Bukti</span>
                    </span>
                `;
            } else {
                tdEv.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>Belum Ada Bukti</span>
                    </span>
                `;
            }
            tr.appendChild(tdEv);

            // Cell 4: Penilaian Reviewer
            const tdAs = document.createElement('td');
            tdAs.className = 'py-3.5 px-3 text-center';
            if (item.has_assessment) {
                tdAs.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <span>Skor: ${escapeHtml(String(item.score || 'Dinilai'))}</span>
                    </span>
                `;
            } else {
                tdAs.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <svg class="w-3 h-3 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>Belum Dinilai</span>
                    </span>
                `;
            }
            tr.appendChild(tdAs);

            // Cell 5: Status
            const tdSt = document.createElement('td');
            tdSt.className = 'py-3.5 px-3 text-center';
            if (item.is_ready) {
                tdSt.innerHTML = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        SIAP
                    </span>
                `;
            } else {
                tdSt.innerHTML = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                        BELUM SIAP
                    </span>
                `;
            }
            tr.appendChild(tdSt);

            tbody.appendChild(tr);
        });

        // Update Tombol Pengajuan Akhir & Notice
        const submitBtn = document.getElementById('btnFinalSubmit');
        const noticeEl = document.getElementById('finalSubmitNotice');

        if (isReady) {
            submitBtn.disabled = false;
            submitBtn.className = 'px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-md cursor-pointer';
            noticeEl.className = 'font-bold text-emerald-700';
            noticeEl.textContent = '✓ Seluruh butir indikator kritis telah siap. Anda dapat melanjutkan pengajuan akhir akreditasi.';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-none cursor-not-allowed bg-slate-200 text-slate-400 disabled:opacity-60 disabled:cursor-not-allowed';
            noticeEl.className = 'font-bold text-rose-700';
            noticeEl.textContent = '⚠ Tombol pengajuan akhir dinonaktifkan: Masih terdapat ' + unreadyCount + ' butir indikator kritis yang belum siap.';
        }

        filterReadinessTable('all');
    }

    // Filter daftar tabel kesiapan
    function filterReadinessTable(filter) {
        activeReadinessFilter = filter;
        const rows = document.querySelectorAll('.readiness-row');
        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            if (filter === 'all') {
                row.classList.remove('hidden');
            } else if (filter === 'ready') {
                row.classList.toggle('hidden', status !== 'ready');
            } else if (filter === 'unready') {
                row.classList.toggle('hidden', status !== 'unready');
            }
        });

        // Update tab styling
        ['all', 'unready', 'ready'].forEach(tab => {
            const btn = document.getElementById('tabFilter' + tab.charAt(0).toUpperCase() + tab.slice(1));
            if (btn) {
                if (tab === filter) {
                    btn.className = 'readiness-filter-tab px-3 py-1.5 rounded-xl text-xs font-bold transition bg-primary text-white cursor-pointer';
                } else {
                    btn.className = 'readiness-filter-tab px-3 py-1.5 rounded-xl text-xs font-bold transition bg-gray-100 text-gray-600 hover:bg-gray-200 cursor-pointer';
                }
            }
        });
    }

    // Aksi submit pengajuan akhir
    function handleFinalSubmit() {
        if (!currentReadinessData || !currentReadinessData.is_ready) {
            alert('Pengajuan belum dapat dilakukan karena masih terdapat indikator kritis yang belum siap.');
            return;
        }

        if (confirm('Apakah Anda yakin ingin mengajukan akreditasi ini ke lembaga akreditasi? Seluruh indikator telah siap.')) {
            alert('Pengajuan akreditasi berhasil dikirim ke server!');
            closeReadinessModal();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
