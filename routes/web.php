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
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KaprodiController;
use App\Http\Controllers\KegiatanMahasiswaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\LaporanAkhirController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\NilaiMahasiswaController;
use App\Http\Controllers\PendidikanController;
use App\Http\Controllers\PerguruanTinggiController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PrestasiMahasiswaController;
use App\Http\Controllers\ProdiController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\SpmiController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\TenagaKependidikanController;
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
    Route::post('/tenaga-kependidikan/{tendikId}/sertifikasi', [TenagaKependidikanController::class, 'storeSertifikasi'])->name('tenaga-kependidikan.sertifikasi.store');
    Route::delete('/tenaga-kependidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'destroySertifikasi'])->name('tenaga-kependidikan.sertifikasi.destroy');
    Route::get('/tenaga-kependidikan/{tendikId}/sertifikasi', function ($tendikId) {
        return redirect()->route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => $tendikId]);
    })->name('tenaga-kependidikan.sertifikasi.index');
    Route::get('/tenaga-pendidikan/{tendikId}/sertifikasi', function ($tendikId) {
        return redirect()->route('sertifikasi.index', ['tipe_pemilik' => 'tendik', 'id_tendik' => $tendikId]);
    })->name('tenaga-pendidikan.sertifikasi.index');
    Route::get('/tenaga-kependidikan/{tendikId}/pendidikan', function ($tendikId) {
        return redirect()->route('pendidikan.index', ['id_tendik' => $tendikId]);
    })->name('tenaga-kependidikan.pendidikan.index');
    Route::get('/tenaga-pendidikan/{tendikId}/pendidikan', function ($tendikId) {
        return redirect()->route('pendidikan.index', ['id_tendik' => $tendikId]);
    })->name('tenaga-pendidikan.pendidikan.index');
    Route::post('/tenaga-kependidikan/{tendikId}/pendidikan', [TenagaKependidikanController::class, 'storePendidikan'])->name('tenaga-kependidikan.pendidikan.store');
    Route::post('/tenaga-pendidikan/{tendikId}/pendidikan', [TenagaKependidikanController::class, 'storePendidikan'])->name('tenaga-pendidikan.pendidikan.store');

    // Modul Pendidikan
    Route::resource('pendidikan', PendidikanController::class);

    // ==========================================
    // MODUL VALIDASI, SPMI & LAPORAN AKHIR
    // ==========================================
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
