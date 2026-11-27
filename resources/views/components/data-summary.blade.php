@props([
    'url' => null,
    'title' => 'Ringkasan data project',
])

<section class="rounded-2xl border border-[#EAEAEA] bg-white p-5 sm:p-6 shadow-xs" aria-labelledby="data-summary-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="data-summary-title" class="text-base font-extrabold text-primary">{{ $title }}</h2>
            <p class="mt-1 text-xs text-gray-500">Ringkasan resmi dari API data-summary project</p>
        </div>
        <span data-summary-overall class="hidden rounded-full border px-3 py-1 text-[11px] font-bold"></span>
    </div>

    <div data-summary-pending class="mt-4 hidden rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs text-amber-800"></div>
    <div data-summary-error role="alert" class="mt-4 hidden rounded-xl border border-rose-100 bg-rose-50 p-4 text-sm text-rose-700"></div>
    <div data-summary-state role="status" aria-live="polite" class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-4 text-sm text-slate-600">
        Memuat ringkasan data...
    </div>
    <div data-summary-metrics class="mt-4 hidden grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4"></div>
    <div class="mt-3 flex justify-end">
        <button type="button" data-summary-expand class="hidden items-center gap-2 rounded-xl border border-primary/15 bg-primary/5 px-3.5 py-2 text-xs font-bold text-primary shadow-sm transition hover:bg-primary hover:text-white">
            Selengkapnya
        </button>
    </div>
    <div data-summary-more class="mt-3 hidden space-y-3"></div>
</section>

