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
        if (Schema::hasTable('pedagangs') && Schema::hasIndex('pedagangs', 'pedagangs_nik_unique')) {
            Schema::table('pedagangs', function (Blueprint $table) {
                // Drop unique index pada kolom nik
                $table->dropUnique('pedagangs_nik_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pedagangs') && !Schema::hasIndex('pedagangs', 'pedagangs_nik_unique')) {
            Schema::table('pedagangs', function (Blueprint $table) {
                $table->unique('nik');
            });
        }
    }
};

