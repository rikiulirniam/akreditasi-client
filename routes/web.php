<?php

use App\Http\Controllers\Akreditasi\Superadmin\BuktiDokumenController;
use App\Http\Controllers\Akreditasi\Superadmin\DashboardController as SuperadminDashboard;
use App\Http\Controllers\Akreditasi\Superadmin\IndikatorController;
use App\Http\Controllers\Akreditasi\Superadmin\InstrumenController;
use App\Http\Controllers\Akreditasi\Superadmin\KriteriaController;
use App\Http\Controllers\Akreditasi\Superadmin\LembagaAkreditasiController;
use App\Http\Controllers\Akreditasi\Superadmin\LembarKerjaController;
use App\Http\Controllers\Akreditasi\Superadmin\RubrikController;
use App\Http\Controllers\Akreditasi\Superadmin\StandarSpmiController;
use App\Http\Controllers\Akreditasi\Superadmin\SubkriteriaController;
use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AngkatanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\LaporanAkhirController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SpmiController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\UppsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidasiMonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (session()->has('access_token')) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware('auth.akademik')->group(function () {
    Route::post('/auth/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reviewer/dashboard', [ReviewerController::class, 'dashboard'])->name('reviewer.dashboard');
    Route::get('/reviewer/penilaian', [ReviewerController::class, 'assessments'])->name('reviewer.assessments');
    Route::get('/reviewer/penilaian/{projectId}', [ReviewerController::class, 'assessmentDetail'])->name('reviewer.assessment-detail');
    Route::post('/reviewer/penilaian/{projectId}/indikator/{indicatorId}', [ReviewerController::class, 'saveAssessment'])->name('reviewer.assessment-save');

    Route::prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/dashboard', [KaprodiController::class, 'dashboard'])->name('dashboard');
        Route::get('/project', [KaprodiController::class, 'project'])->name('project');
        Route::post('/project', [KaprodiController::class, 'storeProject'])->name('project.store');
        Route::delete('/project/{id}', [KaprodiController::class, 'destroyProject'])->name('project.destroy');
        Route::get('/project/detail/{id?}', [KaprodiController::class, 'projectDetail'])->name('project.detail');

        Route::get('/plotting', [KaprodiController::class, 'plotting'])->name('plotting');
    });

    Route::get('/perguruan-tinggi', [PerguruanTinggiController::class, 'index'])->name('perguruan-tinggi.index');
    Route::put('/perguruan-tinggi/{id}', [PerguruanTinggiController::class, 'update'])->name('perguruan-tinggi.update');

    Route::resource('prodi', ProdiController::class);
    Route::resource('upps', UppsController::class);

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::put('/permissions/module/{module}', [PermissionController::class, 'updateModule'])->name('permissions.update-module');
    Route::delete('/permissions/module/{module}', [PermissionController::class, 'destroyModule'])->name('permissions.destroy-module');
    Route::resource('permissions', PermissionController::class)->except(['show']);
    Route::resource('angkatan', AngkatanController::class);
    Route::resource('angkatan-kelas', AngkatanKelasController::class)->only(['store', 'update', 'destroy']);

    Route::resource('kelas', KelasController::class);
    Route::resource('mahasiswa', MahasiswaController::class);
    Route::put('/mata-kuliah-enrollment/{enrollmentId}/evidence', [MataKuliahController::class, 'updateEvidence'])->name('mata-kuliah.update-evidence');
    Route::resource('mata-kuliah', MataKuliahController::class);
    Route::resource('tahun-akademik', TahunAkademikController::class);

    Route::resource('kurikulum', KurikulumController::class);
    Route::put('kurikulum/{kurikulum}/cpl-mata-kuliah', [KurikulumController::class, 'updateMataKuliahCpl'])->name('kurikulum.update-mk-cpl');

    Route::get('/validasi-monitoring', [ValidasiMonitoringController::class, 'index'])->name('validasi-monitoring.index');
    Route::post('/validasi-monitoring/status', [ValidasiMonitoringController::class, 'updateStatus'])->name('validasi-monitoring.update-status');

    Route::get('/spmi/tindak-lanjut', [SpmiController::class, 'tindakLanjut'])->name('spmi.tindak-lanjut');
    Route::post('/spmi/tindak-lanjut', [SpmiController::class, 'storeTindakLanjut'])->name('spmi.tindak-lanjut.store');

    Route::get('/laporan-akhir', [LaporanAkhirController::class, 'index'])->name('laporan-akhir.index');
    Route::get('/laporan-akhir/download', [LaporanAkhirController::class, 'download'])->name('laporan-akhir.download');
});

// =====================================================================
// ROUTE MODUL AKREDITASI (SUPERADMIN)
// =====================================================================

