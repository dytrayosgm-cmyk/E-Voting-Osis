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
        if (! Schema::hasColumn('settings', 'voting_mode')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('voting_mode', 20)->default('paslon')->after('session_name');
            });
        }

        if (! Schema::hasColumn('candidates', 'type')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->string('type', 20)->default('paslon')->after('number_order');
            });
        }

        // Jadikan nama wakil nullable agar form kandidat individu dapat disimpan tanpa wakil
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('vice_chairman_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('settings', 'voting_mode')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('voting_mode');
            });
        }

        if (Schema::hasColumn('candidates', 'type')) {
            Schema::table('candidates', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->string('vice_chairman_name')->nullable(false)->change();
        });
    }
};

