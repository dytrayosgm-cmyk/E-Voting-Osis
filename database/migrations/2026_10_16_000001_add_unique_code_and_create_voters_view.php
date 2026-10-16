<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'unique_code')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('unique_code', 10)->nullable()->after('class');
                $table->index('unique_code');
            });
        }

        // Generate unique_code for any existing rows that have null unique_code
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $rows = DB::table('students')->whereNull('unique_code')->orWhere('unique_code', '')->get(['id']);
        foreach ($rows as $row) {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
            DB::table('students')->where('id', $row->id)->update(['unique_code' => $code]);
        }

        // Create view/triggers for 'voters'
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('CREATE VIEW IF NOT EXISTS voters AS SELECT * FROM students');
            DB::statement('CREATE TRIGGER IF NOT EXISTS insert_voters INSTEAD OF INSERT ON voters BEGIN INSERT INTO students (nis, name, class, unique_code, has_voted, voted_at, created_at, updated_at) VALUES (NEW.nis, NEW.name, NEW.class, NEW.unique_code, COALESCE(NEW.has_voted, 0), NEW.voted_at, NEW.created_at, NEW.updated_at); END;');
            DB::statement('CREATE TRIGGER IF NOT EXISTS update_voters INSTEAD OF UPDATE ON voters BEGIN UPDATE students SET nis = NEW.nis, name = NEW.name, class = NEW.class, unique_code = NEW.unique_code, has_voted = NEW.has_voted, voted_at = NEW.voted_at, updated_at = NEW.updated_at WHERE id = OLD.id; END;');
            DB::statement('CREATE TRIGGER IF NOT EXISTS delete_voters INSTEAD OF DELETE ON voters BEGIN DELETE FROM students WHERE id = OLD.id; END;');
        } else {
            DB::statement('CREATE OR REPLACE VIEW voters AS SELECT * FROM students');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS insert_voters');
            DB::statement('DROP TRIGGER IF EXISTS update_voters');
            DB::statement('DROP TRIGGER IF EXISTS delete_voters');
            DB::statement('DROP VIEW IF EXISTS voters');
        } else {
            DB::statement('DROP VIEW IF EXISTS voters');
        }

        if (Schema::hasColumn('students', 'unique_code')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('unique_code');
            });
        }
    }
};
