@extends('layouts.app')

@section('title', 'Lulusan')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary tracking-tight">Lulusan</h1>
            <p class="text-xs sm:text-sm text-[#5A6E85] mt-1">
                Visualisasi tren kelulusan, masa studi, distribusi IPK, dan data capaian kelulusan mahasiswa terintegrasi API.
            </p>
        </div>
    </div>

    <!-- Summary Cards (KPI Summary Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <!-- Card 1: Total Lulusan -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Lulusan</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['total_lulusan'] ?? 0) }}
                </div>
                <p class="text-xs font-medium text-gray-500">Mahasiswa lulus</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-primary flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-5.25 6.557q0-.36.03-.715" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Lulus Tepat Waktu -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Lulus Tepat Waktu</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tracking-tight">
                    {{ number_format($ringkasan['persen_lulus_tepat_waktu'] ?? 0, 1) }}%
                </div>
                <p class="text-xs font-medium text-gray-500">
                    {{ number_format($ringkasan['jumlah_lulus_tepat_waktu'] ?? 0) }} dari {{ number_format($ringkasan['total_lulusan'] ?? 0) }} lulusan
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Rata-Rata IPK -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-Rata IPK</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['rata_rata_ipk'] ?? 0, 2) }}
                </div>
                <p class="text-xs font-medium text-gray-500">Skala 4.00</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Rata-Rata Masa Studi -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-Rata Masa Studi</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['rata_rata_masa_studi_bulan'] ?? 0, 1) }}
                    <span class="text-sm font-semibold text-gray-500">Bln</span>
                </div>
                <p class="text-xs font-medium text-gray-500">
                    ~ {{ round(($ringkasan['rata_rata_masa_studi_bulan'] ?? 0) / 12, 1) }} Tahun
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <!-- Card 5: Rata-Rata Masa Tunggu -->
        <div class="bg-white rounded-2xl p-5 border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-Rata Masa Tunggu</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ number_format($ringkasan['rata_rata_masa_tunggu_bulan'] ?? 0, 1) }}
                    <span class="text-sm font-semibold text-gray-500">Bln</span>
                </div>
                <p class="text-xs font-medium text-gray-500">
                    @php $avgTunggu = (float) ($ringkasan['rata_rata_masa_tunggu_bulan'] ?? 0); @endphp
                    @if ($avgTunggu > 0 && $avgTunggu <= 6)
                        <span class="text-emerald-600 font-semibold">Memenuhi standar (≤ 6 bln)</span>
                    @elseif ($avgTunggu > 6)
                        <span class="text-amber-600 font-semibold">> 6 bln (melebihi target)</span>
                    @else
                        Standar target ≤ 6 bulan
                    @endif
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Charts Row 1: Tren Kelulusan Tepat Waktu & Tren Masa Studi -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Grafik Tren Kelulusan Tepat Waktu (Line Chart SVG) -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
            <div class="mb-4">
                <h2 class="text-base font-extrabold text-primary tracking-tight">Tren Kelulusan Tepat Waktu</h2>
                <p class="text-xs text-gray-500 mt-1">Persentase mahasiswa yang lulus tepat waktu per kohort/tahun</p>
            </div>

            @php
                $trenYears        = $tren['years'] ?? [];
                $trenTepatWaktu   = $tren['persen_lulus_tepat_waktu'] ?? [];
                $totalTrenPoints  = max(1, count($trenYears));

                // SVG layout constants
                $svgW       = 520;
                $svgH       = 240;
                $padLeft    = 52;
                $padRight   = 20;
                $padTop     = 20;
                $padBottom  = 36;
                $plotW      = $svgW - $padLeft - $padRight;
                $plotH      = $svgH - $padTop - $padBottom;

                // Y scale: 50% at bottom, 100% at top
                $yMin       = 50;
                $yMax       = 100;
                $yRange     = $yMax - $yMin;

                // X step
                $xStep      = $totalTrenPoints > 1 ? $plotW / ($totalTrenPoints - 1) : $plotW / 2;

                // Build points (skip null values)
                $tepatWaktuPts = [];
                foreach ($trenYears as $i => $yr) {
                    $val = $trenTepatWaktu[$i] ?? null;
                    if ($val === null) { continue; }
                    $xPos = $padLeft + ($i * $xStep);
                    $yPos = $padTop + $plotH - (((float)$val - $yMin) / $yRange * $plotH);
                    $yPos = max($padTop, min($padTop + $plotH, $yPos));
                    $tepatWaktuPts[] = ['x' => round($xPos, 1), 'y' => round($yPos, 1), 'val' => (float)$val, 'year' => $yr];
                }

                // Build smooth cubic bezier path
                $buildPath = function (array $pts): string {
                    if (count($pts) < 2) {
                        if (count($pts) === 1) { return 'M '.$pts[0]['x'].' '.$pts[0]['y']; }
                        return '';
                    }
                    $d = 'M '.$pts[0]['x'].' '.$pts[0]['y'];
                    $len = count($pts);
                    for ($i = 0; $i < $len - 1; $i++) {
                        $p0 = $pts[$i];
                        $p1 = $pts[$i + 1];
                        $dx = ($p1['x'] - $p0['x']) / 2;
                        $d .= ' C '.($p0['x'] + $dx).' '.$p0['y'].', '.($p1['x'] - $dx).' '.$p1['y'].', '.$p1['x'].' '.$p1['y'];
                    }
                    return $d;
                };

                // Build area fill path (close to baseline)
                $buildAreaPath = function (array $pts, float $baseY) use ($buildPath): string {
                    if (empty($pts)) { return ''; }
                    $linePath = $buildPath($pts);
                    if (empty($linePath)) { return ''; }
                    $last = end($pts);
                    $first = $pts[0];
                    return $linePath . ' L '.$last['x'].' '.$baseY.' L '.$first['x'].' '.$baseY.' Z';
                };

                $baselineY   = $padTop + $plotH;
                $twPath      = $buildPath($tepatWaktuPts);
                $twAreaPath  = $buildAreaPath($tepatWaktuPts, $baselineY);

                // Y-axis grid lines (50%, 65%, 80%, 95%, 100%)
                $yGrids = [50, 65, 80, 95, 100];
            @endphp

            @if (empty($trenYears))
                <div class="flex items-center justify-center h-48 text-gray-400 text-xs font-medium">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        Data tren belum tersedia
                    </div>
                </div>
            @else
                <svg viewBox="0 0 {{ $svgW }} {{ $svgH }}" class="w-full h-auto overflow-visible select-none" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="tepatWaktuGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10B981" stop-opacity="0.25" />
                            <stop offset="100%" stop-color="#10B981" stop-opacity="0.03" />
                        </linearGradient>
                    </defs>

                    {{-- Y-axis grid lines --}}
                    @foreach ($yGrids as $yg)
                        @php
                            $yPos = $padTop + $plotH - (($yg - $yMin) / $yRange * $plotH);
                            $yPos = max($padTop, min($padTop + $plotH, $yPos));
                        @endphp
                        <line x1="{{ $padLeft }}" y1="{{ round($yPos, 1) }}" x2="{{ $svgW - $padRight }}" y2="{{ round($yPos, 1) }}"
                            stroke="{{ $yg === 85 ? '#10B981' : '#E5EAF0' }}"
                            stroke-width="{{ $yg === 85 ? '1.5' : '1' }}"
                            stroke-dasharray="{{ $yg === 85 ? '4,3' : 'none' }}" />
                        <text x="{{ $padLeft - 6 }}" y="{{ round($yPos + 4, 1) }}" text-anchor="end" font-size="10" font-weight="600" fill="#64748B">{{ $yg }}%</text>
                        @if ($yg === 85)
                            <text x="{{ $svgW - $padRight + 4 }}" y="{{ round($yPos + 4, 1) }}" text-anchor="start" font-size="9" font-weight="700" fill="#10B981">Target</text>
                        @endif
                    @endforeach

                    {{-- Area fill --}}
                    @if (!empty($twAreaPath))
                        <path d="{{ $twAreaPath }}" fill="url(#tepatWaktuGrad)" />
                    @endif

                    {{-- Line --}}
                    @if (!empty($twPath))
                        <path d="{{ $twPath }}" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                    @endif

                    {{-- Data points & labels --}}
                    @foreach ($tepatWaktuPts as $pt)
                        <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="#10B981" stroke="white" stroke-width="2" />
                        <text x="{{ $pt['x'] }}" y="{{ $pt['y'] - 10 }}" text-anchor="middle" font-size="10" font-weight="800" fill="#059669">{{ number_format($pt['val'], 1) }}%</text>
                    @endforeach

                    {{-- X-axis year labels --}}
                    @foreach ($trenYears as $i => $yr)
                        @php $xPos = $padLeft + ($i * $xStep); @endphp
                        <line x1="{{ round($xPos, 1) }}" y1="{{ $padTop + $plotH }}" x2="{{ round($xPos, 1) }}" y2="{{ $padTop + $plotH + 5 }}" stroke="#CBD5E1" stroke-width="1" />
                        <text x="{{ round($xPos, 1) }}" y="{{ $padTop + $plotH + 18 }}" text-anchor="middle" font-size="10.5" font-weight="700" fill="#334155">{{ $yr }}</text>
                    @endforeach
                </svg>
            @endif
        </div>

        <!-- Grafik Tren Masa Studi Lulusan (Bar Chart SVG) -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
            <div class="mb-4">
                <h2 class="text-base font-extrabold text-primary tracking-tight">Tren Masa Studi Lulusan</h2>
                <p class="text-xs text-gray-500 mt-1">Rata-rata masa studi (bulan) lulusan per kohort/tahun</p>
            </div>

            @php
                $masaStudiData  = $tren['rata_masa_studi_bulan'] ?? [];

                // Build bar data (skip null)
                $barPts = [];
                foreach ($trenYears as $i => $yr) {
                    $val = $masaStudiData[$i] ?? null;
                    if ($val === null) { continue; }
                    $barPts[] = ['val' => (float)$val, 'year' => $yr, 'idx' => $i];
                }

                // Y scale for bars: 0 to max value (round up to nearest 10)
                $maxBarVal  = !empty($barPts) ? max(array_column($barPts, 'val')) : 60;
                $yMaxBar    = max(60, ceil($maxBarVal / 10) * 10 + 10);

                $svgBarW    = 520;
                $svgBarH    = 240;
                $bPadLeft   = 52;
                $bPadRight  = 20;
                $bPadTop    = 20;
                $bPadBottom = 36;
                $bPlotW     = $svgBarW - $bPadLeft - $bPadRight;
                $bPlotH     = $svgBarH - $bPadTop - $bPadBottom;

                $barCount   = count($barPts);
                $barW       = $barCount > 0 ? min(50, ($bPlotW / max(1, $barCount)) * 0.6) : 40;
                $barSpacing = $barCount > 0 ? $bPlotW / max(1, $barCount) : $bPlotW;

                // Target standard masa studi (D4 = 48 bulan)
                $targetBulan    = 48;
                $targetYPos     = $bPadTop + $bPlotH - ($targetBulan / $yMaxBar * $bPlotH);
                $targetYPos     = max($bPadTop, min($bPadTop + $bPlotH, $targetYPos));

                $barYGrids = [0, 12, 24, 36, 48, 60];
            @endphp

            @if (empty($barPts))
                <div class="flex items-center justify-center h-48 text-gray-400 text-xs font-medium">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z" />
                        </svg>
                        Data tren belum tersedia
                    </div>
                </div>
            @else
                <svg viewBox="0 0 {{ $svgBarW }} {{ $svgBarH }}" class="w-full h-auto overflow-visible select-none" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#6366F1" stop-opacity="0.9" />
                            <stop offset="100%" stop-color="#6366F1" stop-opacity="0.5" />
                        </linearGradient>
                    </defs>

                    {{-- Y-axis grid lines --}}
                    @foreach ($barYGrids as $yg)
                        @php
                            $yPos = $bPadTop + $bPlotH - ($yg / $yMaxBar * $bPlotH);
                        @endphp
                        <line x1="{{ $bPadLeft }}" y1="{{ round($yPos, 1) }}" x2="{{ $svgBarW - $bPadRight }}" y2="{{ round($yPos, 1) }}"
                            stroke="#E5EAF0" stroke-width="1" />
                        <text x="{{ $bPadLeft - 6 }}" y="{{ round($yPos + 4, 1) }}" text-anchor="end" font-size="10" font-weight="600" fill="#64748B">{{ $yg }}</text>
                    @endforeach

                    {{-- Target batas masa studi line --}}
                    <line x1="{{ $bPadLeft }}" y1="{{ round($targetYPos, 1) }}" x2="{{ $svgBarW - $bPadRight }}" y2="{{ round($targetYPos, 1) }}"
                        stroke="#F59E0B" stroke-width="1.5" stroke-dasharray="5,3" />
                    <text x="{{ $svgBarW - $bPadRight + 4 }}" y="{{ round($targetYPos + 4, 1) }}" text-anchor="start" font-size="9" font-weight="700" fill="#D97706">{{ $targetBulan }} bln</text>

                    {{-- Bars --}}
                    @foreach ($barPts as $k => $bp)
                        @php
                            $xCenter   = $bPadLeft + ($k + 0.5) * $barSpacing;
                            $barHeight = ($bp['val'] / $yMaxBar) * $bPlotH;
                            $barHeight = max(2, $barHeight);
                            $barX      = $xCenter - $barW / 2;
                            $barY      = $bPadTop + $bPlotH - $barHeight;
                            $aboveTarget = $bp['val'] <= $targetBulan;
                        @endphp
                        <rect x="{{ round($barX, 1) }}" y="{{ round($barY, 1) }}"
                            width="{{ round($barW, 1) }}" height="{{ round($barHeight, 1) }}"
                            rx="4" ry="4"
                            fill="{{ $aboveTarget ? 'url(#barGrad)' : '#F87171' }}"
                            opacity="0.9" />
                        <text x="{{ round($xCenter, 1) }}" y="{{ round($barY - 5, 1) }}" text-anchor="middle"
                            font-size="10" font-weight="800" fill="{{ $aboveTarget ? '#4338CA' : '#DC2626' }}">
                            {{ number_format($bp['val'], 1) }}
                        </text>
                        <text x="{{ round($xCenter, 1) }}" y="{{ $bPadTop + $bPlotH + 18 }}" text-anchor="middle"
                            font-size="10.5" font-weight="700" fill="#334155">{{ $bp['year'] }}</text>
                    @endforeach

                    {{-- Y-axis label --}}
                    <text x="12" y="{{ $bPadTop + $bPlotH / 2 }}" text-anchor="middle" font-size="9.5" font-weight="600" fill="#94A3B8"
                        transform="rotate(-90, 12, {{ $bPadTop + $bPlotH / 2 }})">Bulan</text>
                </svg>

                {{-- Legend --}}
                <div class="flex items-center gap-4 mt-3 text-xs font-medium text-gray-500 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-indigo-500 inline-block shrink-0"></span>
                        Masa Studi ≤ {{ $targetBulan }} bln (tepat / lebih cepat)
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-red-400 inline-block shrink-0"></span>
                        Masa Studi > {{ $targetBulan }} bln (melewati batas)
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 h-0 border-t-2 border-dashed border-amber-400 inline-block"></span>
                        Target batas D4 ({{ $targetBulan }} bln)
                    </div>
                </div>
            @endif
        </div>

    </div>

    <!-- Charts Row 2: Tren Masa Tunggu Lulusan & Distribusi IPK -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Grafik Tren Masa Tunggu Lulusan (Bar Chart SVG) -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
            <div class="mb-4">
                <h2 class="text-base font-extrabold text-primary tracking-tight">Tren Masa Tunggu Lulusan</h2>
                <p class="text-xs text-gray-500 mt-1">Rata-rata waktu tunggu (bulan) lulusan mendapatkan pekerjaan per tahun</p>
            </div>

            @php
                $masaTungguData = $tren['rata_masa_tunggu_bulan'] ?? [];

                // Build bar data (skip null)
                $tungguBarPts = [];
                foreach ($trenYears as $i => $yr) {
                    $val = $masaTungguData[$i] ?? null;
                    if ($val === null) { continue; }
                    $tungguBarPts[] = ['val' => (float)$val, 'year' => $yr, 'idx' => $i];
                }

                // Y scale for masa tunggu bars: 0 to max value (target standard is 6 months)
                $maxTungguVal  = !empty($tungguBarPts) ? max(array_column($tungguBarPts, 'val')) : 6;
                $yMaxTunggu    = max(12, ceil($maxTungguVal / 3) * 3 + 3);

                $svgTungguW    = 520;
                $svgTungguH    = 240;
                $tPadLeft      = 52;
                $tPadRight     = 20;
                $tPadTop       = 20;
                $tPadBottom    = 36;
                $tPlotW        = $svgTungguW - $tPadLeft - $tPadRight;
                $tPlotH        = $svgTungguH - $tPadTop - $tPadBottom;

                $tBarCount     = count($tungguBarPts);
                $tBarW         = $tBarCount > 0 ? min(50, ($tPlotW / max(1, $tBarCount)) * 0.6) : 40;
                $tBarSpacing   = $tBarCount > 0 ? $tPlotW / max(1, $tBarCount) : $tPlotW;

                // Target standard masa tunggu (BAN-PT / LAM-INFOKOM: <= 6 bulan)
                $targetMasaTunggu = 6;
                $targetTungguYPos = $tPadTop + $tPlotH - ($targetMasaTunggu / $yMaxTunggu * $tPlotH);
                $targetTungguYPos = max($tPadTop, min($tPadTop + $tPlotH, $targetTungguYPos));

                // Generate clean Y-axis ticks based on yMaxTunggu (e.g. 0, 3, 6, 9, 12...)
                $tungguYGrids = [];
                $stepT = max(1, (int) round($yMaxTunggu / 4));
                for ($g = 0; $g <= $yMaxTunggu; $g += $stepT) {
                    $tungguYGrids[] = $g;
                }
                if (!in_array($targetMasaTunggu, $tungguYGrids) && $targetMasaTunggu <= $yMaxTunggu) {
                    $tungguYGrids[] = $targetMasaTunggu;
                    sort($tungguYGrids);
                }
            @endphp

            @if (empty($tungguBarPts))
                <div class="flex items-center justify-center h-48 text-gray-400 text-xs font-medium">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        Data tren masa tunggu belum tersedia
                    </div>
                </div>
            @else
                <svg viewBox="0 0 {{ $svgTungguW }} {{ $svgTungguH }}" class="w-full h-auto overflow-visible select-none" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="tungguGradPass" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0EA5E9" stop-opacity="0.95" />
                            <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.6" />
                        </linearGradient>
                        <linearGradient id="tungguGradOver" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#F59E0B" stop-opacity="0.95" />
                            <stop offset="100%" stop-color="#FCD34D" stop-opacity="0.6" />
                        </linearGradient>
                    </defs>

                    {{-- Y-axis grid lines --}}
                    @foreach ($tungguYGrids as $yg)
                        @php
                            $yPos = $tPadTop + $tPlotH - ($yg / $yMaxTunggu * $tPlotH);
                        @endphp
                        <line x1="{{ $tPadLeft }}" y1="{{ round($yPos, 1) }}" x2="{{ $svgTungguW - $tPadRight }}" y2="{{ round($yPos, 1) }}"
                            stroke="{{ $yg === $targetMasaTunggu ? '#0D9488' : '#E5EAF0' }}"
                            stroke-width="{{ $yg === $targetMasaTunggu ? '1.5' : '1' }}"
                            stroke-dasharray="{{ $yg === $targetMasaTunggu ? '4,3' : 'none' }}" />
                        <text x="{{ $tPadLeft - 6 }}" y="{{ round($yPos + 4, 1) }}" text-anchor="end" font-size="10" font-weight="600" fill="{{ $yg === $targetMasaTunggu ? '#0D9488' : '#64748B' }}">{{ $yg }}</text>
                    @endforeach

                    {{-- Target threshold line label --}}
                    <text x="{{ $svgTungguW - $tPadRight + 4 }}" y="{{ round($targetTungguYPos + 4, 1) }}" text-anchor="start" font-size="9" font-weight="700" fill="#0D9488">Target (≤ {{ $targetMasaTunggu }} bln)</text>

                    {{-- Bars --}}
                    @foreach ($tungguBarPts as $k => $tp)
                        @php
                            $xCenter     = $tPadLeft + ($k + 0.5) * $tBarSpacing;
                            $barHeight   = ($tp['val'] / $yMaxTunggu) * $tPlotH;
                            $barHeight   = max(3, $barHeight);
                            $barX        = $xCenter - $tBarW / 2;
                            $barY        = $tPadTop + $tPlotH - $barHeight;
                            $isIdeal     = $tp['val'] <= $targetMasaTunggu;
                        @endphp
                        <rect x="{{ round($barX, 1) }}" y="{{ round($barY, 1) }}"
                            width="{{ round($tBarW, 1) }}" height="{{ round($barHeight, 1) }}"
                            rx="4" ry="4"
                            fill="{{ $isIdeal ? 'url(#tungguGradPass)' : 'url(#tungguGradOver)' }}" />
                        <text x="{{ round($xCenter, 1) }}" y="{{ round($barY - 5, 1) }}" text-anchor="middle"
                            font-size="10" font-weight="800" fill="{{ $isIdeal ? '#0284C7' : '#D97706' }}">
                            {{ number_format($tp['val'], 1) }}
                        </text>
                        <text x="{{ round($xCenter, 1) }}" y="{{ $tPadTop + $tPlotH + 18 }}" text-anchor="middle"
                            font-size="10.5" font-weight="700" fill="#334155">{{ $tp['year'] }}</text>
                    @endforeach

                    {{-- Y-axis label --}}
                    <text x="12" y="{{ $tPadTop + $tPlotH / 2 }}" text-anchor="middle" font-size="9.5" font-weight="600" fill="#94A3B8"
                        transform="rotate(-90, 12, {{ $tPadTop + $tPlotH / 2 }})">Bulan</text>
                </svg>

                {{-- Legend --}}
                <div class="flex items-center gap-4 mt-3 text-xs font-medium text-gray-500 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-sky-500 inline-block shrink-0"></span>
                        Masa Tunggu ≤ {{ $targetMasaTunggu }} bln (memenuhi standar)
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-amber-500 inline-block shrink-0"></span>
                        Masa Tunggu > {{ $targetMasaTunggu }} bln (melebihi target)
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-5 h-0 border-t-2 border-dashed border-teal-600 inline-block"></span>
                        Target akreditasi (≤ {{ $targetMasaTunggu }} bln)
                    </div>
                </div>
            @endif
        </div>

        <!-- Grafik Distribusi IPK (Horizontal Bar Chart Standar LAM-INFOKOM) -->
        <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
            <div class="mb-5">
                <h2 class="text-base font-extrabold text-primary tracking-tight">Distribusi IPK Lulusan</h2>
                <p class="text-xs text-gray-500 mt-1">Sebaran IPK lulusan berdasarkan kategori standar LAM-INFOKOM</p>
            </div>

            @php
                $distribusiIpk  = $ringkasan['distribusi_ipk'] ?? [];
                $totalDistribusi = array_sum($distribusiIpk);

                $ipkColors = [
                    '< 2.75'       => ['bg' => '#F87171', 'text' => '#DC2626', 'label' => 'bg-red-100 text-red-700'],
                    '2.75 - 3.00'  => ['bg' => '#FBBF24', 'text' => '#D97706', 'label' => 'bg-amber-100 text-amber-700'],
                    '3.01 - 3.50'  => ['bg' => '#60A5FA', 'text' => '#2563EB', 'label' => 'bg-blue-100 text-blue-700'],
                    '> 3.50'       => ['bg' => '#34D399', 'text' => '#059669', 'label' => 'bg-emerald-100 text-emerald-700'],
                ];
                $maxIpkVal = !empty($distribusiIpk) ? max(array_values($distribusiIpk)) : 1;
            @endphp

            @if (empty($distribusiIpk))
                <div class="flex items-center justify-center h-48 text-gray-400 text-xs font-medium">
                    <div class="text-center">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                        </svg>
                        Data distribusi IPK belum tersedia
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($distribusiIpk as $kategori => $jumlah)
                        @php
                            $color   = $ipkColors[$kategori] ?? ['bg' => '#94A3B8', 'text' => '#475569', 'label' => 'bg-gray-100 text-gray-700'];
                            $pct     = $totalDistribusi > 0 ? round($jumlah / $totalDistribusi * 100, 1) : 0;
                            $barPct  = $maxIpkVal > 0 ? round($jumlah / $maxIpkVal * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-bold text-gray-800">IPK {{ $kategori }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-900">{{ number_format($jumlah) }} mhs</span>
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $color['label'] }}">
                                        {{ number_format($pct, 1) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="w-full h-5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500"
                                    style="width: {{ $barPct }}%; background-color: {{ $color['bg'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- IPK Summary --}}
                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-medium text-gray-500">
                    <span>Total lulusan terdistribusi</span>
                    <span class="font-bold text-gray-900">{{ number_format($totalDistribusi) }} mahasiswa</span>
                </div>
            @endif
        </div>

    </div>

    <!-- Charts Row 3: Ringkasan Per Kohort (Full Width) -->
    <div class="bg-white rounded-2xl border border-[#EAEAEA] p-6 shadow-xs">
        <div class="mb-4">
            <h2 class="text-base font-extrabold text-primary tracking-tight">Ringkasan Per Kohort</h2>
            <p class="text-xs text-gray-500 mt-1">Data mahasiswa baru, capaian kelulusan, masa studi, dan masa tunggu per tahun kohort</p>
        </div>

        @php
            $trenMhsBaru    = $tren['jumlah_mahasiswa_baru'] ?? [];
            $trenIpkLulusan = $tren['rata_ipk_lulusan'] ?? [];
            $trenMasaTunggu = $tren['rata_masa_tunggu_bulan'] ?? [];
        @endphp

        @if (empty($trenYears))
            <div class="flex items-center justify-center h-48 text-gray-400 text-xs font-medium">
                <div class="text-center">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                    </svg>
                    Data kohort belum tersedia
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-2.5 px-3 text-left font-bold text-gray-500 uppercase tracking-wide">Tahun</th>
                            <th class="py-2.5 px-3 text-center font-bold text-gray-500 uppercase tracking-wide">Mhs Baru</th>
                            <th class="py-2.5 px-3 text-center font-bold text-gray-500 uppercase tracking-wide">IPK Lulusan</th>
                            <th class="py-2.5 px-3 text-center font-bold text-gray-500 uppercase tracking-wide">% Tepat Waktu</th>
                            <th class="py-2.5 px-3 text-center font-bold text-gray-500 uppercase tracking-wide">Masa Studi</th>
                            <th class="py-2.5 px-3 text-center font-bold text-gray-500 uppercase tracking-wide">Masa Tunggu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($trenYears as $i => $yr)
                            @php
                                $mhsBaru     = $trenMhsBaru[$i] ?? null;
                                $ipkLulus    = $trenIpkLulusan[$i] ?? null;
                                $tepatWaktu  = ($tren['persen_lulus_tepat_waktu'] ?? [])[$i] ?? null;
                                $masaStudi   = ($tren['rata_masa_studi_bulan'] ?? [])[$i] ?? null;
                                $masaTungguYr = $trenMasaTunggu[$i] ?? null;
                                $isCurrentYear = ($yr == date('Y'));
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition-colors {{ $isCurrentYear ? 'bg-blue-50/40' : '' }}">
                                <td class="py-3 px-3 font-bold text-gray-900 whitespace-nowrap">
                                    {{ $yr }}
                                    @if ($isCurrentYear)
                                        <span class="ml-1 inline-block px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700">Saat ini</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-semibold text-gray-700">
                                    {{ $mhsBaru !== null ? number_format($mhsBaru) : '-' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($ipkLulus !== null)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $ipkLulus >= 3.5 ? 'bg-emerald-50 text-emerald-700' : ($ipkLulus >= 3.0 ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ number_format($ipkLulus, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-medium">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($tepatWaktu !== null)
                                        <span class="font-bold {{ $tepatWaktu >= 85 ? 'text-emerald-600' : 'text-amber-600' }}">
                                            {{ number_format($tepatWaktu, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-medium">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center font-semibold text-gray-700">
                                    {{ $masaStudi !== null ? number_format($masaStudi, 1).' bln' : '—' }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if ($masaTungguYr !== null)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $masaTungguYr <= 6 ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ number_format($masaTungguYr, 1) }} bln
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-medium">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Main Content: Filters & Table Lulusan (Paling Bawah) -->
    <div class="space-y-4 pt-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-gray-900">Daftar Lulusan</h2>
                <p class="text-xs font-medium text-gray-500 mt-0.5">
                    Data kelulusan mahasiswa dengan rincian masa studi, IPK riil, dan status kelulusan. Klik ikon mata untuk melihat detail lengkap.
                </p>
            </div>
        </div>

        <!-- Top Action / Filter Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px] max-w-xs">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </span>
                    <x-input 
                        type="text" 
                        id="searchLulusanInput"
                        placeholder="Cari nama, NIM, jenjang..." 
                        value="{{ $search ?? '' }}"
                        class="pl-10 text-xs"
                    />
                </div>

                <!-- Filter: Program Studi -->
                @if(!empty($prodiOptions))
                <div class="relative w-44 sm:w-52">
                    <select 
                        id="filterProdiSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition truncate"
                    >
                        <option value="">Semua Program Studi</option>
                        @foreach($prodiOptions as $prodi)
                            <option value="{{ $prodi['id'] }}" {{ (string)($selectedProdi ?? '') === (string)$prodi['id'] ? 'selected' : '' }}>
                                {{ $prodi['nama'] }} {{ !empty($prodi['jenjang']) ? '('.$prodi['jenjang'].')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>
                @endif

                <!-- Filter: Angkatan (Tahun Masuk) -->
                <div class="relative w-36 sm:w-40">
                    <select 
                        id="filterAngkatanSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Angkatan</option>
                        @foreach($angkatanOptions as $akt)
                            <option value="{{ $akt }}" {{ (string)($selectedAngkatan ?? '') === (string)$akt ? 'selected' : '' }}>
                                {{ $akt }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <!-- Filter: Tahun Kelulusan -->
                <div class="relative w-36 sm:w-40">
                    <select 
                        id="filterTahunLulusSelect"
                        class="w-full appearance-none px-3.5 py-2.5 pr-9 rounded-[8px] bg-white-form border border-white-bone text-gray-900 font-medium text-xs focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary cursor-pointer transition"
                    >
                        <option value="">Semua Thn Lulus</option>
                        @foreach($tahunLulusOptions as $thnL)
                            <option value="{{ $thnL }}" {{ (string)($selectedTahunLulus ?? '') === (string)$thnL ? 'selected' : '' }}>
                                {{ $thnL }}
                            </option>
                        @endforeach
                    </select>
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-primary">
                        <svg class="w-4 h-4 stroke-[2.5]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </span>
                </div>

                <!-- Reset Filter Button -->
                @php
                    $hasActiveFilter = !empty($search) || !empty($selectedProdi) || !empty($selectedAngkatan) || !empty($selectedTahunLulus);
                @endphp
                <button 
                    type="button" 
                    id="resetFilterBtn"
                    class="{{ $hasActiveFilter ? 'inline-flex' : 'hidden' }} items-center gap-1.5 px-3 py-2.5 rounded-[8px] bg-white-form hover:bg-gray-200 text-gray-600 hover:text-gray-900 text-xs font-semibold border border-white-bone transition cursor-pointer"
                    title="Reset Semua Filter"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- Table: Lulusan -->
        <x-table>
            <x-slot:header>
                <x-table.th class="w-14">No</x-table.th>
                <x-table.th>NIM</x-table.th>
                <x-table.th>Nama Mahasiswa</x-table.th>
                <x-table.th class="w-24">Jenjang</x-table.th>
                <x-table.th class="w-28">Tahun Masuk</x-table.th>
                <x-table.th class="whitespace-nowrap">Tanggal Lulus</x-table.th>
                <x-table.th class="w-28">Masa Studi</x-table.th>
                <x-table.th class="w-24">IPK</x-table.th>
                <x-table.th class="w-36">Kelulusan</x-table.th>
                <x-table.th class="w-24 text-right">Action</x-table.th>
            </x-slot:header>

            @forelse($lulusanList as $index => $item)
                @php
                    $rowNumber = $index + 1 + (($pagination['current_page'] - 1) * $pagination['per_page']);
                    $nama = $item['nama_mahasiswa'] ?? '-';
                    $nim = $item['nim_display'] ?? $item['nim'] ?? '-';
                    $tahunMasuk = $item['tahun_masuk'] ?? '-';
                    $jenjang = $item['jenjang_prodi'] ?? '-';
                    $rawKelulusan = $item['tanggal_kelulusan'] ?? '';
                    $formattedKelulusan = !empty($rawKelulusan) ? \Carbon\Carbon::parse($rawKelulusan)->format('d-m-Y') : '-';
                    $masaStudi = $item['masa_studi_bulan'] ?? null;
                    $masaTunggu = $item['masa_tunggu_bulan'] ?? null;
                    $tempatKerja = $item['tempat_kerja'] ?? null;
                    $ipk = $item['ipk_riil'] ?? null;
                    $lulusTepatWaktu = $item['lulus_tepat_waktu'] ?? null;
                @endphp
                <x-table.row 
                    class="lulusan-row"
                    data-nama="{{ strtolower($nama) }}"
                    data-nim="{{ strtolower($nim) }}"
                    data-jenjang="{{ strtolower($jenjang) }}"
                    data-tahun-masuk="{{ $tahunMasuk }}"
                >
                    <!-- No -->
                    <x-table.cell class="text-gray-500 font-medium w-14">
                        {{ $rowNumber }}
                    </x-table.cell>

                    <!-- NIM -->
                    <x-table.cell class="whitespace-nowrap">
                        <span class="font-mono text-xs sm:text-sm font-semibold text-gray-800">
                            {{ $nim }}
                        </span>
                    </x-table.cell>

                    <!-- Nama Mahasiswa -->
                    <x-table.cell>
                        <span class="font-bold text-sm text-gray-900 tracking-tight">
                            {{ $nama }}
                        </span>
                    </x-table.cell>

                    <!-- Jenjang Prodi -->
                    <x-table.cell class="w-24">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-700">
                            {{ $jenjang }}
                        </span>
                    </x-table.cell>

                    <!-- Tahun Masuk -->
                    <x-table.cell class="w-28">
                        <span class="text-sm font-medium text-gray-700">
                            {{ $tahunMasuk }}
                        </span>
                    </x-table.cell>

                    <!-- Tanggal Lulus -->
                    <x-table.cell class="whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-700 whitespace-nowrap">
                            {{ $formattedKelulusan }}
                        </span>
                    </x-table.cell>

                    <!-- Masa Studi -->
                    <x-table.cell class="w-28 whitespace-nowrap">
                        @if($masaStudi !== null)
                            <span class="text-sm font-medium text-gray-800">
                                {{ $masaStudi }} bln
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>

                    <!-- IPK -->
                    <x-table.cell class="w-24">
                        @if($ipk !== null)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ (float)$ipk >= 3.5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-800' }}">
                                {{ number_format((float)$ipk, 2) }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>

                    <!-- Kelulusan Tepat Waktu (Badge) -->
                    <x-table.cell class="w-36">
                        @if($lulusTepatWaktu === true)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                Tepat Waktu
                            </span>
                        @elseif($lulusTepatWaktu === false)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                Terlambat
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">-</span>
                        @endif
                    </x-table.cell>

                    <!-- Action -->
                    <x-table.cell class="w-24">
                        <div class="flex items-center justify-end gap-2.5">
                            {{-- Edit (Modal) --}}
                            <button
                                type="button"
                                class="open-edit-lulusan-btn text-[#F59E0B] hover:text-[#D97706] transition p-1 cursor-pointer"
                                title="Edit Lulusan"
                                data-action="{{ route('lulusan.update', $item['id'] ?? $item['nim'] ?? '') }}"
                                data-nama="{{ $nama }}"
                                data-nim="{{ $nim }}"
                                data-tahun="{{ $tahunMasuk }}"
                                data-jenis="{{ $item['jenis_pendaftaran'] ?? 'baru' }}"
                                data-kelulusan="{{ !empty($rawKelulusan) ? \Carbon\Carbon::parse($rawKelulusan)->format('Y-m-d') : '' }}"
                                data-masa-studi="{{ $masaStudi ?? '' }}"
                                data-masa-tunggu="{{ $masaTunggu ?? '' }}"
                                data-tempat-kerja="{{ $tempatKerja ?? '' }}"
                            >
                                <x-icon.edit class="w-5 h-5" />
                            </button>

                            {{-- Detail (mata) --}}
                            <a
                                href="{{ route('lulusan.show', $item['nim'] ?? $item['nim_display'] ?? '') }}"
                                class="text-[#3B82F6] hover:text-[#1D4ED8] transition p-1 cursor-pointer"
                                title="Lihat Detail"
                            >
                                <x-icon.eye class="w-5 h-5" />
                            </a>
                        </div>
                    </x-table.cell>
                </x-table.row>
            @empty
                <x-table.empty 
                    colspan="10" 
                    message="Belum ada lulusan" 
                    submessage="Data lulusan dari endpoint API belum tersedia atau filter yang dipilih tidak memiliki hasil." 
                />
            @endforelse
            <tr id="noLulusanFilterMatchRow" class="hidden">
                <td colspan="10" class="text-center py-10 text-gray-400 text-xs font-medium">
                    Tidak ada lulusan yang sesuai dengan filter yang dipilih.
                </td>
            </tr>
        </x-table>

        <!-- Pagination -->
        <x-pagination :paginator="$pagination" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchLulusanInput');
        const prodiFilter = document.getElementById('filterProdiSelect');
        const angkatanFilter = document.getElementById('filterAngkatanSelect');
        const tahunLulusFilter = document.getElementById('filterTahunLulusSelect');
        const resetBtn = document.getElementById('resetFilterBtn');
        const noFilterMatchRow = document.getElementById('noLulusanFilterMatchRow');
        const rows = document.querySelectorAll('.lulusan-row');

        let debounceTimer = null;

        function applyClientSearch() {
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
            let visibleCount = 0;

            rows.forEach(row => {
                const rowNama = row.getAttribute('data-nama') || '';
                const rowNim = row.getAttribute('data-nim') || '';
                const rowJenjang = row.getAttribute('data-jenjang') || '';
                const rowTahun = row.getAttribute('data-tahun-masuk') || '';

                const matchesKeyword = !keyword || (rowNama.includes(keyword) || rowNim.includes(keyword) || rowJenjang.includes(keyword) || rowTahun.includes(keyword));

                if (matchesKeyword) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noFilterMatchRow) {
                noFilterMatchRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            }
        }

        function syncUrlParams() {
            const params = new URLSearchParams(window.location.search);
            const searchVal = searchInput ? searchInput.value.trim() : '';
            const prodiVal = prodiFilter ? prodiFilter.value : '';
            const angkatanVal = angkatanFilter ? angkatanFilter.value : '';
            const tahunLulusVal = tahunLulusFilter ? tahunLulusFilter.value : '';

            if (searchVal) {
                params.set('search', searchVal);
            } else {
                params.delete('search');
            }

            if (prodiVal) {
                params.set('id_prodi', prodiVal);
            } else {
                params.delete('id_prodi');
            }

            if (angkatanVal) {
                params.set('angkatan', angkatanVal);
            } else {
                params.delete('angkatan');
            }

            if (tahunLulusVal) {
                params.set('tahun_lulus', tahunLulusVal);
            } else {
                params.delete('tahun_lulus');
            }

            params.set('page', '1');

            window.location.search = params.toString();
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyClientSearch();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    syncUrlParams();
                }, 600);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(debounceTimer);
                    syncUrlParams();
                }
            });
        }

        [prodiFilter, angkatanFilter, tahunLulusFilter].forEach(el => {
            if (el) {
                el.addEventListener('change', function () {
                    syncUrlParams();
                });
            }
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                window.location.href = "{{ route('lulusan.index') }}";
            });
        }

        // ==========================================
        // Modal Edit Lulusan Handling
        // ==========================================
        const editModal = document.getElementById('editLulusanModal');
        const editForm = document.getElementById('editLulusanForm');
        const editNama = document.getElementById('editNamaInput');
        const editNim = document.getElementById('editNimInput');
        const editTahun = document.getElementById('editTahunInput');
        const editJenis = document.getElementById('editJenisInput');
        const editKelulusan = document.getElementById('editKelulusanInput');
        const editMasaStudi = document.getElementById('editMasaStudiInput');
        const editMasaTunggu = document.getElementById('editMasaTungguInput');
        const editTempatKerja = document.getElementById('editTempatKerjaInput');

        function showModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.lulusan-modal-card');
            modalEl.classList.remove('opacity-0', 'pointer-events-none');
            modalEl.classList.add('opacity-100');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }

        function hideModal(modalEl) {
            if (!modalEl) return;
            const card = modalEl.querySelector('.lulusan-modal-card');
            modalEl.classList.remove('opacity-100');
            modalEl.classList.add('opacity-0', 'pointer-events-none');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }
        }

        function populateAndShowEdit(data) {
            if (editForm) editForm.action = data.action || '';
            if (editNama) editNama.value = data.nama || '';
            if (editNim) editNim.value = data.nim || '';
            if (editTahun) editTahun.value = data.tahun || '';
            if (editJenis) {
                editJenis.value = data.jenis || 'baru';
                if (!editJenis.value && data.jenis) {
                    for (let opt of editJenis.options) {
                        if (opt.value.toLowerCase() === data.jenis.toLowerCase()) {
                            opt.selected = true;
                            break;
                        }
                    }
                }
            }
            if (editKelulusan) editKelulusan.value = data.kelulusan || '';
            if (editMasaStudi) editMasaStudi.value = data.masaStudi || '48';
            if (editMasaTunggu) editMasaTunggu.value = data.masaTunggu || '';
            if (editTempatKerja) editTempatKerja.value = data.tempatKerja || '';

            showModal(editModal);
            setTimeout(() => editKelulusan?.focus(), 100);
        }

        document.querySelectorAll('.open-edit-lulusan-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                populateAndShowEdit({
                    action: this.getAttribute('data-action'),
                    nama: this.getAttribute('data-nama'),
                    nim: this.getAttribute('data-nim'),
                    tahun: this.getAttribute('data-tahun'),
                    jenis: this.getAttribute('data-jenis'),
                    kelulusan: this.getAttribute('data-kelulusan'),
                    masaStudi: this.getAttribute('data-masa-studi'),
                    masaTunggu: this.getAttribute('data-masa-tunggu'),
                    tempatKerja: this.getAttribute('data-tempat-kerja')
                });
            });
        });

        document.querySelectorAll('.close-lulusan-modal').forEach(btn => {
            btn.addEventListener('click', function () {
                hideModal(this.closest('[role="dialog"]'));
            });
        });

        if (editModal) {
            editModal.addEventListener('click', function (e) {
                if (e.target === editModal) hideModal(editModal);
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                hideModal(editModal);
            }
        });
    });
</script>

@include('lulusan.partials.edit-modal')
@endsection
