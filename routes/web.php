<?php

use App\Http\Controllers\AngkatanController;
use App\Http\Controllers\AngkatanKelasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KegiatanMahasiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\PendidikanController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrestasiMahasiswaController;
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

    // ==========================================
    // MODUL DOSEN & SDM
    // ==========================================
    Route::get('/dosen/template', [DosenController::class, 'template'])->name('dosen.template');
    Route::post('/dosen/import', [DosenController::class, 'import'])->name('dosen.import');
    Route::resource('dosen', DosenController::class);

    // Modul Sertifikasi
    Route::get('/sertifikasi', [SertifikasiController::class, 'index'])->name('sertifikasi.index');
    Route::get('/sertifikasi/create', [SertifikasiController::class, 'create'])->name('sertifikasi.create');
    Route::post('/sertifikasi', [SertifikasiController::class, 'store'])->name('sertifikasi.store');
    Route::get('/sertifikasi/{sertifikasi}/edit', [SertifikasiController::class, 'edit'])->whereNumber('sertifikasi')->name('sertifikasi.edit');
    Route::put('/sertifikasi/{sertifikasi}', [SertifikasiController::class, 'update'])->whereNumber('sertifikasi')->name('sertifikasi.update');
    Route::delete('/sertifikasi/{sertifikasi}', [SertifikasiController::class, 'destroy'])->whereNumber('sertifikasi')->name('sertifikasi.destroy');
    Route::get('/sertifikasi/{sertifikasi}', [SertifikasiController::class, 'show'])->whereNumber('sertifikasi')->name('sertifikasi.show');

    // Modul Tenaga Kependidikan
    Route::resource('tenaga-kependidikan', TenagaKependidikanController::class);
    Route::get('/tenaga-pendidikan', [TenagaKependidikanController::class, 'index'])->name('tenaga-pendidikan.index');
    Route::get('/tenaga-pendidikan/{tenaga_kependidikan}', [TenagaKependidikanController::class, 'show'])->name('tenaga-pendidikan.show');
    Route::post('/tenaga-pendidikan/{tendikId}/sertifikasi', [TenagaKependidikanController::class, 'storeSertifikasi'])->name('tenaga-pendidikan.sertifikasi.store');
    Route::put('/tenaga-pendidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'updateSertifikasi'])->name('tenaga-pendidikan.sertifikasi.update');
    Route::delete('/tenaga-pendidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'destroySertifikasi'])->name('tenaga-pendidikan.sertifikasi.destroy');
    Route::post('/tenaga-kependidikan/{tenaga_kependidikan}/sertifikasi', [TenagaKependidikanController::class, 'storeSertifikasi'])->name('tenaga-kependidikan.sertifikasi.store');
    Route::delete('/tenaga-kependidikan/{tenaga_kependidikan}/sertifikasi/{sertifikasi}', [TenagaKependidikanController::class, 'destroySertifikasi'])->name('tenaga-kependidikan.sertifikasi.destroy');

    // Modul Pendidikan
    Route::resource('pendidikan', PendidikanController::class);
});