Route::prefix('pages')->name('akreditasi.superadmin.')->group(function () {
    Route::get('/superadmin', [SuperadminDashboard::class, 'index'])->name('dashboard');
    Route::get('/superadmin/dashboard', [SuperadminDashboard::class, 'index']);

    // Master Data Akreditasi - Level 1: Lembaga
    Route::get('/superadmin/lembaga-akreditasi', [LembagaAkreditasiController::class, 'index'])->name('lembaga_akreditasi');
    Route::post('/superadmin/lembaga-akreditasi', [LembagaAkreditasiController::class, 'store'])->name('lembaga_akreditasi.store');
    Route::put('/superadmin/lembaga-akreditasi/{id}', [LembagaAkreditasiController::class, 'update'])->name('lembaga_akreditasi.update');
    Route::delete('/superadmin/lembaga-akreditasi/{id}', [LembagaAkreditasiController::class, 'destroy'])->name('lembaga_akreditasi.destroy');

    // Master Data Akreditasi - Level 2: Detail Lembaga -> Daftar Instrumen
    Route::get('/superadmin/lembaga-akreditasi/{lembagaId}', [InstrumenController::class, 'index'])->name('instrumen');
    Route::post('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen', [InstrumenController::class, 'store'])->name('instrumen.store');
    Route::put('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}', [InstrumenController::class, 'update'])->name('instrumen.update');
    Route::delete('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}', [InstrumenController::class, 'destroy'])->name('instrumen.destroy');
    Route::get('/superadmin/instrumen', function () {
        return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
    });

    // Master Data Akreditasi - Level 3: Detail Instrumen -> Daftar Kriteria
    Route::get('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}', [KriteriaController::class, 'index'])->name('kriteria');
    Route::post('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria', [KriteriaController::class, 'store'])->name('kriteria.store');
    Route::put('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}', [KriteriaController::class, 'update'])->name('kriteria.update');
    Route::delete('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}', [KriteriaController::class, 'destroy'])->name('kriteria.destroy');
    Route::get('/superadmin/kriteria', function () {
        return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
    });

    // Master Data Akreditasi - Level 4: Detail Kriteria -> Daftar Subkriteria
    Route::get('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}', [SubkriteriaController::class, 'index'])->name('subkriteria');
    Route::post('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria', [SubkriteriaController::class, 'store'])->name('subkriteria.store');
    Route::put('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}', [SubkriteriaController::class, 'update'])->name('subkriteria.update');
    Route::delete('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}', [SubkriteriaController::class, 'destroy'])->name('subkriteria.destroy');
    Route::get('/superadmin/subkriteria', function () {
        return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
    });

    // Master Data Akreditasi - Level 5: Detail Subkriteria -> Daftar Indikator
    Route::get('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}', [IndikatorController::class, 'index'])->name('indikator');
    Route::post('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator', [IndikatorController::class, 'store'])->name('indikator.store');
    Route::put('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator/{indikatorId}', [IndikatorController::class, 'update'])->name('indikator.update');
    Route::delete('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator/{indikatorId}', [IndikatorController::class, 'destroy'])->name('indikator.destroy');
    Route::get('/superadmin/indikator', function () {
        return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
    });

    // Master Data Akreditasi - Level 6: Detail Indikator -> Daftar Rubrik
    Route::get('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator/{indikatorId}', [RubrikController::class, 'index'])->name('rubrik');
    Route::post('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator/{indikatorId}/rubrik', [RubrikController::class, 'store'])->name('rubrik.store');
    Route::put('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator/{indikatorId}/rubrik/{rubrikId}', [RubrikController::class, 'update'])->name('rubrik.update');
    Route::delete('/superadmin/lembaga-akreditasi/{lembagaId}/instrumen/{instrumenId}/kriteria/{kriteriaId}/subkriteria/{subkriteriaId}/indikator/{indikatorId}/rubrik/{rubrikId}', [RubrikController::class, 'destroy'])->name('rubrik.destroy');
    Route::get('/superadmin/rubrik', function () {
        return redirect()->route('akreditasi.superadmin.lembaga_akreditasi');
    });

    Route::get('/superadmin/standar-spmi', [StandarSpmiController::class, 'index'])->name('standar_spmi');
    Route::post('/superadmin/standar-spmi', [StandarSpmiController::class, 'store'])->name('standar_spmi.store');

    Route::get('/superadmin/lembar-kerja', [LembarKerjaController::class, 'index'])->name('lembar_kerja');
    Route::get('/superadmin/bukti-dokumen', [BuktiDokumenController::class, 'index'])->name('bukti_dokumen');
});
Route::get('/superadmin', [SuperadminDashboard::class, 'index']);
