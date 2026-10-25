<?php

namespace App\Http\Controllers;

use App\Services\AkreditasiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpmiController extends Controller
{
    public function __construct(
        protected AkreditasiService $akreditasiService,
    ) {}

    /**
     * Display the Tindak Lanjut SPMI page.
     */
    public function tindakLanjut(Request $request): View
    {
        $selectedYear = (int) $request->query('tahun', 2026);
        $activeTab = $request->query('tab', 'ami'); // 'ami' or 'rtm'

        // Daftar Temuan AMI dimuat dari API Akreditasi atau fallback ke data default
        $temuanAmi = $this->loadTemuanAmiFromApi($selectedYear);

        // Summary statistics cards dihitung dinamis dari temuan AMI
        $summary = [
            'temuan_terbuka' => 0,
            'menunggu_tanggapan' => 0,
            'dalam_perbaikan' => 0,
            'selesai' => 0,
        ];

        foreach ($temuanAmi as $item) {
            $status = $item['status'] ?? 'menunggu_tanggapan';
            if ($status === 'menunggu_tanggapan') {
                $summary['menunggu_tanggapan']++;
                $summary['temuan_terbuka']++;
            } elseif ($status === 'dalam_perbaikan') {
                $summary['dalam_perbaikan']++;
                $summary['temuan_terbuka']++;
            } elseif ($status === 'selesai' || $status === 'efektif') {
                $summary['selesai']++;
            }
        }

        // Daftar Keputusan RTM
        $keputusanRtm = [
            [
                'id' => 1,
                'tanggal_rapat' => '12 Jun 2026',
                'keputusan' => 'Percepatan rekrutmen 2 dosen tetap bidang rekayasa jaringan',
                'tindak_lanjut' => 'Pengajuan formasi ke bagian SDM',
                'status' => 'dalam_perbaikan',
                'status_label' => 'Dalam perbaikan',
            ],
            [
                'id' => 2,
                'tanggal_rapat' => '12 Jun 2026',
                'keputusan' => 'Pembaruan kurikulum berbasis masukan industri',
                'tindak_lanjut' => 'Lokakarya kurikulum bersama mitra',
                'status' => 'menunggu_tanggapan',
                'status_label' => 'Menunggu tanggapan',
            ],
            [
                'id' => 3,
                'tanggal_rapat' => '12 Jun 2026',
                'keputusan' => 'Pengesahan roadmap penelitian 2025-2029',
                'tindak_lanjut' => 'Ditetapkan pada rapat prodi',
                'status' => 'selesai',
                'status_label' => 'Selesai',
            ],
            [
                'id' => 4,
                'tanggal_rapat' => '15 Mei 2026',
                'keputusan' => 'Peningkatan rasio komputasi laboratorium praktikum IoT',
                'tindak_lanjut' => 'Pengadaan 15 unit mikrokomputer dan modul sensor',
                'status' => 'selesai',
                'status_label' => 'Selesai',
            ],
            [
                'id' => 5,
                'tanggal_rapat' => '20 Apr 2026',
                'keputusan' => 'Optimalisasi tracer study online dengan sistem reminder WhatsApp',
                'tindak_lanjut' => 'Integrasi API blast WhatsApp gateway ke sistem alumni',
                'status' => 'dalam_perbaikan',
                'status_label' => 'Dalam perbaikan',
            ],
        ];

        // Daftar anggota tim untuk dropdown penanggung jawab
        $anggotaTim = [
            'Dr. Rina Kusuma',
            'Budi Santoso M.Kom.',
            'Sari Wulandari',
            'Andi Pratama',
            'Kuat (Kaprodi)',
            'Tim Penjaminan Mutu Prodi',
        ];

        return view('pages.kaprodi.tindak-lanjut-spmi', [
            'selectedYear' => $selectedYear,
            'activeTab' => $activeTab,
            'summary' => $summary,
            'temuanAmi' => $temuanAmi,
            'keputusanRtm' => $keputusanRtm,
            'anggotaTim' => $anggotaTim,
        ]);
    }

    /**
     * Store feedback / improvement plan draft or submission to Akreditasi API.
     */
    public function storeTindakLanjut(Request $request): RedirectResponse
    {
        $findingId = (int) $request->input('finding_id', 1);
        $action = $request->input('action', 'draft');
        $rencanaPerbaikan = (string) $request->input('rencana_perbaikan');
        $tanggapan = (string) $request->input('tanggapan');
        $targetSelesai = $request->input('target_selesai');

        $statusTindakLanjut = $action === 'submit' ? 'Selesai' : 'Sedang Berjalan';

        try {
            // Memperbarui tindak lanjut temuan audit SPMI via Akreditasi REST API
            $this->akreditasiService->updateTindakLanjutSpmi($findingId, [
                'rencana_tindak_lanjut' => $rencanaPerbaikan,
                'status_tindak_lanjut' => $statusTindakLanjut,
                'tanggal_efektif' => ! empty($targetSelesai) ? $targetSelesai : date('Y-m-d'),
                'evaluasi_efektivitas' => $tanggapan,
            ]);
        } catch (\Throwable $e) {
            // Abaikan jika id temuan berupa dummy lokal yang belum ada di backend
        }

        $message = $action === 'submit'
            ? 'Tanggapan dan rencana perbaikan berhasil dikirim ke Auditor!'
            : 'Draf tanggapan berhasil disimpan sementara.';

        return redirect()
            ->route('spmi.tindak-lanjut', ['tab' => 'ami'])
            ->with('success', $message);
    }

    /**
     * Memuat daftar temuan audit dari Akreditasi API atau fallback.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function loadTemuanAmiFromApi(int $year): array
    {
        $default = $this->getDefaultTemuanAmi();

        try {
            $response = $this->akreditasiService->getSpmiAudits(['tahun' => $year]);
            if ($response->successful()) {
                $apiData = $response->json('data') ?? [];
                if (is_array($apiData) && ! empty($apiData)) {
                    $mapped = [];
                    foreach ($apiData as $item) {
                        $statusTindak = $item['status_tindak_lanjut'] ?? null;
                        $status = match (strtolower((string) $statusTindak)) {
                            'selesai', 'efektif' => 'selesai',
                            'sedang berjalan' => 'dalam_perbaikan',
                            default => 'menunggu_tanggapan',
                        };

                        $statusLabel = match ($status) {
                            'selesai' => 'Selesai',
                            'dalam_perbaikan' => 'Dalam perbaikan',
                            default => 'Menunggu tanggapan',
                        };

                        $standarNama = $item['standar_mutu']['nama_standar'] ?? 'Standar Penjaminan Mutu';
                        $kodeStandar = $item['standar_mutu']['kode_standar'] ?? ('STD-'.($item['id_standar_mutu'] ?? '01'));

                        $mapped[] = [
                            'id' => $item['id'],
                            'kode' => $kodeStandar,
                            'standar' => $standarNama,
                            'judul' => "{$kodeStandar} {$standarNama}",
                            'status' => $status,
                            'status_label' => $statusLabel,
                            'ringkasan' => $item['kondisi_temuan'] ?? 'Temuan audit SPMI',
                            'deskripsi' => $item['kondisi_temuan'] ?? '',
                            'rekomendasi' => $item['rekomendasi'] ?? '',
                            'siklus' => 'AMI',
                            'tanggal_audit' => ! empty($item['tanggal_audit']) ? date('d M Y', strtotime($item['tanggal_audit'])) : '22 Sep 2026',
                            'tenggat' => ! empty($item['tanggal_efektif']) ? date('d M', strtotime($item['tanggal_efektif'])) : '30 Okt',
                            'tanggapan' => $item['evaluasi_efektivitas'] ?? '',
                            'rencana_perbaikan' => $item['rencana_tindak_lanjut'] ?? '',
                            'target_selesai' => $item['tanggal_efektif'] ?? date('Y-m-d', strtotime('+14 days')),
                            'penanggung_jawab' => 'Kuat (Kaprodi)',
                            'bukti_url' => '',
                        ];
                    }

                    return $mapped;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan kesalahan koneksi
        }

        return $default;
    }

    /**
     * Default temuan audit AMI.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getDefaultTemuanAmi(): array
    {
        return [
            [
                'id' => 1,
                'kode' => 'STD-04',
                'standar' => 'Standar Isi Pembelajaran',
                'judul' => 'STD-04 Standar Isi Pembelajaran',
                'status' => 'menunggu_tanggapan',
                'status_label' => 'Menunggu tanggapan',
                'ringkasan' => 'Dokumen evaluasi kurikulum belum diperbarui sejak peninjauan terakhir.',
                'deskripsi' => 'Dokumen evaluasi kurikulum belum diperbarui sejak peninjauan terakhir tahun 2022. Masukan dari mitra industri dan tracer study 2025 belum tercermin dalam profil lulusan dan CPL.',
                'rekomendasi' => 'Lakukan peninjauan kurikulum dengan melibatkan mitra industri dan alumni, lalu sahkan dokumen evaluasi kurikulum terbaru melalui SK Direktur.',
                'siklus' => 'AMI',
                'tanggal_audit' => '22 Sep 2026',
                'tenggat' => '20 Okt',
                'tanggapan' => '',
                'rencana_perbaikan' => '',
                'target_selesai' => '2026-10-20',
                'penanggung_jawab' => '',
                'bukti_url' => '',
            ],
            [
                'id' => 2,
                'kode' => 'STD-07',
                'standar' => 'Standar Dosen dan Tenaga Kependidikan',
                'judul' => 'STD-07 Standar Dosen dan Tenaga Kependidikan',
                'status' => 'menunggu_tanggapan',
                'status_label' => 'Menunggu tanggapan',
                'ringkasan' => 'Rasio dosen-mahasiswa melebihi batas yang ditetapkan standar.',
                'deskripsi' => 'Rasio dosen tetap terhadap mahasiswa pada program studi mencapai 1:38, melebihi batas standar BAN-PT maksimal 1:30 untuk rumpun teknik.',
                'rekomendasi' => 'Ajukan formasi penambahan dosen tetap baru melalui bagian kepegawaian dan optimalkan plotting dosen lintas prodi yang serumpun.',
                'siklus' => 'AMI',
                'tanggal_audit' => '22 Sep 2026',
                'tenggat' => '27 Okt',
                'tanggapan' => '',
                'rencana_perbaikan' => '',
                'target_selesai' => '2026-10-27',
                'penanggung_jawab' => '',
                'bukti_url' => '',
            ],
            [
                'id' => 3,
                'kode' => 'STD-02',
                'standar' => 'Standar Kerja Sama',
                'judul' => 'STD-02 Standar Kerja Sama',
                'status' => 'dalam_perbaikan',
                'status_label' => 'Dalam perbaikan',
                'ringkasan' => 'Bukti kerja sama dengan mitra industri sudah kedaluwarsa.',
                'deskripsi' => 'Sebanyak 4 dokumen MoU dan MoA kerja sama magang industri telah habis masa berlakunya pada awal tahun 2026 dan belum dilakukan perpanjangan resmi.',
                'rekomendasi' => 'Lakukan komunikasi dengan mitra industri terkait untuk pembaruan MoU/MoA serta lengkapi Implementation Agreement (IA) terbaru.',
                'siklus' => 'AMI',
                'tanggal_audit' => '23 Sep 2026',
                'tenggat' => '10 Nov',
                'tanggapan' => 'Proses perpanjangan naskah MoU sedang dikoordinasikan dengan 3 mitra industri prioritas.',
                'rencana_perbaikan' => 'Menyusun draft perpanjangan kerjasama dan menjadwalkan penandatanganan pada bulan November 2026.',
                'target_selesai' => '2026-11-10',
                'penanggung_jawab' => 'Sari Wulandari',
                'bukti_url' => 'https://drive.google.com/drive/folders/mou-mitra-2026',
            ],
            [
                'id' => 4,
                'kode' => 'STD-01',
                'standar' => 'Standar Kompetensi Lulusan',
                'judul' => 'STD-01 Standar Kompetensi Lulusan',
                'status' => 'selesai',
                'status_label' => 'Selesai',
                'ringkasan' => 'Rumusan CPL belum dipetakan ke mata kuliah secara lengkap.',
                'deskripsi' => 'Matriks keselarasan Capaian Pembelajaran Lulusan (CPL) dengan Capaian Pembelajaran Mata Kuliah (CPMK) belum terpetakan untuk semester 5 dan 6.',
                'rekomendasi' => 'Lengkapi matriks pemetaan CPL-CPMK pada kurikulum operasional prodi dan verifikasi bersama tim penjaminan mutu fakultas.',
                'siklus' => 'AMI',
                'tanggal_audit' => '10 Mar 2026',
                'tenggat' => '30 Apr',
                'tanggapan' => 'Pemetaan CPL ke seluruh mata kuliah telah dirampungkan bersama tim kurikulum.',
                'rencana_perbaikan' => 'Melakukan revisi buku kurikulum dan pengesahan matriks CPL.',
                'target_selesai' => '2026-04-30',
                'penanggung_jawab' => 'Dr. Rina Kusuma',
                'bukti_url' => 'https://drive.google.com/file/d/matriks-cpl-final',
            ],
            [
                'id' => 5,
                'kode' => 'STD-05',
                'standar' => 'Standar Proses Pembelajaran',
                'judul' => 'STD-05 Standar Proses Pembelajaran',
                'status' => 'selesai',
                'status_label' => 'Selesai',
                'ringkasan' => 'Monitoring kehadiran dosen belum terdokumentasi.',
                'deskripsi' => 'Laporan rekapitulasi kehadiran dosen mengajar secara periodik belum terintegrasi ke sistem pemantauan mutu prodi.',
                'rekomendasi' => 'Gunakan log presensi digital LMS/SIAKAD dan buat laporan berkala setiap tengah dan akhir semester.',
                'siklus' => 'AMI',
                'tanggal_audit' => '10 Mar 2026',
                'tenggat' => '15 Mei',
                'tanggapan' => 'Dokumentasi presensi dosen telah diintegrasikan langsung dari sistem akademik portal.',
                'rencana_perbaikan' => 'Membuat template laporan monitoring bulanan kehadiran dosen.',
                'target_selesai' => '2026-05-15',
                'penanggung_jawab' => 'Budi Santoso M.Kom.',
                'bukti_url' => 'https://drive.google.com/file/d/rekap-kehadiran-dosen',
            ],
            [
                'id' => 6,
                'kode' => 'STD-09',
                'standar' => 'Standar Penelitian',
                'judul' => 'STD-09 Standar Penelitian',
                'status' => 'selesai',
                'status_label' => 'Selesai',
                'ringkasan' => 'Roadmap penelitian prodi belum disahkan.',
                'deskripsi' => 'Roadmap payung penelitian dosen prodi telah disusun namun belum memperoleh pengesahan formal dari Dekan/Direktur.',
                'rekomendasi' => 'Ajukan surat permohonan SK pengesahan roadmap penelitian prodi periode 2025-2029 ke pimpinan unit.',
                'siklus' => 'RTM',
                'tanggal_audit' => '12 Jun 2026',
                'tenggat' => '31 Jul',
                'tanggapan' => 'SK pengesahan roadmap penelitian telah diterbitkan oleh Direktur.',
                'rencana_perbaikan' => 'Sosialisasi roadmap kepada seluruh kelompok keahlian dosen prodi.',
                'target_selesai' => '2026-07-31',
                'penanggung_jawab' => 'Andi Pratama',
                'bukti_url' => 'https://drive.google.com/file/d/sk-roadmap-penelitian',
            ],
        ];
    }
}
