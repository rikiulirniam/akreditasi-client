@extends('layouts.app')

@section('title', 'Penilaian Indikator')

@section('content')
    @php
        $projectId = $project['id'] ?? $project['project_id'];
        $projectName = $project['nama_project'] ?? $project['name'] ?? 'Project akreditasi';
        $program = $project['program_studi'] ?? $project['prodi'] ?? null;
        $programName = is_array($program) ? ($program['nama'] ?? $program['name'] ?? 'Program studi') : ($program ?? 'Program studi');
        $totalIndicators = count($indicators);
        $savedCount = count(array_filter($savedAssessments, fn (array $assessment): bool => isset($assessment['score'])));
        $selectedScore = (int) old('score', $savedAssessment['score'] ?? 4);
        $scoreWeight = (float) ($indicator['weight'] ?? 2.5);
        $weightedScore = $selectedScore * $scoreWeight;
        $evidence = $indicator['evidence'] ?? [];
        $evidenceStatuses = $savedAssessment['evidence_status'] ?? [];
    @endphp

    <div class="space-y-4" id="reviewer-assessment">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <a href="{{ route('reviewer.assessments') }}" class="text-xs font-semibold text-additional hover:text-primary">
                    &larr; Kembali ke daftar penilaian
                </a>
                <h2 class="mt-1 truncate text-lg font-extrabold tracking-tight text-primary sm:text-xl">{{ $projectName }}</h2>
                <p class="text-xs text-slate-500">{{ $programName }} · {{ $project['jenis_instrumen'] ?? $project['instrumen'] ?? 'Instrumen akreditasi' }}</p>
            </div>
            @if ($isDemo)
                <span class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">Data dummy</span>
            @endif
        </div>

        @if ($apiError)
            <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ $apiError }}</div>
        @endif

        @if ($indicator)
            <div class="grid items-start gap-4 xl:grid-cols-[220px_minmax(0,1fr)_280px]">
                <aside class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-xs font-bold text-slate-800">Navigasi penilaian</h3>
                            <span class="text-[10px] font-bold text-additional">{{ $savedCount }}/{{ $totalIndicators }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-additional" style="width: {{ $totalIndicators > 0 ? ($savedCount / $totalIndicators) * 100 : 0 }}%"></div>
                        </div>
                        <p class="mt-1 text-right text-[9px] text-slate-400">{{ $totalIndicators > 0 ? round(($savedCount / $totalIndicators) * 100) : 0 }}% selesai</p>
                    </div>

                    <nav class="max-h-[65vh] space-y-3 overflow-y-auto p-3" aria-label="Daftar indikator">
                        @foreach (collect($indicators)->groupBy('criterion') as $criterion => $criterionIndicators)
                            <div>
                                <p class="mb-1 px-2 text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $criterion }}</p>
                                @foreach ($criterionIndicators as $item)
                                    @php
                                        $isCurrentIndicator = (string) $item['id'] === (string) $indicator['id'];
                                        $isIndicatorSaved = isset($savedAssessments[(string) $item['id']]['score']);
                                    @endphp
                                    <a href="{{ route('reviewer.assessment-detail', ['projectId' => $projectId, 'indikator' => $item['id']]) }}"
                                        @class([
                                            'mb-1 flex items-start gap-2 rounded-lg px-2 py-2 text-[10px] transition',
                                            'bg-blue-50 font-bold text-primary ring-1 ring-inset ring-blue-200' => $isCurrentIndicator,
                                            'text-slate-600 hover:bg-slate-50' => ! $isCurrentIndicator,
                                        ])>
                                        <span class="mt-0.5 shrink-0 font-mono">{{ $item['code'] }}</span>
                                        @if ($isIndicatorSaved)
                                            <svg class="ml-auto h-3.5 w-3.5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                                            </svg>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </nav>
                    <div class="flex gap-3 border-t border-slate-100 px-4 py-2 text-[9px] text-slate-400">
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Sudah dinilai</span>
                        <span class="flex items-center gap-1"><span class="h-2 w-2 rounded-full border border-slate-300"></span>Belum dinilai</span>
                    </div>
                </aside>

                <main class="min-w-0 space-y-3">
                    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="rounded-md bg-blue-50 px-2 py-1 font-mono text-[10px] font-bold text-additional">{{ $indicator['code'] }}</span>
                            <span class="text-[10px] text-slate-400">Indikator {{ $indicatorIndex + 1 }} dari {{ $totalIndicators }}</span>
                        </div>
                        <h3 class="text-sm font-bold leading-relaxed text-slate-800 sm:text-base">{{ $indicator['title'] }}</h3>
                        <div class="mt-3 rounded-lg border border-amber-100 bg-amber-50 px-3 py-2.5">
                            <p class="text-[9px] font-extrabold uppercase tracking-wide text-amber-800">Parameter penilaian</p>
                            <p class="mt-1 text-[10px] leading-relaxed text-slate-600">{{ $indicator['parameter'] }}</p>
                        </div>
                    </section>

                    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800">Narasi program studi</h3>
                                <p class="mt-0.5 text-[9px] text-slate-400">Pernyataan yang disampaikan oleh program studi.</p>
                            </div>
                            <span class="shrink-0 text-[9px] text-slate-400">Baca saja</span>
                        </div>
                        <div class="prose prose-sm mt-3 max-w-none whitespace-pre-line text-[10px] leading-relaxed text-slate-600">{{ $indicator['narrative'] ?? 'Belum ada narasi untuk indikator ini.' }}</div>
                    </section>

                    @if (collect($evidence)->contains(fn (array $item, int $index): bool => ($evidenceStatuses[$index] ?? $item['status'] ?? '') !== 'Disetujui'))
                        <div class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-100 px-3 py-2.5 text-[10px] text-amber-900">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m12 9 3 6m-3-6-3 6m3-6V3m0 18v-3m9-6h-3M6 12H3" />
                            </svg>
                            <span><strong>Masih ada bukti berstatus pending.</strong> Periksa seluruh bukti sebelum menyelesaikan penilaian indikator.</span>
                        </div>
                    @endif

                    <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800">Bukti pendukung</h3>
                                <p class="mt-0.5 text-[9px] text-slate-400">Verifikasi relevansi, aksesibilitas, dan keterkaitan bukti.</p>
                            </div>
                            <span class="rounded-md bg-blue-50 px-2 py-1 text-[9px] font-bold text-additional">{{ count($evidence) }} bukti</span>
                        </div>

                        @forelse ($evidence as $evidenceIndex => $item)
                            @php
                                $evidenceStatus = $evidenceStatuses[$evidenceIndex] ?? $item['status'] ?? 'Pending';
                                $statusClasses = match ($evidenceStatus) {
                                    'Disetujui' => 'bg-emerald-100 text-emerald-700',
                                    'Perlu revisi' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <article class="{{ $evidenceIndex > 0 ? 'border-t border-slate-100' : '' }} py-3">
                                <div class="flex items-start gap-2.5">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-additional" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h6l4.5 4.5v12h-12.75V3.75H7.5Zm6 0v4.5H18m-10.5 4.5h7.5m-7.5 3h7.5" />
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">{{ $item['type'] ?? 'Dokumen' }}</p>
                                            <span class="rounded px-2 py-0.5 text-[8px] font-bold {{ $statusClasses }}">{{ $evidenceStatus }}</span>
                                        </div>
                                        <p class="mt-1 text-[10px] font-bold leading-snug text-slate-800">{{ $item['name'] }}</p>
                                        <p class="mt-1 text-[9px] text-slate-400">Bukti pendukung · {{ $item['date'] ?? 'Tanggal tidak tersedia' }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex flex-wrap items-center justify-end gap-2">
                                    <span class="mr-auto text-[9px] text-slate-400">Dokumen contoh</span>
                                    <button type="submit" form="assessment-form" name="evidence_action" value="{{ $evidenceIndex }}:Disetujui"
                                        class="rounded-md border border-slate-200 px-2 py-1 text-[9px] font-semibold text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700">
                                        &#10003; Setujui
                                    </button>
                                    <button type="submit" form="assessment-form" name="evidence_action" value="{{ $evidenceIndex }}:Perlu revisi"
                                        class="rounded-md border border-rose-200 px-2 py-1 text-[9px] font-semibold text-rose-600 transition hover:bg-rose-50">
                                        &#9888; Minta revisi
                                    </button>
                                </div>
                            </article>
                        @empty
                            <p class="py-5 text-center text-xs text-slate-400">Belum ada bukti pendukung.</p>
                        @endforelse
                    </section>
                </main>

                <aside class="space-y-3 xl:sticky xl:top-4">
                    <form id="assessment-form" method="POST" action="{{ route('reviewer.assessment-save', ['projectId' => $projectId, 'indicatorId' => $indicator['id']]) }}"
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        @csrf
                        <div class="border-b border-slate-100 px-4 py-3">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-xs font-extrabold text-primary">Panel penilaian</h3>
                                <span class="text-[9px] font-semibold text-additional">{{ $indicator['code'] }}</span>
                            </div>
                            <p class="mt-0.5 text-[9px] text-slate-500">Pilih skor berdasarkan rubrik.</p>
                        </div>

                        <fieldset class="space-y-1.5 p-3">
                            <legend class="sr-only">Pilih skor indikator</legend>
                            @php
                                $apiRubriks = $indicator['rubriks'] ?? [];
                                $rubricOptions = [];
                                if (!empty($apiRubriks)) {
                                    foreach ($apiRubriks as $r) {
                                        $s = (int) ($r['skor'] ?? 0);
                                        $desc = $r['deskripsi_rubrik'] ?? $r['deskripsi'] ?? '';
                                        $title = match ($s) {
                                            4 => 'Sangat baik',
                                            3 => 'Baik',
                                            2 => 'Cukup',
                                            1 => 'Kurang',
                                            default => 'Tidak memenuhi',
                                        };
                                        $rubricOptions[$s] = [
                                            'id' => $r['id'] ?? null,
                                            'title' => $title,
                                            'description' => $desc ?: 'Kriteria penilaian sesuai rubrik instrumen.',
                                        ];
                                    }
                                    krsort($rubricOptions);
                                } else {
                                    $rubricOptions = [
                                        4 => ['id' => null, 'title' => 'Sangat baik', 'description' => 'Memenuhi seluruh aspek penilaian dengan bukti yang sangat relevan.'],
                                        3 => ['id' => null, 'title' => 'Baik', 'description' => 'Memenuhi sebagian besar aspek penilaian dan didukung bukti.'],
                                        2 => ['id' => null, 'title' => 'Cukup', 'description' => 'Memenuhi beberapa aspek, tetapi masih perlu dilengkapi.'],
                                        1 => ['id' => null, 'title' => 'Kurang', 'description' => 'Pemenuhan aspek penilaian masih terbatas.'],
                                        0 => ['id' => null, 'title' => 'Tidak memenuhi', 'description' => 'Belum memenuhi aspek penilaian yang dipersyaratkan.'],
                                    ];
                                }
                            @endphp
                            @foreach ($rubricOptions as $score => $rubric)
                                <label data-score-option="{{ $score }}"
                                    class="flex cursor-pointer items-start gap-2.5 rounded-lg border p-2.5 transition {{ $selectedScore === $score ? 'border-additional bg-blue-50 ring-1 ring-inset ring-additional' : 'border-slate-200 hover:bg-slate-50' }}">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-slate-100 text-[10px] font-extrabold text-slate-700">{{ $score }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-[10px] font-bold text-slate-800">{{ $rubric['title'] }}</span>
                                        <span class="mt-0.5 block text-[8px] leading-relaxed text-slate-500">{{ $rubric['description'] }}</span>
                                    </span>
                                    <input class="mt-1 h-3.5 w-3.5 shrink-0 accent-[#01568D]" type="radio" name="score" value="{{ $score }}" @checked($selectedScore === $score)>
                                    @if (!empty($rubric['id']))
                                        <input type="hidden" name="rubrik_ids[{{ $score }}]" value="{{ $rubric['id'] }}">
                                    @endif
                                </label>
                            @endforeach
                        </fieldset>

                        <div class="px-3 pb-3">
                            <label for="reviewer-notes" class="mb-1.5 block text-[10px] font-bold text-slate-800">Catatan reviewer</label>
                            <textarea id="reviewer-notes" name="notes" rows="3" maxlength="1000" placeholder="Tuliskan alasan pemberian skor atau catatan perbaikan..."
                                class="w-full resize-y rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-[10px] leading-relaxed text-slate-700 placeholder:text-slate-400 focus:border-additional focus:ring-2 focus:ring-blue-100">{{ old('notes', $savedAssessment['notes'] ?? '') }}</textarea>
                            @error('score')
                                <p class="mt-1 text-[9px] text-rose-600">{{ $message }}</p>
                            @enderror
                            @error('notes')
                                <p class="mt-1 text-[9px] text-rose-600">{{ $message }}</p>
                            @enderror
                            @error('evidence_action')
                                <p class="mt-1 text-[9px] text-rose-600">{{ $message }}</p>
                            @enderror
                            @foreach ($evidence as $evidenceIndex => $item)
                                <input type="hidden" name="evidence_status[{{ $evidenceIndex }}]" value="{{ $evidenceStatuses[$evidenceIndex] ?? $item['status'] ?? 'Pending' }}">
                            @endforeach
                            <p class="mt-1 text-right text-[8px] text-slate-400"><span id="notes-count">0</span>/1000 karakter</p>
                        </div>

                        <div class="mx-3 rounded-lg bg-slate-50 p-2.5">
                            <h4 class="text-[9px] font-bold text-slate-800">Perhitungan skor</h4>
                            <div class="mt-2 grid grid-cols-3 gap-1.5 text-center">
                                <div class="rounded-md border border-slate-200 bg-white p-1.5">
                                    <span class="block text-[8px] text-slate-400">Skor</span>
                                    <strong id="score-value" class="text-xs text-slate-800">{{ $selectedScore }}</strong>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-1.5">
                                    <span class="block text-[8px] text-slate-400">Bobot</span>
                                    <strong class="text-xs text-slate-800">{{ rtrim(rtrim(number_format($scoreWeight, 1), '0'), '.') }}</strong>
                                </div>
                                <div class="rounded-md border border-additional bg-white p-1.5">
                                    <span class="block text-[8px] text-slate-400">Skor terbobot</span>
                                    <strong id="weighted-score" class="text-xs text-additional">{{ number_format($weightedScore, 1) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="p-3">
                            <button type="submit" name="save_assessment" value="1"
                                class="w-full rounded-lg bg-additional px-3 py-2.5 text-[10px] font-bold text-white shadow-sm transition hover:bg-primary focus:outline-none focus:ring-2 focus:ring-additional focus:ring-offset-2">
                                Simpan penilaian
                            </button>
                        </div>
                    </form>

                    <div class="grid grid-cols-2 gap-2">
                        @if ($indicatorIndex > 0)
                            <a href="{{ route('reviewer.assessment-detail', ['projectId' => $projectId, 'indikator' => $indicators[$indicatorIndex - 1]['id']]) }}"
                                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-center text-[9px] font-semibold text-slate-600 hover:bg-slate-50">
                                &larr; Indikator sebelumnya
                            </a>
                        @else
                            <span class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-2 text-center text-[9px] text-slate-300">&larr; Sebelumnya</span>
                        @endif
                        @if ($indicatorIndex < $totalIndicators - 1)
                            <a href="{{ route('reviewer.assessment-detail', ['projectId' => $projectId, 'indikator' => $indicators[$indicatorIndex + 1]['id']]) }}"
                                class="rounded-lg border border-slate-200 bg-white px-2 py-2 text-center text-[9px] font-semibold text-slate-600 hover:bg-slate-50">
                                Indikator berikutnya &rarr;
                            </a>
                        @else
                            <span class="rounded-lg border border-slate-100 bg-slate-50 px-2 py-2 text-center text-[9px] text-slate-300">Berikutnya &rarr;</span>
                        @endif
                    </div>
                </aside>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                <p class="font-semibold text-slate-700">Detail indikator belum tersedia</p>
                <p class="mt-1 text-sm text-slate-500">Tambahkan data indikator ke data project untuk mencoba halaman penilaian.</p>
                <a href="{{ route('reviewer.assessments') }}" class="mt-4 inline-flex rounded-lg bg-additional px-4 py-2 text-sm font-bold text-white">Kembali ke daftar penilaian</a>
            </div>
        @endif
    </div>

    @if ($indicator)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const scoreInputs = document.querySelectorAll('input[name="score"]');
                const scoreValue = document.getElementById('score-value');
                const weightedScore = document.getElementById('weighted-score');
                const notes = document.getElementById('reviewer-notes');
                const notesCount = document.getElementById('notes-count');
                const weight = {{ $scoreWeight }};

                function updateScore() {
                    const selected = document.querySelector('input[name="score"]:checked');
                    const score = selected ? Number(selected.value) : 0;

                    scoreValue.textContent = String(score);
                    weightedScore.textContent = (score * weight).toFixed(1);

                    document.querySelectorAll('[data-score-option]').forEach(function (option) {
                        const isSelected = Number(option.dataset.scoreOption) === score;

                        option.classList.toggle('border-additional', isSelected);
                        option.classList.toggle('bg-blue-50', isSelected);
                        option.classList.toggle('ring-1', isSelected);
                        option.classList.toggle('ring-inset', isSelected);
                        option.classList.toggle('ring-additional', isSelected);
                        option.classList.toggle('border-slate-200', !isSelected);
                    });
                }

                scoreInputs.forEach(function (input) {
                    input.addEventListener('change', updateScore);
                });

                notes.addEventListener('input', function () {
                    notesCount.textContent = String(notes.value.length);
                });
                notesCount.textContent = String(notes.value.length);
                updateScore();
            });
        </script>
    @endif
@endsection
