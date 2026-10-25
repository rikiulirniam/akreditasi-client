<?php

use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AngkatanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\LaporanAkhirController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SpmiController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\UppsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ValidasiMonitoringController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Akreditasi\Superadmin\DashboardController as SuperadminDashboard;

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

});

// =====================================================================
// ROUTE MODUL AKREDITASI (SUPERADMIN)
// =====================================================================

Route::prefix('pages')->name('akreditasi.superadmin.')->group(function () {
    Route::get('/superadmin', [SuperadminDashboard::class, 'index'])->name('dashboard');
    Route::get('/superadmin/dashboard', [SuperadminDashboard::class, 'index'])->name('dashboard');
    
    // Tambahkan baris ini untuk mengatasi error instrumen
    Route::get('/superadmin/instrumen', function () {
        return view('pages.superadmin.instrumen');
    })->name('instrumen');

    Route::get('/superadmin/lembaga-akreditasi', function () {
        return view('pages.superadmin.lembaga-akreditasi');
    })->name('lembaga_akreditasi');

    Route::get('/superadmin/kriteria', function () {
        return view('pages.superadmin.kriteria');
    })->name('kriteria');

    Route::get('/superadmin/subkriteria', function () {
        return view('pages.superadmin.subkriteria');
    })->name('subkriteria');

    Route::get('/superadmin/indikator', function () {
        return view('pages.superadmin.indikator');
    })->name('indikator');

    Route::get('/superadmin/rubrik', function () {
        return view('pages.superadmin.rubrik');
    })->name('rubrik');

    Route::get('/superadmin/bobot', function () {
        return view('pages.superadmin.bobot');
    })->name('bobot');

    Route::get('/superadmin/detail-bobot', function () {
        return view('pages.superadmin.detail-bobot');
    })->name('detail_bobot');

    Route::get('/superadmin/standar-spmi', function () {
        return view('pages.superadmin.standar-spmi');
    })->name('standar_spmi');

    Route::get('/superadmin/lembar-kerja', function () {
        return view('pages.superadmin.lembar-kerja');
    })->name('lembar_kerja');

    Route::get('/superadmin/bukti-dokumen', function () {
        return view('pages.superadmin.bukti-dokumen');
    })->name('bukti_dokumen');
});
Route::get('/superadmin', [SuperadminDashboard::class, 'index']);