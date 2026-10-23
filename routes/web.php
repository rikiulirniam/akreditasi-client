<?php

use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AngkatanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\UppsController;
use App\Http\Controllers\UserController;
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
    
    Route::get('/tahun-akademik/{id}/kelas', [TahunAkademikController::class, 'daftarKelas'])->name('tahun-akademik.kelas');
    Route::get('/tahun-akademik/{id}/kelas/{kelasId}', [TahunAkademikController::class, 'detailKelas'])->name('tahun-akademik.detail-kelas');
    
    Route::get('/upps/{id}/anggota', [UppsController::class, 'anggota'])->name('upps.anggota');
    Route::post('/upps/{id}/anggota', [UppsController::class, 'storeAnggota'])->name('upps.anggota.store');
    Route::put('/upps/{id}/anggota/{anggotaId}', [UppsController::class, 'updateAnggota'])->name('upps.anggota.update');
    Route::delete('/upps/{id}/anggota/{anggotaId}', [UppsController::class, 'destroyAnggota'])->name('upps.anggota.destroy');

    Route::get('/kemahasiswaan/jenis-prestasi', [MahasiswaController::class, 'jenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.index');
    Route::post('/kemahasiswaan/jenis-prestasi', [MahasiswaController::class, 'storeJenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.store');
    Route::put('/kemahasiswaan/jenis-prestasi/{id}', [MahasiswaController::class, 'updateJenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.update');
    Route::delete('/kemahasiswaan/jenis-prestasi/{id}', [MahasiswaController::class, 'destroyJenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.destroy');
});
