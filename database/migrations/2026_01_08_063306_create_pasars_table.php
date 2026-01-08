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
        Schema::create('pasars', function (Blueprint $table) {
            $table->id();

            $table->string('nama_pasar');
            $table->year('tahun_pembangunan')->nullable();
            $table->decimal('luas_lahan', 10, 2)->nullable(); // m2

            $table->boolean('ada_sertifikat')->default(false);

            $table->string('kecamatan');
            $table->string('kelurahan');

            $table->enum('status_pasar', ['harian', 'mingguan'])->default('harian');

            // Jumlah unit
            $table->integer('jumlah_pedagang')->default(0);
            $table->integer('kios')->default(0);
            $table->integer('los')->default(0);
            $table->integer('lapak')->default(0);
            $table->integer('ruko')->default(0);

            // Kondisi bangunan
            $table->integer('kondisi_baik')->default(0);
            $table->integer('kondisi_rusak')->default(0);
            $table->integer('kondisi_terpakai')->default(0);

            // Tenaga kerja
            $table->integer('kepala_pasar')->default(0);
            $table->integer('petugas_kebersihan')->default(0);
            $table->integer('petugas_keamanan')->default(0);

            // Lain-lain
            $table->integer('retribusi')->default(0);
            $table->integer('ptt')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasars');
    }
};
