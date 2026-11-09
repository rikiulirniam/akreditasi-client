<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ValidasiMonitoringController extends Controller
{
    /**
     * Display the Validasi & Monitoring Formulir page.
     */
    public function index(Request $request): View
    {
        // Get indicators from session if available to persist status changes,
        // otherwise initialize with default dummy data
        $indikators = session('validasi_indikators', $this->getDefaultIndikators());

        // Calculate summary counts
        $summary = [
            'siap_ditinjau' => 0,
            'menunggu_validasi' => 0,
            'perlu_revisi' => 0,
            'belum_lengkap' => 0,
        ];

        foreach ($indikators as $ind) {
            if (isset($summary[$ind['status']])) {
                $summary[$ind['status']]++;
            }
        }

        $total = count($indikators);

        // Apply status filter if provided
        $activeFilter = $request->query('filter', 'semua');
        $filteredIndikators = $indikators;

        if ($activeFilter !== 'semua') {
            $filteredIndikators = array_values(
                array_filter($indikators, fn (array $ind): bool => $ind['status'] === $activeFilter)
            );
        }

        $view = view()->exists('pages.kaprodi.validasi-monitoring')
            ? 'pages.kaprodi.validasi-monitoring'
            : (view()->exists('pages.kaprodi.index') ? 'pages.kaprodi.index' : 'validasi-monitoring.index');

        return view($view, [
            'indikators' => $filteredIndikators,
            'allIndikators' => $indikators,
            'summary' => $summary,
            'total' => $total,
            'activeFilter' => $activeFilter,
        ]);
    }

    /**
     * Update the status of an indicator (Setujui / Minta Revisi).
     */
    public function updateStatus(Request $request): JsonResponse|RedirectResponse
    {
        $kode = (string) $request->input('kode');
        $status = (string) $request->input('status'); // 'siap_ditinjau' or 'perlu_revisi'
        $catatanRevisi = $request->input('catatan_revisi');

        $indikators = session('validasi_indikators', $this->getDefaultIndikators());

        foreach ($indikators as &$ind) {
            if ($ind['kode'] === $kode) {
                $ind['status'] = $status;
                if ($status === 'perlu_revisi') {
                    $ind['catatan_revisi'] = ! empty($catatanRevisi)
                        ? (string) $catatanRevisi
                        : 'Harap perbaiki dan lengkapi dokumen bukti pendukung.';
                } elseif ($status === 'siap_ditinjau') {
                    unset($ind['catatan_revisi']);
                }
                break;
            }
        }
        unset($ind);

        session(['validasi_indikators' => $indikators]);

        $message = $status === 'siap_ditinjau'
            ? "Indikator {$kode} berhasil disetujui (Siap Ditinjau)!"
            : "Permintaan revisi untuk indikator {$kode} berhasil dikirim!";

        $summary = [
            'siap_ditinjau' => 0,
            'menunggu_validasi' => 0,
            'perlu_revisi' => 0,
            'belum_lengkap' => 0,
        ];

        foreach ($indikators as $item) {
            if (isset($summary[$item['status']])) {
                $summary[$item['status']]++;
            }
        }

        $total = count($indikators);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'indikators' => $indikators,
                'summary' => $summary,
                'total' => $total,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Get default dummy indicator data.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getDefaultIndikators(): array
    {
        return [
            [
                'kode' => 'K1.1',
                'nama' => 'Kejelasan visi dan misi',
                'pic' => 'Dr. Rina Kusuma',
                'status' => 'menunggu_validasi',
                'narasi' => 'Visi program studi telah dirumuskan berdasarkan analisis kebutuhan industri dan perkembangan IPTEKS. Misi disusun untuk mendukung pencapaian visi melalui pendidikan, penelitian, dan pengabdian.',
                'bukti' => [
                    ['nama' => 'SK_Visi_Misi_2024.pdf', 'url' => '#'],
                    ['nama' => 'Notulen_Rapat_Perumusan.pdf', 'url' => '#'],
                ],
            ],
            [
                'kode' => 'K1.2',
                'nama' => 'Strategi pencapaian tujuan',
                'pic' => 'Dr. Rina Kusuma',
                'status' => 'perlu_revisi',
                'narasi' => 'Strategi pencapaian tujuan disusun melalui rencana strategis 5 tahun yang mencakup pengembangan kurikulum, peningkatan kompetensi dosen, dan perluasan kerjasama industri.',
                'bukti' => [
                    ['nama' => 'Renstra_2024_2029.pdf', 'url' => '#'],
                ],
                'catatan_revisi' => 'Dokumen renstra belum mencantumkan indikator kinerja utama (IKU). Harap ditambahkan beserta target capaian per tahun.',
            ],
            [
                'kode' => 'K2.1',
                'nama' => 'Sistem tata pamong',
                'pic' => 'Budi Santoso M.Kom.',
                'status' => 'siap_ditinjau',
                'narasi' => 'Sistem tata pamong telah menerapkan prinsip kredibilitas, transparansi, akuntabilitas, tanggung jawab, dan keadilan sesuai pedoman BAN-PT.',
                'bukti' => [
                    ['nama' => 'Struktur_Organisasi.pdf', 'url' => '#'],
                    ['nama' => 'SOP_Tata_Pamong.pdf', 'url' => '#'],
                    ['nama' => 'SK_Dekan_Struktur.pdf', 'url' => '#'],
                ],
            ],
            [
                'kode' => 'K2.2',
                'nama' => 'Kerja sama dengan industri',
                'pic' => 'Sari Wulandari',
                'status' => 'menunggu_validasi',
                'narasi' => 'Program studi telah menjalin kerjasama dengan 12 perusahaan industri dan 5 instansi pemerintah untuk pelaksanaan magang, penelitian bersama, dan pengabdian masyarakat.',
                'bukti' => [
                    ['nama' => 'MoU_Kerjasama_2024.pdf', 'url' => '#'],
                    ['nama' => 'Daftar_Mitra_Kerjasama.xlsx', 'url' => '#'],
                ],
            ],
            [
                'kode' => 'K3.1',
                'nama' => 'Seleksi mahasiswa baru',
                'pic' => 'Andi Pratama',
                'status' => 'belum_lengkap',
                'narasi' => null,
                'bukti' => [],
            ],
            [
                'kode' => 'K3.2',
                'nama' => 'Layanan kemahasiswaan',
                'pic' => 'Sari Wulandari',
                'status' => 'siap_ditinjau',
                'narasi' => 'Layanan kemahasiswaan mencakup bimbingan akademik, konseling, beasiswa, dan pengembangan minat bakat yang terstruktur dan terdokumentasi.',
                'bukti' => [
                    ['nama' => 'Panduan_Layanan_Mahasiswa.pdf', 'url' => '#'],
                    ['nama' => 'Laporan_Kegiatan_UKM.pdf', 'url' => '#'],
                ],
            ],
        ];
    }
}