<script>
    (() => {
        const summaryRoot = document.currentScript.previousElementSibling;
        if (!summaryRoot) return;

        const endpoint = @js($url);
        const state = summaryRoot.querySelector('[data-summary-state]');
        const error = summaryRoot.querySelector('[data-summary-error]');
        const pending = summaryRoot.querySelector('[data-summary-pending]');
        const overall = summaryRoot.querySelector('[data-summary-overall]');
        const container = summaryRoot.querySelector('[data-summary-metrics]');
        const moreContainer = summaryRoot.querySelector('[data-summary-more]');
        const expandButton = summaryRoot.querySelector('[data-summary-expand]');

        const hasValue = (value) => {
            return value !== null && value !== undefined && value !== '' &&
                !(typeof value === 'object' && Object.keys(value).length === 0);
        };

        const textValue = (value) => {
            if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
            if (typeof value === 'number') return new Intl.NumberFormat('id-ID').format(value);
            return String(value);
        };

        const setBadge = (element, label, colors) => {
            element.textContent = label;
            element.className = `rounded-full border px-2 py-0.5 text-[10px] font-bold ${colors}`;
        };

        const createIcon = (kind) => {
            const icons = {
                students: '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M5 20a7 7 0 0 1 14 0"/>',
                lecturers: '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/><path d="m9 7 2 2 4-4"/>',
                doctoral: '<path d="m3 10 9-5 9 5-9 5-9-5Z"/><path d="M7 12.5V17c2.8 2 7.2 2 10 0v-4.5"/><path d="M21 10v5"/>',
                partners: '<path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M16 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M2 20a6 6 0 0 1 12 0"/><path d="M14 20a4 4 0 0 1 8 0"/>',
            };
            const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('viewBox', '0 0 24 24');
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
            svg.setAttribute('stroke-width', '1.8');
            svg.setAttribute('stroke-linecap', 'round');
            svg.setAttribute('stroke-linejoin', 'round');
            svg.setAttribute('class', 'h-5 w-5');
            svg.innerHTML = icons[kind] ?? icons.students;
            return svg;
        };

        const createMetricCard = (label, value, detail = '', iconKind = 'students', accent = 'sky') => {
            const card = document.createElement('article');
            const accents = {
                sky: 'border-sky-100 bg-gradient-to-br from-white to-sky-50/70 text-sky-700',
                blue: 'border-blue-100 bg-gradient-to-br from-white to-blue-50/70 text-blue-700',
                violet: 'border-violet-100 bg-gradient-to-br from-white to-violet-50/70 text-violet-700',
                emerald: 'border-emerald-100 bg-gradient-to-br from-white to-emerald-50/70 text-emerald-700',
            };
            card.className = `group relative overflow-hidden rounded-2xl border px-4 py-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md ${accents[accent]}`;

            const accentLine = document.createElement('div');
            accentLine.className = `absolute inset-x-0 top-0 h-1 ${accent === 'emerald' ? 'bg-emerald-400' : accent === 'violet' ? 'bg-violet-400' : accent === 'blue' ? 'bg-blue-400' : 'bg-sky-400'}`;

            const heading = document.createElement('div');
            heading.className = 'flex items-start justify-between gap-3';
            const title = document.createElement('p');
            title.className = 'text-[10px] font-bold uppercase tracking-wide text-slate-500';
            title.textContent = label;
            const iconWrap = document.createElement('div');
            iconWrap.className = `flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${accent === 'emerald' ? 'bg-emerald-100' : accent === 'violet' ? 'bg-violet-100' : accent === 'blue' ? 'bg-blue-100' : 'bg-sky-100'}`;
            iconWrap.append(createIcon(iconKind));
            heading.append(title, iconWrap);

            const metric = document.createElement('p');
            metric.className = 'mt-2 text-3xl font-extrabold tracking-tight text-primary';
            metric.textContent = hasValue(value) ? textValue(value) : '-';
            card.append(accentLine, heading, metric);
            if (detail) {
                const detailText = document.createElement('p');
                detailText.className = 'mt-1 truncate text-[10px] text-slate-500';
                detailText.textContent = detail;
                card.append(detailText);
            }
            return card;
        };

        const createDetailCard = (title, items) => {
            const card = document.createElement('article');
            card.className = 'rounded-2xl border border-slate-100 bg-white p-4 shadow-sm';
            const heading = document.createElement('h3');
            heading.className = 'border-b border-slate-100 pb-3 text-xs font-bold uppercase tracking-wide text-slate-600';
            heading.textContent = title;
            const grid = document.createElement('div');
            grid.className = 'mt-3 grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3';
            items.forEach(([label, value]) => {
                const item = document.createElement('div');
                const itemLabel = document.createElement('p');
                itemLabel.className = 'text-[10px] text-slate-500';
                itemLabel.textContent = label;
                const itemValue = document.createElement('p');
                itemValue.className = 'mt-0.5 font-bold text-slate-800';
                itemValue.textContent = hasValue(value) ? textValue(value) : '-';
                item.append(itemLabel, itemValue);
                grid.append(item);
            });
            card.append(heading, grid);
            return card;
        };

        const render = (summary, errorMessage = null) => {
            state.classList.add('hidden');
            container.classList.remove('hidden');
            container.replaceChildren();
            moreContainer.replaceChildren();
            moreContainer.classList.add('hidden');
            error.classList.toggle('hidden', !errorMessage);
            if (errorMessage) error.textContent = errorMessage;
            if (errorMessage) {
                container.classList.add('hidden');
                expandButton.classList.add('hidden');
                return;
            }

            const isSimulation = summary?.is_simulation === true;
            const hasAnyValue = ['mahasiswa', 'dosen', 'tridharma'].some((key) => hasValue(summary?.[key]));
            overall.classList.remove('hidden');
            setBadge(
                overall,
                errorMessage || !hasAnyValue ? 'Data tidak tersedia' : (isSimulation ? 'Simulasi' : 'Data tersedia'),
                errorMessage || !hasAnyValue
                    ? 'border-rose-200 bg-rose-50 text-rose-700'
                    : (isSimulation ? 'border-amber-200 bg-amber-50 text-amber-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700')
            );

            const pendingItems = Array.isArray(summary?.pending)
                ? summary.pending
                : (summary?.pending ? [summary.pending] : []);
            pending.classList.toggle('hidden', pendingItems.length === 0);
            pending.replaceChildren();
            if (pendingItems.length) {
                pending.className = 'mt-4 flex items-start gap-3 rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 text-xs text-amber-800';
                const icon = document.createElement('span');
                icon.className = 'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-200 text-[11px] font-black';
                icon.textContent = '!';
                const text = document.createElement('span');
                text.textContent = `Menunggu integrasi: ${pendingItems.map((item) => typeof item === 'object' ? JSON.stringify(item) : String(item)).join(', ')}`;
                pending.append(icon, text);
            }

            const project = summary?.project;
            const mahasiswa = summary?.mahasiswa;
            const dosen = summary?.dosen;
            const tridharma = summary?.tridharma;

            if (project) {
                const projectRow = document.createElement('div');
                    projectRow.className = 'flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white px-4 py-3 text-xs shadow-sm';
                const projectText = document.createElement('span');
                projectText.className = 'text-slate-500';
                projectText.textContent = 'Project ';
                const projectValue = document.createElement('strong');
                projectValue.className = 'text-slate-800';
                projectValue.textContent = `#${textValue(project.id ?? '-')}`;
                projectText.append(projectValue);

                const yearText = document.createElement('span');
                yearText.className = 'text-slate-500';
                yearText.textContent = `Tahun ${textValue(project.tahun ?? '-')}`;
                projectRow.append(projectText);
                projectRow.append(document.createTextNode(' | '), yearText);

                if (hasValue(project.id_prodi)) {
                    projectRow.append(document.createTextNode(' | '));
                    const prodiText = document.createElement('span');
                    prodiText.className = 'text-slate-500';
                    prodiText.textContent = `ID Prodi ${textValue(project.id_prodi)}`;
                    projectRow.append(prodiText);
                }
                moreContainer.prepend(projectRow);
            }

            container.append(
                createMetricCard('Mahasiswa Aktif', mahasiswa?.total_aktif, mahasiswa?.source ? `Sumber: ${mahasiswa.source}` : '', 'students', 'sky'),
                createMetricCard('Total Dosen', dosen?.total_dosen, dosen?.dosen_tersertifikasi ? `${dosen.dosen_tersertifikasi} dosen tersertifikasi` : '', 'lecturers', 'blue'),
                createMetricCard('Dosen S3', dosen?.total_s3_doktor, dosen?.persentase_s3 ? `${dosen.persentase_s3} dari total dosen` : '', 'doctoral', 'violet'),
                createMetricCard('Kerja Sama Aktif', tridharma?.kerjasama_dudika_aktif, '', 'partners', 'emerald'),
            );

            const jabatan = dosen?.jabatan_fungsional;
            if (jabatan) {
                moreContainer.append(createDetailCard('Jabatan Fungsional Dosen', [
                    ['Guru Besar', jabatan.guru_besar],
                    ['Lektor Kepala', jabatan.lektor_kepala],
                    ['Lektor', jabatan.lektor],
                    ['Asisten Ahli', jabatan.asisten_ahli],
                    ['Tersertifikasi', dosen.dosen_tersertifikasi],
                ]));
            }

            const penelitian = tridharma?.penelitian;
            const pkm = tridharma?.pkm;
            const publikasi = tridharma?.publikasi;
            const hki = tridharma?.hki;

            if (penelitian) {
                moreContainer.append(createDetailCard('Penelitian', [
                    ['Total Kegiatan', penelitian.total_kegiatan],
                    ['Melibatkan Mahasiswa', penelitian.melibatkan_mahasiswa],
                    ['Hibah BIMA', penelitian.hibah_nasional_bima],
                    ['Hibah Internal', penelitian.hibah_internal_polines],
                    ['Hibah Internasional', penelitian.hibah_internasional],
                    ['Mandiri', penelitian.mandiri],
                ]));
            }
            if (pkm) {
                moreContainer.append(createDetailCard('Pengabdian kepada Masyarakat', [
                    ['Total Kegiatan', pkm.total_kegiatan],
                    ['Melibatkan Mahasiswa', pkm.melibatkan_mahasiswa],
                    ['Hibah Nasional', pkm.hibah_pkm_nasional],
                    ['Hibah Internal', pkm.hibah_internal],
                    ['Hibah Internasional', pkm.hibah_internasional],
                ]));
            }
            if (publikasi) {
                moreContainer.append(createDetailCard('Publikasi', [
                    ['Jurnal Internasional Scopus', publikasi.jurnal_internasional_scopus],
                    ['Jurnal Nasional SINTA', publikasi.jurnal_nasional_sinta],
                    ['Prosiding IEEE', publikasi.prosiding_seminar_ieee],
                    ['Buku ISBN', publikasi.buku_isbn],
                ]));
            }
            if (hki) {
                moreContainer.append(createDetailCard('HKI', [
                    ['Paten Terdaftar', hki.paten_terdaftar],
                    ['Hak Cipta Software', hki.hak_cipta_program_komputer],
                ]));
            }

            expandButton.textContent = 'Selengkapnya';
            expandButton.classList.toggle('hidden', moreContainer.children.length === 0);
        };

        expandButton.addEventListener('click', () => {
            const isExpanded = !moreContainer.classList.contains('hidden');
            moreContainer.classList.toggle('hidden', isExpanded);
            expandButton.textContent = isExpanded ? 'Selengkapnya' : 'Ringkas';
        });

        if (!endpoint) {
            render(null, 'Project akreditasi belum tersedia.');
            return;
        }

        fetch(endpoint, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(async (response) => {
                const body = await response.json();
                if (!response.ok) throw new Error(body.message || `Ringkasan data gagal dimuat (HTTP ${response.status}).`);
                if (!body.success || !body.data || typeof body.data !== 'object') {
                    throw new Error(body.message || 'Respons ringkasan data tidak memiliki format yang valid.');
                }
                return body.data;
            })
            .then((summary) => render(summary))
            .catch((requestError) => render(null, requestError.message || 'Ringkasan data tidak dapat dimuat.'));
    })();
</script>
