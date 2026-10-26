@extends('layouts.app')

@section('title', 'Penilaian')

@section('content')
    <div class="space-y-5">
        <section class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-slate-500">Ruang kerja reviewer</p>
                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-primary">Project akreditasi</h2>
                <p class="mt-2 text-sm text-slate-500">Project yang ditugaskan dan progres penilaian indikator.</p>
            </div>
            <a href="{{ route('reviewer.dashboard') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-additional hover:text-additional">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6M9 12h12" />
                </svg>
                Kembali ke dashboard
            </a>
        </section>

        @if ($apiError)
            <div role="alert" class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p>{{ $apiError }}</p>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <div>
                    <h3 class="font-bold text-slate-800">Daftar project penilaian</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ count($projects) }} dari {{ $totalProjects }} project ditugaskan</p>
                </div>
                <form method="GET" action="{{ route('reviewer.assessments') }}" class="flex flex-wrap items-center gap-2">
                    @if ($isDemo)
                        <span class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700">Data dummy</span>
                    @endif
                    <label class="sr-only" for="status-filter">Filter status</label>
                    <select id="status-filter" name="status" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-additional focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $statusOption)
                            @php
                                $statusLabel = match ($statusOption) {
                                    'sedang_dinilai' => 'Sedang dinilai',
                                    'menunggu' => 'Menunggu',
                                    'selesai' => 'Selesai',
                                    default => ucfirst(str_replace('_', ' ', $statusOption)),
                                };
                            @endphp
                            <option value="{{ $statusOption }}" @selected($statusFilter === $statusOption)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                    <label class="sr-only" for="year-filter">Filter tahun</label>
                    <select id="year-filter" name="tahun" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm focus:border-additional focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">Semua tahun</option>
                        @foreach ($years as $yearOption)
                            <option value="{{ $yearOption }}" @selected($yearFilter === $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-additional px-3 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-primary">Filter</button>
                    @if ($statusFilter !== '' || $yearFilter !== '')
                        <a href="{{ route('reviewer.assessments') }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1050px] text-left">
                    <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 sm:px-6">Nama project</th>
                            <th scope="col" class="px-4 py-3.5">Program studi</th>
                            <th scope="col" class="px-4 py-3.5">Jenjang</th>
                            <th scope="col" class="px-4 py-3.5">Jenis instrumen</th>
                            <th scope="col" class="px-4 py-3.5">Target submit</th>
                            <th scope="col" class="px-4 py-3.5">Progres penilaian</th>
                            <th scope="col" class="px-4 py-3.5">Status</th>
                            <th scope="col" class="px-4 py-3.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($projects as $project)
                            @php
                                $projectName = $project['nama_project'] ?? $project['name'] ?? $project['nama'] ?? 'Project akreditasi';
                                $program = $project['program_studi'] ?? $project['prodi'] ?? null;
                                $programName = is_array($program) ? ($program['nama'] ?? $program['name'] ?? '—') : ($program ?? $project['nama_prodi'] ?? '—');
                                $degree = $project['jenjang'] ?? (is_array($program) ? ($program['jenjang'] ?? null) : null) ?? '—';
                                $instrument = $project['jenis_instrumen'] ?? $project['instrumen'] ?? '—';
                                $targetYear = $project['target_submit'] ?? $project['tahun'] ?? $project['tahun_akademik'] ?? '—';
                                $status = $project['status'] ?? $project['status_project'] ?? 'Menunggu';
                                $progress = $project['_reviewer_progress'] ?? 0;
                                $progress = is_numeric($progress) ? min(100, max(0, (float) $progress)) : 0;
                                $progressText = rtrim(rtrim(number_format($progress, 1), '0'), '.');
                                $statusClasses = match ($project['_reviewer_status_key']) {
                                    'sedang_dinilai' => 'border border-blue-200 bg-blue-50 text-blue-700',
                                    'menunggu' => 'border border-amber-200 bg-amber-50 text-amber-700',
                                    'selesai' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
                                    default => 'border border-slate-200 bg-slate-50 text-slate-700',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-additional">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h6l2.25 2.25h8.25v9.75H3.75V6.75Z" />
                                            </svg>
                                        </span>
                                        <span class="max-w-[220px] font-bold text-slate-800">{{ $projectName }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $programName }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600">{{ $degree }}</span>
                                </td>
                                <td class="px-4 py-4 text-slate-600">{{ $instrument }}</td>
                                <td class="px-4 py-4 text-slate-600">{{ $targetYear }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex min-w-28 items-center gap-2">
                                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-additional" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-slate-700">{{ $progressText }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">{{ $status }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    @if (isset($project['id']) || isset($project['project_id']))
                                        <a href="{{ route('reviewer.assessment-detail', ['projectId' => $project['id'] ?? $project['project_id']]) }}"
                                            class="inline-flex whitespace-nowrap items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-additional transition hover:border-additional hover:bg-blue-50">
                                            {{ $progress > 0 ? 'Lanjutkan' : 'Mulai menilai' }}
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400">Belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-14 text-center">
                                    <p class="font-semibold text-slate-700">Belum ada project penilaian</p>
                                    <p class="mt-1 text-sm text-slate-500">Project yang ditugaskan akan muncul di halaman ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
