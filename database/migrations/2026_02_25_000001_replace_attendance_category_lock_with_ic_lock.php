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
        // Drop FK that depends on the unique index, then swap the index, then re-add FK
        DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_meeting_id_foreign');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_category_lock');
            $table->unique(['meeting_id', 'ic_number_hash'], 'attendance_ic_lock');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
        });
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
