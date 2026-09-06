<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Panitia OSIS',
                'password' => 'password123', // otomatis di-hash lewat cast 'hashed' di Model Admin
                'role' => 'superadmin',
            ]
        );

        $this->command->warn('>> Akun admin default dibuat: username=admin / password=password123');
        $this->command->warn('>> WAJIB segera ganti password ini sebelum hari-H pemilihan!');
    }
}
