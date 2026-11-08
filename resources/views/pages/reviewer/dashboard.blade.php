@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $userName = $user['nama'] ?? $user['name'] ?? 'Reviewer';
        $firstName = explode(' ', trim((string) $userName))[0] ?: 'Reviewer';
    @endphp

    <div class="space-y-6 sm:space-y-7">
        <section class="relative overflow-hidden rounded-3xl bg-primary px-6 py-7 text-white shadow-lg sm:px-8 sm:py-8">
            <div class="relative z-10 max-w-2xl">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold tracking-wide text-secondary">
                        PORTAL REVIEWER AKREDITASI
                    </span>
                    @if ($isDemo)
                        <span class="inline-flex rounded-full bg-amber-300/20 px-3 py-1 text-xs font-bold text-amber-100">DATA DUMMY</span>
                    @endif
                </div>
                <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Halo, {{ $firstName }}!</h2>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-white/75">
                    Pantau project akreditasi yang ditugaskan dan lanjutkan penilaian indikator program studi.
                </p>
                @if ($isDemo)
                    <p class="mt-2 text-xs text-white/60">Data contoh aktif. Hapus <code class="text-white">resources/reviewer-dummy-data.php</code> untuk memakai API.</p>
                @endif
                <a href="{{ route('reviewer.assessments') }}"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-secondary px-4 py-2.5 text-sm font-bold text-primary transition hover:brightness-95">
                    Buka penilaian
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>
                </a>
            </div>
            <div class="pointer-events-none absolute -right-8 -top-20 h-64 w-64 rounded-full bg-additional/50 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 right-32 h-56 w-56 rounded-full bg-secondary/15 blur-3xl"></div>
        </section>

        @if ($apiError)
            <div role="alert" class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p>{{ $apiError }}</p>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan penilaian">
            @foreach ([
                ['label' => 'Project ditugaskan', 'value' => $projectCount, 'caption' => 'Project akreditasi untuk Anda', 'tone' => 'bg-blue-50 text-additional', 'icon' => 'M4.5 6.75h15m-15 5.25h15m-15 5.25h9m-10.5-13.5h18v16.5h-18z'],
                ['label' => 'Sedang dinilai', 'value' => $inProgressCount, 'caption' => 'Project dalam proses penilaian', 'tone' => 'bg-amber-50 text-amber-600', 'icon' => 'M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['label' => 'Selesai dinilai', 'value' => $completedCount, 'caption' => 'Project sudah diselesaikan', 'tone' => 'bg-emerald-50 text-emerald-600', 'icon' => 'm5 12 4 4L19 6'],
                ['label' => 'Rata-rata progres', 'value' => $averageProgress.'%', 'caption' => 'Progres seluruh project', 'tone' => 'bg-rose-50 text-rose-500', 'icon' => 'M12 3v18m9-9H3'],
            ] as $stat)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                            <p class="mt-3 text-3xl font-extrabold tracking-tight text-primary">{{ $stat['value'] }}</p>
                        </div>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $stat['tone'] }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ $stat['caption'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.7fr)_minmax(280px,0.9fr)]">
            <article id="projects" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h3 class="font-bold text-slate-800">Ringkasan progres penilaian</h3>
                        <p class="mt-1 text-xs text-slate-500">Project yang ditugaskan kepada Anda.</p>
                    </div>
                    <a href="{{ route('reviewer.assessments') }}" class="text-xs font-bold text-additional hover:text-primary">
                        Lihat semua <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                @if (count($projects))
                    <div class="divide-y divide-slate-100 px-5 sm:px-6">
                        @foreach ($projects as $project)
                            @php
                                $projectName = $project['nama_project'] ?? $project['name'] ?? $project['nama'] ?? 'Project akreditasi';
                                $program = $project['program_studi'] ?? $project['prodi'] ?? null;
                                $programName = is_array($program) ? ($program['nama'] ?? $program['name'] ?? 'Program studi') : ($program ?? $project['nama_prodi'] ?? 'Program studi');
                                $instrument = $project['jenis_instrumen'] ?? $project['instrumen'] ?? 'Instrumen akreditasi';
                                $targetYear = $project['target_submit'] ?? $project['tahun'] ?? $project['tahun_akademik'] ?? '—';
                                $status = $project['status'] ?? $project['status_project'] ?? 'Menunggu';
                                $progress = $project['_reviewer_progress'] ?? 0;
                                $progress = is_numeric($progress) ? min(100, max(0, (float) $progress)) : 0;
                                $progressText = rtrim(rtrim(number_format($progress, 1), '0'), '.');
                            @endphp
                            <div class="grid gap-3 py-4 sm:grid-cols-[minmax(0,1.4fr)_minmax(150px,1fr)_auto] sm:items-center">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-additional">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h6l2.25 2.25h8.25v9.75H3.75V6.75Z" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        @if (isset($project['id']) || isset($project['project_id']))
                                            <a href="{{ route('reviewer.assessment-detail', ['projectId' => $project['id'] ?? $project['project_id']]) }}"
                                                class="truncate text-sm font-bold text-slate-800 hover:text-additional">{{ $projectName }}</a>
                                        @else
                                            <p class="truncate text-sm font-bold text-slate-800">{{ $projectName }}</p>
                                        @endif
                                        <p class="mt-1 truncate text-xs text-slate-500">{{ $programName }} · {{ $instrument }} · {{ $targetYear }}</p>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between gap-3 text-xs">
                                        <span class="text-slate-500">Progres penilaian</span>
                                        <span class="font-bold text-slate-700">{{ $progressText }}%</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-additional transition-all" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                                <span class="w-fit rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-additional">{{ $status }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5v12.5H3.75zM8 6.75V4.5h8v2.25" />
                            </svg>
                        </span>
                        <p class="mt-3 font-semibold text-slate-700">Belum ada project penilaian</p>
                        <p class="mt-1 text-sm text-slate-500">Project yang ditugaskan akan muncul di sini.</p>
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <h3 class="font-bold text-slate-800">Kinerja penilaian</h3>
                    <p class="mt-1 text-xs text-slate-500">Ringkasan project yang ditugaskan.</p>
                </div>
                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row xl:flex-col 2xl:flex-row">
                    <div class="relative flex h-36 w-36 shrink-0 items-center justify-center rounded-full"
                        style="background: conic-gradient(#01568D {{ $averageProgress }}%, #e2e8f0 0)">
                        <div class="flex h-28 w-28 flex-col items-center justify-center rounded-full bg-white">
                            <span class="text-2xl font-extrabold text-primary">{{ $averageProgress }}%</span>
                            <span class="text-[10px] text-slate-500">Rata-rata progres</span>
                        </div>
                    </div>
                    <div class="w-full space-y-3">
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Selesai dinilai</span>
                            <strong class="text-slate-800">{{ $completedCount }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>Sedang dinilai</span>
                            <strong class="text-slate-800">{{ $inProgressCount }}</strong>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="flex items-center gap-2 text-slate-600"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>Total project</span>
                            <strong class="text-slate-800">{{ $projectCount }}</strong>
                        </div>
                    </div>
                </div>
                <a href="{{ route('reviewer.assessments') }}"
                    class="mt-6 flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-primary transition hover:border-additional hover:bg-blue-50">
                    Lihat daftar penilaian
                </a>
            </article>
        </section>
    </div>
@endsection