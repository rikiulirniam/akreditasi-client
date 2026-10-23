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

    // ==========================================
    // MODUL PERGURUAN TINGGI & KELEMBAGAAN
    // ==========================================
    Route::middleware('permission:perguruan_tinggi.read')->get('/perguruan-tinggi', [PerguruanTinggiController::class, 'index'])->name('perguruan-tinggi.index');
    Route::middleware('permission:perguruan_tinggi.update')->put('/perguruan-tinggi/{id}', [PerguruanTinggiController::class, 'update'])->name('perguruan-tinggi.update');
    Route::middleware('permission:prodi')->resource('prodi', ProdiController::class);
    Route::middleware('permission:upps')->resource('upps', UppsController::class);

    // ==========================================
    // MODUL PENGGUNA, PERAN, & HAK AKSES
    // ==========================================
    Route::middleware('permission:user')->resource('users', UserController::class);
    Route::middleware('permission:role')->resource('roles', RoleController::class)->except(['show']);
    Route::middleware('permission:permission.update')->put('/permissions/module/{module}', [PermissionController::class, 'updateModule'])->name('permissions.update-module');
    Route::middleware('permission:permission.delete')->delete('/permissions/module/{module}', [PermissionController::class, 'destroyModule'])->name('permissions.destroy-module');
    Route::middleware('permission:permission')->resource('permissions', PermissionController::class)->except(['show']);

    // ==========================================
    // MODUL KEMAHASISWAAN & KELAS
    // ==========================================
    Route::middleware('permission:angkatan')->resource('angkatan', AngkatanController::class);
    Route::middleware('permission:angkatan_kelas')->resource('angkatan-kelas', AngkatanKelasController::class)->only(['store', 'update', 'destroy']);
    Route::middleware('permission:kelas')->resource('kelas', KelasController::class);
    Route::middleware('permission:mahasiswa')->resource('mahasiswa', MahasiswaController::class);

    // ==========================================
    // MODUL AKADEMIK, KURIKULUM, & MATA KULIAH
    // ==========================================
    Route::middleware('permission:mata_kuliah')->resource('mata-kuliah', MataKuliahController::class);
    Route::middleware('permission:mata_kuliah.update,enrollment_mata_kuliah.update')->put('/mata-kuliah-enrollment/{enrollmentId}/evidence', [MataKuliahController::class, 'updateEvidence'])->name('mata-kuliah.update-evidence');

    Route::middleware('permission:tahun_akademik')->resource('tahun-akademik', TahunAkademikController::class);
    Route::middleware('permission:tahun_akademik.read')->get('/tahun-akademik/{id}/kelas', [TahunAkademikController::class, 'daftarKelas'])->name('tahun-akademik.kelas');
    Route::middleware('permission:kelas.read')->get('/tahun-akademik/{id}/kelas/{kelasId}', [TahunAkademikController::class, 'detailKelas'])->name('tahun-akademik.detail-kelas');

    Route::middleware('permission:kurikulum')->resource('kurikulum', KurikulumController::class);
    Route::middleware('permission:kurikulum.update')->put('kurikulum/{kurikulum}/cpl-mata-kuliah', [KurikulumController::class, 'updateMataKuliahCpl'])->name('kurikulum.update-mk-cpl');
});
