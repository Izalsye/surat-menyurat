<?php

namespace Database\Seeders;

use App\Models\LetterCategory;
use Illuminate\Database\Seeder;

class LetterCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $letterCategories = [
            [
                'code' => '000',
                'name' => 'Umum',
                'description' => 'Surat menyangkut kegiatan umum seperti pengumuman, edaran, dan surat undangan umum.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '010',
                'name' => 'Kepegawaian',
                'description' => 'Surat tentang pengangkatan, mutasi, cuti, pensiun, dan urusan personalia lainnya.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '020',
                'name' => 'Keuangan',
                'description' => 'Surat mengenai anggaran, laporan keuangan, pembayaran, dan pengelolaan dana.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '030',
                'name' => 'Organisasi dan Tata Laksana',
                'description' => 'Surat terkait struktur organisasi, pembentukan panitia, SOP, dan tata kelola.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '040',
                'name' => 'Hukum dan Peraturan',
                'description' => 'Surat hukum, perjanjian, peraturan perundang-undangan, dan dokumen legal lainnya.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '050',
                'name' => 'Pendidikan dan Pelatihan',
                'description' => 'Surat pelatihan, seminar, workshop, diklat, dan peningkatan kapasitas SDM.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '060',
                'name' => 'Fasilitas dan Aset',
                'description' => 'Surat terkait inventarisasi, pengadaan barang/jasa, dan pemeliharaan aset.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '070',
                'name' => 'Hubungan Masyarakat',
                'description' => 'Surat untuk komunikasi eksternal, kehumasan, kerjasama, dan publikasi.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '080',
                'name' => 'Pengawasan dan Evaluasi',
                'description' => 'Surat audit, pemantauan kinerja, dan evaluasi program/kegiatan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '090',
                'name' => 'Teknologi Informasi',
                'description' => 'Surat terkait sistem informasi, teknologi, keamanan data, dan pengadaan IT.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '100',
                'name' => 'Laporan',
                'description' => 'Laporan berkala, laporan kegiatan, monitoring, dan dokumentasi hasil kerja.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '110',
                'name' => 'Perjalanan Dinas',
                'description' => 'Surat tugas, surat perintah perjalanan dinas (SPPD), dan dokumen pendukung.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => '120',
                'name' => 'Surat Pribadi / Non-dinas',
                'description' => 'Surat pribadi atau informal yang tidak terkait langsung dengan kegiatan kedinasan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        LetterCategory::query()->insert($letterCategories);
    }
}
