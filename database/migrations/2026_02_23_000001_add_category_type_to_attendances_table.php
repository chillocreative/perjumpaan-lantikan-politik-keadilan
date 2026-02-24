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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('category_type')->after('ic_number_hash');
        });

        // Drop the FK on meeting_id so we can drop the indexes it depends on
        DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_meeting_id_foreign');
        DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_member_id_foreign');
        DB::statement('ALTER TABLE attendances DROP INDEX attendance_ic_lock');
        DB::statement('ALTER TABLE attendances DROP INDEX attendance_member_lock');

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->cascadeOnDelete();
            $table->unique(['meeting_id', 'ic_number_hash', 'category_type'], 'attendance_category_lock');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendance_category_lock');
            $table->unique(['meeting_id', 'ic_number_hash'], 'attendance_ic_lock');
            $table->unique(['meeting_id', 'member_id'], 'attendance_member_lock');
            $table->dropColumn('category_type');
        });
    }
};
