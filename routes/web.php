<?php

use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AngkatanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\TenagaKependidikanController;
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

    Route::resource('tenaga-kependidikan', TenagaKependidikanController::class);
    Route::get('/tenaga-pendidikan', [TenagaKependidikanController::class, 'index'])->name('tenaga-pendidikan.index');
    Route::get('/tenaga-pendidikan/{tenaga_kependidikan}', [TenagaKependidikanController::class, 'show'])->name('tenaga-pendidikan.show');
    Route::post('/tenaga-pendidikan/{tendikId}/sertifikasi', [TenagaKependidikanController::class, 'storeSertifikasi'])->name('tenaga-pendidikan.sertifikasi.store');
    Route::put('/tenaga-pendidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'updateSertifikasi'])->name('tenaga-pendidikan.sertifikasi.update');
    Route::delete('/tenaga-pendidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'destroySertifikasi'])->name('tenaga-pendidikan.sertifikasi.destroy');
    Route::post('/tenaga-kependidikan/{tendikId}/sertifikasi', [TenagaKependidikanController::class, 'storeSertifikasi'])->name('tenaga-kependidikan.sertifikasi.store');
    Route::delete('/tenaga-kependidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'destroySertifikasi'])->name('tenaga-kependidikan.sertifikasi.destroy');
    Route::post('/tenaga-kependidikan/{tendikId}/pendidikan', [TenagaKependidikanController::class, 'storePendidikan'])->name('tenaga-kependidikan.pendidikan.store');

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

    // Modul Dosen
    Route::get('/dosen/template', [DosenController::class, 'template'])->name('dosen.template');
    Route::post('/dosen/import', [DosenController::class, 'import'])->name('dosen.import');
    Route::resource('dosen', DosenController::class);

    // Modul Kurikulum
    Route::resource('kurikulum', KurikulumController::class);
    Route::put('kurikulum/{kurikulum}/cpl-mata-kuliah', [KurikulumController::class, 'updateMataKuliahCpl'])->name('kurikulum.update-mk-cpl');

    // Modul Sertifikasi
    Route::get('/sertifikasi', [SertifikasiController::class, 'index'])->name('sertifikasi.index');
    Route::get('/sertifikasi/create', [SertifikasiController::class, 'create'])->name('sertifikasi.create');
    Route::post('/sertifikasi', [SertifikasiController::class, 'store'])->name('sertifikasi.store');
    Route::get('/sertifikasi/{sertifikasi}/edit', [SertifikasiController::class, 'edit'])->whereNumber('sertifikasi')->name('sertifikasi.edit');
    Route::put('/sertifikasi/{sertifikasi}', [SertifikasiController::class, 'update'])->whereNumber('sertifikasi')->name('sertifikasi.update');
    Route::delete('/sertifikasi/{sertifikasi}', [SertifikasiController::class, 'destroy'])->whereNumber('sertifikasi')->name('sertifikasi.destroy');
    Route::get('/sertifikasi/{sertifikasi}', [SertifikasiController::class, 'show'])->whereNumber('sertifikasi')->name('sertifikasi.show');
});
