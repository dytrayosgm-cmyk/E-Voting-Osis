<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        Candidate::firstOrCreate(['number_order' => 1], [
            'chairman_name' => 'Rafi Aditya',
            'vice_chairman_name' => 'Salsa Amelia',
            'chairman_photo' => 'candidates/2yIDjK73xYpSZPq766t9cOgJ5HO31Ltv0F2RzrrD.jpg',
            'vice_chairman_photo' => 'candidates/9M1Ef8rTelQ3Zp7pvglID3S6LaEYZABw4ihPw7WH.jpg',
            'slogan' => 'Bersama Kita Berkarya, Mandiri dan Berprestasi',
            'vision' => 'Mewujudkan OSIS yang aktif, inovatif, dan dekat dengan seluruh siswa.',
            'mission' => [
                'Mengaktifkan kembali program mentoring antar angkatan',
                'Membuka ruang aspirasi digital untuk seluruh siswa',
                'Mengadakan event kreativitas siswa setiap bulan',
            ],
            'is_active' => true,
        ]);

        Candidate::firstOrCreate(['number_order' => 2], [
            'chairman_name' => 'Bima Nugroho',
            'vice_chairman_name' => 'Citra Wulandari',
            'chairman_photo' => 'candidates/i5kNseHdGKZtP8PaQkuQtYkvY7zKJf5d7KgprOdJ.jpg',
            'vice_chairman_photo' => 'candidates/I7XoFbG38b85lNJFhh35X3wlyDGYB4qwhqmKbe82.jpg',
            'slogan' => 'Satu Suara, Satu Tujuan Memajukan Almamater',
            'vision' => 'Membangun ekosistem sekolah yang inklusif, transparan, dan berprestasi.',
            'mission' => [
                'Transparansi penuh laporan kegiatan & keuangan OSIS',
                'Program kolaborasi lintas ekstrakurikuler',
                'Pelatihan kepemimpinan untuk pengurus kelas',
            ],
            'is_active' => true,
        ]);

        $this->command->info('>> 2 paslon kandidat contoh berhasil dibuat.');
    }
}
