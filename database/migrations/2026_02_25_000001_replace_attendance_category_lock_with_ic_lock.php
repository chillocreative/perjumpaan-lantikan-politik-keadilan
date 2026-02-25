<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dbName = DB::getDatabaseName();

        // Helper: check if a foreign key exists
        $fkExists = fn (string $fk) => DB::selectOne(
            "SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = 'attendances'
               AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$dbName, $fk]
        ) !== null;

        // Helper: check if an index exists
        $indexExists = fn (string $index) => DB::selectOne(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'attendances'
               AND INDEX_NAME = ?",
            [$dbName, $index]
        ) !== null;

        // 1. Remove duplicate IC entries per meeting (safe to re-run)
        DB::statement('
            DELETE a FROM attendances a
            INNER JOIN (
                SELECT meeting_id, ic_number_hash, MIN(id) AS keep_id
                FROM attendances
                GROUP BY meeting_id, ic_number_hash
                HAVING COUNT(*) > 1
            ) dups ON a.meeting_id = dups.meeting_id
                  AND a.ic_number_hash = dups.ic_number_hash
                  AND a.id != dups.keep_id
        ');

        // 2. Drop FK only if it exists
        if ($fkExists('attendances_meeting_id_foreign')) {
            DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_meeting_id_foreign');
        }

        // 3. Drop old index only if it exists
        if ($indexExists('attendance_category_lock')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropUnique('attendance_category_lock');
            });
        }

        // 4. Add new unique index only if it doesn't exist
        if (!$indexExists('attendance_ic_lock')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unique(['meeting_id', 'ic_number_hash'], 'attendance_ic_lock');
            });
        }

        // 5. Re-add FK only if it doesn't exist
        if (!$fkExists('attendances_meeting_id_foreign')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_meeting_id_foreign');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_ic_lock');
            $table->unique(['meeting_id', 'ic_number_hash', 'category_type'], 'attendance_category_lock');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
        });
    }
};
