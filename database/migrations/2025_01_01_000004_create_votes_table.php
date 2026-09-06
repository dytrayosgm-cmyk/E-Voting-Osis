<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel suara masuk.
     *
     * KUNCI KEAMANAN UTAMA: `student_id` diberi constraint UNIQUE.
     * Ini adalah lapisan pertahanan terakhir di level database — bahkan
     * jika application-level lock (Cache::lock) dan row-level lock
     * (lockForUpdate) sama-sama gagal mencegah race condition, MySQL
     * sendiri akan menolak INSERT kedua untuk student_id yang sama dan
     * melempar QueryException (error code 23000) yang kita tangkap
     * di Livewire component.
     */
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->unique() // <-- 1 siswa = maksimal 1 baris vote, dijamin oleh DB
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('candidate_id')
                ->constrained('candidates')
                ->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('voted_at');
            $table->timestamps();

            $table->index('candidate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
