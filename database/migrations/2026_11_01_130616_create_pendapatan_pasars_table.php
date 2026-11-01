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
        Schema::create('pendapatan_pasars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasar_id')->constrained('pasars')->cascadeOnDelete();
            $table->year('tahun_ssrd');
            $table->string('no_rekening_bank', 50)->nullable();
            $table->string('masa_retribusi', 100)->nullable();
            $table->year('tahun_retribusi');
            $table->string('nomor_urut_ssrd', 50)->nullable();
            $table->string('jenis_retribusi', 50); // SKRD, SKRDT, SKRDB, SKRDKBT, STRD, STPRD, SK Pembetulan, SK Keberatan, Lain-lain
            $table->string('nomor_ayat', 50)->nullable();
            $table->decimal('jumlah_retribusi', 15, 2)->default(0);
            $table->text('terbilang')->nullable();
            $table->date('tanggal_penyetoran');
            $table->string('nama_penyetor', 150);
            $table->string('petugas_bank', 150)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['pasar_id', 'tahun_retribusi']);
            $table->index('tanggal_penyetoran');
            $table->index('jenis_retribusi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendapatan_pasars');
    }
};
