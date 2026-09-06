<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel audit trail / security log.
     * Setiap kejadian penting (vote sukses, percobaan vote ganda, race
     * condition yang berhasil diblokir, aksi admin, dsb) dicatat di sini
     * dan ditampilkan real-time di Dashboard Admin.
     */
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            // vote_success | duplicate_attempt | race_condition_blocked |
            // nis_not_found | admin_action | session_toggled | data_reset

            $table->string('message');
            $table->foreignId('student_id')->nullable()
                ->constrained('students')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->json('meta')->nullable(); // payload tambahan (fleksibel)
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
