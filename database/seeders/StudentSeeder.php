<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Seeder ini hanya CONTOH untuk keperluan development/testing.
     * Untuk hari-H sesungguhnya, import Daftar Pemilih Tetap (DPT) asli
     * lewat file CSV/Excel sekolah — lihat panduan import di dokumentasi
     * (DOKUMENTASI-INSTALASI-MAINTENANCE.md, bagian "Menyiapkan DPT").
     */
    public function run(): void
    {
        $samples = [
            ['nis' => '2024001', 'name' => 'Andi Pratama', 'class' => 'XII IPA 1', 'unique_code' => 'A1B2'],
            ['nis' => '2024002', 'name' => 'Bunga Citra Lestari', 'class' => 'XII IPA 1', 'unique_code' => 'C3D4'],
            ['nis' => '2024003', 'name' => 'Chandra Wijaya', 'class' => 'XII IPA 2', 'unique_code' => 'E5F6'],
            ['nis' => '2024004', 'name' => 'Dewi Anggraini', 'class' => 'XII IPS 1', 'unique_code' => 'G7H8'],
            ['nis' => '2024005', 'name' => 'Eka Saputra', 'class' => 'XII IPS 1', 'unique_code' => 'J9K2'],
            ['nis' => '2024006', 'name' => 'Fitri Handayani', 'class' => 'XI IPA 1', 'unique_code' => 'M3N4'],
            ['nis' => '2024007', 'name' => 'Gilang Ramadhan', 'class' => 'XI IPA 2', 'unique_code' => 'P5Q6'],
            ['nis' => '2024008', 'name' => 'Hana Permata', 'class' => 'XI IPS 1', 'unique_code' => 'R7S8'],
        ];

        foreach ($samples as $s) {
            Student::firstOrCreate(['nis' => $s['nis']], $s);
        }

        $this->command->info('>> '.count($samples).' data siswa contoh berhasil dibuat.');
    }
}
