<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['ic_number_hash']);
            $table->unique(['ic_number_hash', 'category_type']);
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['ic_number_hash', 'category_type']);
            $table->unique('ic_number_hash');
        });
    }
};
