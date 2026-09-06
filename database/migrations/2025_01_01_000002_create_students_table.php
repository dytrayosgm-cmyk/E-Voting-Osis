<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel daftar pemilih (siswa). `nis` diberi unique() yang otomatis
     * membuat index, sehingga pencarian saat verifikasi cepat dan tidak
     * mungkin ada NIS kembar di database.
     *
     * `has_voted` + `voted_at` adalah penanda cepat (denormalized flag)
     * yang SELALU divalidasi ulang di dalam transaksi terkunci pada saat
     * proses vote (lihat VotingBooth::confirmVote()). Kolom ini tidak
     * boleh dipercaya begitu saja tanpa lock, karena rentan race condition.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 30)->unique();
            $table->string('name');
            $table->string('class', 50)->nullable(); // kelas, mis. "XII IPA 1"
            $table->boolean('has_voted')->default(false);
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();

            $table->index(['nis', 'has_voted']); // mempercepat query verifikasi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
