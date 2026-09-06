<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel kandidat (paslon Ketua & Wakil OSIS).
     * `vote_count` adalah counter cache yang di-increment atomis di dalam
     * transaksi vote (bukan dihitung ulang dari COUNT(*) setiap saat) agar
     * leaderboard admin bisa dibaca super cepat tanpa membebani DB.
     * Sumber kebenaran tetap tabel `votes` — vote_count hanya cache.
     */
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number_order')->unique(); // Nomor Urut
            $table->string('chairman_name');       // Nama Ketua
            $table->string('vice_chairman_name');  // Nama Wakil
            $table->string('chairman_photo')->nullable();
            $table->string('vice_chairman_photo')->nullable();
            $table->string('slogan')->nullable();
            $table->text('vision');   // Visi Utama
            $table->json('mission');  // Array poin-poin misi
            $table->unsignedInteger('vote_count')->default(0);
            $table->boolean('is_active')->default(true); // tampil/tidak di bilik suara
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
