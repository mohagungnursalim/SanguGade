<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendapatan_pasars', function (Blueprint $table) {
            $table->string('bukti_ssrd')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pendapatan_pasars', function (Blueprint $table) {
            $table->dropColumn('bukti_ssrd');
        });
    }
};
