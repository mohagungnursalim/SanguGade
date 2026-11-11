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
        Schema::create('pedagangs', function (Blueprint $table) {
        $table->id();

        // Identitas Pedagang
        $table->string('nama');
        $table->string('nik', 20)->nullable()->unique();
        $table->string('email')->nullable()->unique();
        $table->string('no_telepon', 20)->nullable();
        $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
        $table->text('alamat')->nullable();

        // Data Usaha
        $table->string('jenis_usaha'); // contoh: sembako, daging, sayur
        $table->string('jenis_sarana'); // kios, los, tenda, dll
        $table->decimal('ukuran', 8, 2)->nullable(); // contoh: 2.50 (meter)
        
        // Tarif
        $table->decimal('tarif_per_meter', 12, 2)->default(0);
        $table->decimal('tarif_bulanan', 12, 2)->default(0);

        // Lokasi
        $table->foreignId('pasar_id')->constrained('pasars');
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();

        $table->timestamps();

        // Index buat pencarian cepat
        $table->index('nama');
        $table->index(['pasar_id', 'jenis_usaha']);

    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedagangs');
    }
};
