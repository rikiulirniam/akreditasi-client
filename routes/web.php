<?php

use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AngkatanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CplController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KegiatanMahasiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrestasiMahasiswaController;
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
    Route::get('/upps/{id}/anggota', [UppsController::class, 'anggota'])->name('upps.anggota');
    Route::post('/upps/{id}/anggota', [UppsController::class, 'storeAnggota'])->name('upps.anggota.store');
    Route::put('/upps/{id}/anggota/{anggotaId}', [UppsController::class, 'updateAnggota'])->name('upps.anggota.update');
    Route::delete('/upps/{id}/anggota/{anggotaId}', [UppsController::class, 'destroyAnggota'])->name('upps.anggota.destroy');

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
    Route::middleware('permission:prestasi_mahasiswa')->resource('prestasi-mahasiswa', PrestasiMahasiswaController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::middleware('permission:evidence_prestasi_mahasiswa.create')->post('/prestasi-mahasiswa/{prestasiId}/evidence', [PrestasiMahasiswaController::class, 'storeEvidence'])->name('prestasi-mahasiswa.evidence.store');
    Route::middleware('permission:evidence_prestasi_mahasiswa.update')->put('/prestasi-mahasiswa/{prestasiId}/evidence/{evidenceId}', [PrestasiMahasiswaController::class, 'updateEvidence'])->name('prestasi-mahasiswa.evidence.update');
    Route::middleware('permission:evidence_prestasi_mahasiswa.delete')->delete('/prestasi-mahasiswa/{prestasiId}/evidence/{evidenceId}', [PrestasiMahasiswaController::class, 'destroyEvidence'])->name('prestasi-mahasiswa.evidence.destroy');
    Route::middleware('permission:jenis_prestasi.create')->post('/prestasi-mahasiswa/{prestasiId}/jenis', [PrestasiMahasiswaController::class, 'storeJenis'])->name('prestasi-mahasiswa.jenis.store');
    Route::middleware('permission:jenis_prestasi.update')->put('/prestasi-mahasiswa/{prestasiId}/jenis/{jenisId}', [PrestasiMahasiswaController::class, 'updateJenis'])->name('prestasi-mahasiswa.jenis.update');
    Route::middleware('permission:jenis_prestasi.delete')->delete('/prestasi-mahasiswa/{prestasiId}/jenis/{jenisId}', [PrestasiMahasiswaController::class, 'destroyJenis'])->name('prestasi-mahasiswa.jenis.destroy');
    Route::post('/mahasiswa/{mahasiswa}/status', [MahasiswaController::class, 'storeStatus'])->name('mahasiswa.status.store');
    Route::put('/mahasiswa/{mahasiswa}/status/{statusId}', [MahasiswaController::class, 'updateStatus'])->name('mahasiswa.status.update');
    Route::delete('/mahasiswa/{mahasiswa}/status/{statusId}', [MahasiswaController::class, 'destroyStatus'])->name('mahasiswa.status.destroy');
    Route::resource('kegiatan-mahasiswa', KegiatanMahasiswaController::class);

    Route::get('/kemahasiswaan/jenis-prestasi', [MahasiswaController::class, 'jenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.index');
    Route::post('/kemahasiswaan/jenis-prestasi', [MahasiswaController::class, 'storeJenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.store');
    Route::put('/kemahasiswaan/jenis-prestasi/{id}', [MahasiswaController::class, 'updateJenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.update');
    Route::delete('/kemahasiswaan/jenis-prestasi/{id}', [MahasiswaController::class, 'destroyJenisPrestasi'])->name('kemahasiswaan.jenis-prestasi.destroy');

    // ==========================================
    // MODUL AKADEMIK, KURIKULUM, & MATA KULIAH
    // ==========================================
    Route::middleware('permission:mata_kuliah')->resource('mata-kuliah', MataKuliahController::class);
    Route::middleware('permission:mata_kuliah.update,enrollment_mata_kuliah.update')->put('/mata-kuliah-enrollment/{enrollmentId}/evidence', [MataKuliahController::class, 'updateEvidence'])->name('mata-kuliah.update-evidence');
    Route::middleware('permission:mata_kuliah.update,enrollment_mata_kuliah.update')->patch('/mata-kuliah/{mataKuliahId}/kelas/{angkatanKelasId}/link-nilai', [MataKuliahController::class, 'updateNilaiLink'])->name('mata-kuliah.update-grade-link');
    Route::middleware('permission:mata_kuliah.update,enrollment_mata_kuliah.update')->patch('/mata-kuliah/{mataKuliahId}/kelas/{angkatanKelasId}/link-evidence', [MataKuliahController::class, 'updateEvidenceLink'])->name('mata-kuliah.update-grade-evidence');

    Route::middleware('permission:tahun_akademik')->resource('tahun-akademik', TahunAkademikController::class);
    Route::middleware('permission:tahun_akademik.read')->get('/tahun-akademik/{id}/kelas', [TahunAkademikController::class, 'daftarKelas'])->name('tahun-akademik.kelas');
    Route::middleware('permission:kelas.read')->get('/tahun-akademik/{id}/kelas/{kelasId}', [TahunAkademikController::class, 'detailKelas'])->name('tahun-akademik.detail-kelas');

    Route::middleware('permission:kurikulum')->resource('kurikulum', KurikulumController::class);
    Route::middleware('permission:kurikulum.update')->put('kurikulum/{kurikulum}/cpl-mata-kuliah', [KurikulumController::class, 'updateMataKuliahCpl'])->name('kurikulum.update-mk-cpl');
    Route::middleware('permission:cpl')->resource('cpl', CplController::class);

    // ==========================================
    // MODUL NILAI MAHASISWA & AKADEMIK
    // ==========================================
    Route::middleware('permission:nilai_mahasiswa.read')->get('/nilai-mahasiswa', [NilaiMahasiswaController::class, 'index'])->name('nilai-mahasiswa.index');
    Route::middleware('permission:nilai_mahasiswa.read')->get('/nilai-mahasiswa/template', [NilaiMahasiswaController::class, 'downloadTemplate'])->name('nilai-mahasiswa.template');
    Route::middleware('permission:nilai_mahasiswa.create')->post('/nilai-mahasiswa/import', [NilaiMahasiswaController::class, 'import'])->name('nilai-mahasiswa.import');
    Route::middleware('permission:nilai_mahasiswa.create')->post('/nilai-mahasiswa', [NilaiMahasiswaController::class, 'store'])->name('nilai-mahasiswa.store');
    Route::middleware('permission:nilai_mahasiswa.update')->put('/nilai-mahasiswa/{id}', [NilaiMahasiswaController::class, 'update'])->name('nilai-mahasiswa.update');
    Route::middleware('permission:nilai_mahasiswa.delete')->delete('/nilai-mahasiswa/{id}', [NilaiMahasiswaController::class, 'destroy'])->name('nilai-mahasiswa.destroy');
    Route::middleware('permission:nilai_mahasiswa.read')->get('/nilai-mahasiswa/khs/{mahasiswaId}', [NilaiMahasiswaController::class, 'khs'])->name('nilai-mahasiswa.khs');
    Route::middleware('permission:nilai_mahasiswa.read')->get('/nilai-mahasiswa/transkrip/{mahasiswaId}', [NilaiMahasiswaController::class, 'transkrip'])->name('nilai-mahasiswa.transkrip');
});
