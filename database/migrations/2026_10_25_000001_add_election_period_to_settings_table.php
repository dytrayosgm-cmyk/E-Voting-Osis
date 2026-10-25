<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('settings', 'election_period')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('election_period')->default('Periode 2025/2026')->after('session_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('settings', 'election_period')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('election_period');
            });
        }
    }
};

