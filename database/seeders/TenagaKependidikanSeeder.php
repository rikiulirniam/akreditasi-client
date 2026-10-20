<?php

namespace Database\Seeders;

use App\Models\TenagaKependidikan;
use Illuminate\Database\Seeder;

class TenagaKependidikanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Bambang Triyono, S.E.',
                'nip' => '197805122003121001',
                'jabatan' => 'Kepala Tata Usaha',
                'kualifikasi' => 'S1 Manajemen',
                'bidang_keahlian' => 'Manajemen Perkantoran',
                'program_studi' => 'S1 Manajemen',
                'email' => 'bambang.triyono@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Sri Wahyuni, A.Md.',
                'nip' => '198509202008012003',
                'jabatan' => 'Admin Akademik',
                'kualifikasi' => 'D3 Administrasi Perkantoran',
                'bidang_keahlian' => 'Sistem Informasi Akademik',
                'program_studi' => 'D3 Administrasi Perkantoran',
                'email' => 'sri.wahyuni@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Eko Prasetyo, S.Kom.',
                'nip' => '199103152014021002',
                'jabatan' => 'Laboran Komputer',
                'kualifikasi' => 'S1 Teknik Informatika',
                'bidang_keahlian' => 'Jaringan Komputer & Cloud',
                'program_studi' => 'S1 Teknik Informatika',
                'email' => 'eko.prasetyo@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Ratna Juwita, S.Sos.',
                'nip' => '198811042010122001',
                'jabatan' => 'Staf Kemahasiswaan',
                'kualifikasi' => 'S1 Administrasi Publik',
                'bidang_keahlian' => 'Kemahasiswaan & Alumni',
                'program_studi' => 'S1 Administrasi Publik',
                'email' => 'ratna.juwita@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Hendra Pratama, A.Md.T.',
                'nip' => '199307222019031005',
                'jabatan' => 'Teknisi Laboratorium',
                'kualifikasi' => 'D3 Teknik Elektronika',
                'bidang_keahlian' => 'Instrumentasi & Kalibrasi',
                'program_studi' => 'D3 Teknik Elektronika',
                'email' => 'hendra.pratama@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Nunung Suhartini, S.E.',
                'nip' => '198201302006042002',
                'jabatan' => 'Staf Keuangan',
                'kualifikasi' => 'S1 Akuntansi',
                'bidang_keahlian' => 'Akuntansi Sektor Publik',
                'program_studi' => 'S1 Akuntansi',
                'email' => 'nunung.suhartini@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Arif Budiman, S.I.Pust.',
                'nip' => '198908102015031003',
                'jabatan' => 'Pustakawan',
                'kualifikasi' => 'S1 Ilmu Perpustakaan',
                'bidang_keahlian' => 'Manajemen Repositori Digital',
                'program_studi' => 'S1 Ilmu Perpustakaan',
                'email' => 'arif.budiman@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Rina Susanti, A.Md.',
                'nip' => '199504182020122004',
                'jabatan' => 'Admin Prodi',
                'kualifikasi' => 'D3 Administrasi',
                'bidang_keahlian' => 'Administrasi Akreditasi',
                'program_studi' => 'D3 Administrasi',
                'email' => 'rina.susanti@polines.ac.id',
                'status' => 'Aktif',
            ],
            [
                'nama' => 'Joko Supriyanto',
                'nip' => '197602052005011001',
                'jabatan' => 'Pengelola Sarana Prasarana',
                'kualifikasi' => 'D3 Manajemen Aset',
                'bidang_keahlian' => 'Inventaris & Logistik',
                'program_studi' => 'D3 Manajemen Aset',
                'email' => 'joko.supriyanto@polines.ac.id',
                'status' => 'Aktif',
            ],
        ];

        foreach ($data as $item) {
            $tendik = TenagaKependidikan::updateOrCreate(
                ['nip' => $item['nip']],
                $item
            );

            // Populate sample kualifikasi if empty
            if ($tendik->kualifikasis()->count() === 0) {
                $tendik->kualifikasis()->createMany([
                    [
                        'gelar' => 'Dr',
                        'jenjang' => 'S3',
                        'institusi' => 'Politeknik Negeri Semarang',
                        'program_studi' => 'Teknik Informatika',
                        'tahun_lulus' => '2026',
                    ],
                    [
                        'gelar' => 'M.Kom',
                        'jenjang' => 'S2',
                        'institusi' => 'Universitas Diponegoro',
                        'program_studi' => 'Sistem Informasi',
                        'tahun_lulus' => '2018',
                    ],
                    [
                        'gelar' => 'S.Kom',
                        'jenjang' => 'S1',
                        'institusi' => 'Universitas Dian Nuswantoro',
                        'program_studi' => 'Teknik Informatika',
                        'tahun_lulus' => '2014',
                    ],
                ]);
            }
        }
    }
}
