<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel single-row untuk status sesi voting global.
     * Selalu hanya ada 1 baris (id = 1) yang dibaca/diperbarui oleh
     * tombol "Tutup/Buka Sesi Voting" di Dashboard Admin.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('session_name')->default('Pemilihan Ketua & Wakil OSIS');
            $table->boolean('is_voting_open')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
