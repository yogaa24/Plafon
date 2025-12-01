<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class SubmissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user yang role nya 'sales'
        $salesUsers = User::where('role', 'sales')->pluck('id');

        // Jika tidak ada user sales, pakai 3 user pertama
        if ($salesUsers->isEmpty()) {
            $salesUsers = User::limit(3)->pluck('id');
        }

        $submissions = [
            [
                'kode' => 'SUB-2024-001',
                'nama' => 'Budi Santoso',
                'nama_kios' => 'Toko Maju Jaya',
                'alamat' => 'Jl. Merdeka No. 123, Jember, Jawa Timur',
                'plafon' => 50000000.00,
                'jumlah_buka_faktur' => 5,
                'komitmen_pembayaran' => 'Pembayaran dilakukan setiap tanggal 15 dan 30 setiap bulan dengan sistem transfer bank',
                'sales_id' => $salesUsers->first() ?? 1,
                'status' => 'pending',
                'current_level' => 1,
                'revision_note' => null,
                'rejection_note' => null,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'kode' => 'SUB-2024-002',
                'nama' => 'Siti Rahayu',
                'nama_kios' => 'Warung Berkah',
                'alamat' => 'Jl. Gajah Mada No. 45, Jember, Jawa Timur',
                'plafon' => 30000000.00,
                'jumlah_buka_faktur' => 3,
                'komitmen_pembayaran' => 'Pembayaran cash setiap minggu, maksimal tempo 7 hari',
                'sales_id' => $salesUsers->skip(1)->first() ?? 1,
                'status' => 'approved_1',
                'current_level' => 2,
                'revision_note' => null,
                'rejection_note' => null,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'kode' => 'SUB-2024-003',
                'nama' => 'Ahmad Fauzi',
                'nama_kios' => 'Toko Sejahtera',
                'alamat' => 'Jl. Sudirman No. 78, Jember, Jawa Timur',
                'plafon' => 75000000.00,
                'jumlah_buka_faktur' => 8,
                'komitmen_pembayaran' => 'Pembayaran dengan giro mundur jatuh tempo 30 hari',
                'sales_id' => $salesUsers->first() ?? 1,
                'status' => 'approved_2',
                'current_level' => 3,
                'revision_note' => null,
                'rejection_note' => null,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'kode' => 'SUB-2024-004',
                'nama' => 'Dewi Lestari',
                'nama_kios' => 'Kios Barokah',
                'alamat' => 'Jl. Diponegoro No. 56, Jember, Jawa Timur',
                'plafon' => 100000000.00,
                'jumlah_buka_faktur' => 10,
                'komitmen_pembayaran' => 'Sistem pembayaran kredit dengan cicilan 3 bulan',
                'sales_id' => $salesUsers->skip(2)->first() ?? 1,
                'status' => 'done',
                'current_level' => 4,
                'revision_note' => null,
                'rejection_note' => null,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'kode' => 'SUB-2024-005',
                'nama' => 'Eko Prasetyo',
                'nama_kios' => 'Toko Sumber Rejeki',
                'alamat' => 'Jl. Ahmad Yani No. 90, Jember, Jawa Timur',
                'plafon' => 25000000.00,
                'jumlah_buka_faktur' => 2,
                'komitmen_pembayaran' => 'Pembayaran tunai setiap pengambilan barang',
                'sales_id' => $salesUsers->first() ?? 1,
                'status' => 'revision',
                'current_level' => 1,
                'revision_note' => 'Mohon lengkapi dokumen NPWP dan KTP pemilik kios',
                'rejection_note' => null,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'kode' => 'SUB-2024-006',
                'nama' => 'Rina Wati',
                'nama_kios' => 'Warung Murah',
                'alamat' => 'Jl. Kartini No. 12, Jember, Jawa Timur',
                'plafon' => 15000000.00,
                'jumlah_buka_faktur' => 2,
                'komitmen_pembayaran' => 'Pembayaran setiap akhir bulan via transfer',
                'sales_id' => $salesUsers->skip(1)->first() ?? 1,
                'status' => 'rejected',
                'current_level' => 1,
                'revision_note' => null,
                'rejection_note' => 'Plafon terlalu kecil untuk jumlah faktur yang diminta. Riwayat pembayaran tidak konsisten.',
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(6),
            ],
            [
                'kode' => 'SUB-2024-007',
                'nama' => 'Hendra Gunawan',
                'nama_kios' => 'Toko Lancar Jaya',
                'alamat' => 'Jl. Pahlawan No. 34, Jember, Jawa Timur',
                'plafon' => 60000000.00,
                'jumlah_buka_faktur' => 6,
                'komitmen_pembayaran' => 'Pembayaran 50% di muka, sisanya tempo 14 hari',
                'sales_id' => $salesUsers->skip(2)->first() ?? 1,
                'status' => 'approved_3',
                'current_level' => 3,
                'revision_note' => null,
                'rejection_note' => null,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(18),
            ],
        ];

        DB::table('submissions')->insert($submissions);
    }
}