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
        if (!Schema::hasTable('pedagangs')) {
            return;
        }

        $needsNamaTempat = !Schema::hasColumn('pedagangs', 'nama_tempat');
        $needsKeterangan = !Schema::hasColumn('pedagangs', 'keterangan');

        if ($needsNamaTempat || $needsKeterangan) {
            Schema::table('pedagangs', function (Blueprint $table) use ($needsNamaTempat, $needsKeterangan) {
                if ($needsNamaTempat) {
                    $table->string('nama_tempat')->nullable()->after('alamat');
                }
                if ($needsKeterangan) {
                    $table->text('keterangan')->nullable()->after('ukuran');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('pedagangs')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('pedagangs', 'nama_tempat') ? 'nama_tempat' : null,
            Schema::hasColumn('pedagangs', 'keterangan') ? 'keterangan' : null,
        ]));

        if (!empty($columns)) {
            Schema::table('pedagangs', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};

