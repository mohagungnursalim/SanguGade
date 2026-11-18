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
        if (Schema::hasTable('pedagangs') && !Schema::hasColumn('pedagangs', 'alamat_usaha')) {
            Schema::table('pedagangs', function (Blueprint $table) {
                $table->text('alamat_usaha')->nullable()->after('alamat');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pedagangs') && Schema::hasColumn('pedagangs', 'alamat_usaha')) {
            Schema::table('pedagangs', function (Blueprint $table) {
                $table->dropColumn('alamat_usaha');
            });
        }
    }
};

