<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::current(); // pastikan baris settings id=1 dibuat, default: voting closed

        $this->call([
            AdminSeeder::class,
            StudentSeeder::class,
            CandidateSeeder::class,
        ]);
    }
}
