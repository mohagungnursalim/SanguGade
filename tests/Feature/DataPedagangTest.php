<?php

namespace Tests\Feature;

use App\Jobs\ExportPedagangJob;
use App\Jobs\ImportPedagangJob;
use App\Livewire\Dashboard\DataPedagang;
use App\Models\Pasar;
use App\Models\Pedagang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DataPedagangTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Pasar $pasar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pasar = Pasar::create([
            'nama_pasar' => 'Pasar Masomba',
            'kecamatan'  => 'Palu Selatan',
            'kelurahan'  => 'Tatura Utara',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($adminRole);
    }

    public function test_authenticated_user_can_access_data_pedagang_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.data-pedagang'));
        $response->assertOk();
        $response->assertSee('Data Pedagang');
    }

    public function test_can_create_pedagang(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(DataPedagang::class)
            ->set('nama', 'Budi Santoso')
            ->set('nik', '7271012345678901')
            ->set('alamat', 'Jl. Mawar No 1')
            ->set('nama_tempat', 'Kios A No 5')
            ->set('jenis_sarana', 'Kios')
            ->set('ukuran', 6)
            ->set('tarif_per_meter', 25000)
            ->set('tarif_bulanan', 150000)
            ->set('jenis_usaha', 'Sembako')
            ->set('latitude', -0.8912)
            ->set('longitude', 119.8765)
            ->set('alamat_usaha', 'Blok A No 1')
            ->set('keterangan', 'Aktif berjualan')
            ->set('pasar_id', $this->pasar->id)
            ->call('save')
            ->assertDispatched('pedagang-saved')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('pedagangs', [
            'nama' => 'Budi Santoso',
            'nik' => '7271012345678901',
            'nama_tempat' => 'Kios A No 5',
            'jenis_sarana' => 'Kios',
            'jenis_usaha' => 'Sembako',
            'keterangan' => 'Aktif berjualan',
            'pasar_id' => $this->pasar->id,
        ]);
    }

    public function test_can_update_pedagang(): void
    {
        $pedagang = Pedagang::create([
            'nama' => 'Pedagang Lama',
            'nik' => '7271000000000001',
            'nama_tempat' => 'Kios Lama',
            'jenis_sarana' => 'Kios',
            'jenis_usaha' => 'Sembako',
            'keterangan' => 'Catatan lama',
            'pasar_id' => $this->pasar->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(DataPedagang::class)
            ->set('pedagangId', $pedagang->id)
            ->set('nama', 'Pedagang Diedit')
            ->set('nik', '7271000000000001')
            ->set('nama_tempat', 'Los Baru No 2')
            ->set('jenis_sarana', 'Los')
            ->set('jenis_usaha', 'Sayur')
            ->set('keterangan', 'Catatan diperbarui')
            ->set('pasar_id', $this->pasar->id)
            ->call('save')
            ->assertDispatched('pedagang-saved');

        $this->assertDatabaseHas('pedagangs', [
            'id' => $pedagang->id,
            'nama' => 'Pedagang Diedit',
            'nama_tempat' => 'Los Baru No 2',
            'jenis_sarana' => 'Los',
            'jenis_usaha' => 'Sayur',
            'keterangan' => 'Catatan diperbarui',
        ]);
    }

    public function test_can_delete_pedagang(): void
    {
        $pedagang = Pedagang::create([
            'nama' => 'Pedagang Dihapus',
            'nik' => '7271000000000002',
            'jenis_sarana' => 'Kios',
            'jenis_usaha' => 'Sembako',
            'pasar_id' => $this->pasar->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(DataPedagang::class)
            ->set('deleteId', $pedagang->id)
            ->call('delete')
            ->assertDispatched('pedagang-deleted');

        $this->assertSoftDeleted('pedagangs', [
            'id' => $pedagang->id,
        ]);
    }

    public function test_pedagang_status_stats_and_30_day_pruning(): void
    {
        $aktif = Pedagang::create([
            'nama' => 'Pedagang Aktif',
            'jenis_sarana' => 'Kios',
            'jenis_usaha' => 'Sembako',
            'pasar_id' => $this->pasar->id,
        ]);

        $keluarBaru = Pedagang::create([
            'nama' => 'Pedagang Keluar Baru',
            'jenis_sarana' => 'Los',
            'jenis_usaha' => 'Sayur',
            'pasar_id' => $this->pasar->id,
        ]);
        $keluarBaru->delete();

        $keluarLama = Pedagang::create([
            'nama' => 'Pedagang Keluar Lama',
            'jenis_sarana' => 'Lapak',
            'jenis_usaha' => 'Ikan',
            'pasar_id' => $this->pasar->id,
        ]);
        $keluarLama->forceFill(['deleted_at' => now()->subDays(31)])->saveQuietly();

        \App\Helpers\PedagangCacheHelper::refreshAll();
        $stats = \App\Helpers\PedagangCacheHelper::getStatusPedagangStats($this->pasar->id);

        $this->assertSame([$this->pasar->nama_pasar], $stats['labels']);
        $this->assertSame([1], $stats['aktif']);
        $this->assertSame([2], $stats['keluar']);

        // Jalankan prune -> yang >= 30 hari terhapus permanen
        $this->artisan('model:prune', ['--model' => [Pedagang::class]])->assertExitCode(0);

        $this->assertDatabaseHas('pedagangs', ['id' => $aktif->id]);
        $this->assertSoftDeleted('pedagangs', ['id' => $keluarBaru->id]);
        $this->assertDatabaseMissing('pedagangs', ['id' => $keluarLama->id]);
    }

    public function test_can_import_excel(): void
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->create('pedagang.xlsx', 100);

        Livewire::actingAs($this->adminUser)
            ->test(DataPedagang::class)
            ->set('excelFile', $file)
            ->call('importExcel')
            ->assertDispatched('excel-imported');

        Queue::assertPushed(ImportPedagangJob::class);
    }

    public function test_import_job_processes_records_and_upserts(): void
    {
        Storage::fake('local');

        $csvContent = "Nama,NIK,Alamat Tinggal,Nama Tempat,Jenis Sarana,Ukuran,Tarif per meter,Total tarif bulanan,Jenis Usaha,Titik Kordinat,Alamat Usaha,Keterangan,Pasar\n"
            . "Siti Aminah,7271023456789012,Jl. Melati,Los B No 2,Los,4,20000,80000,Sayuran,\"-0.891, 119.876\",Blok B No 2,Catatan impor,{$this->pasar->id}\n";

        Storage::disk('local')->put('temp_imports/test.csv', $csvContent);

        $job = new ImportPedagangJob('temp_imports/test.csv', $this->pasar->id, true);
        $job->handle();

        $this->assertDatabaseHas('pedagangs', [
            'nama' => 'Siti Aminah',
            'nik' => '7271023456789012',
            'nama_tempat' => 'Los B No 2',
            'jenis_sarana' => 'Los',
            'jenis_usaha' => 'Sayuran',
            'keterangan' => 'Catatan impor',
            'pasar_id' => $this->pasar->id,
        ]);
    }

    public function test_export_dispatches_queue_job(): void
    {
        Queue::fake();

        Livewire::actingAs($this->adminUser)
            ->test(DataPedagang::class)
            ->call('exportExcel')
            ->assertDispatched('export-queued');

        Queue::assertPushed(ExportPedagangJob::class);
    }

    public function test_export_job_creates_xlsx_with_nama_tempat_and_keterangan(): void
    {
        Pedagang::create([
            'nama' => 'Pedagang Export',
            'nik' => '7271099999999999',
            'alamat' => 'Jl. Export',
            'nama_tempat' => 'Kios C No 9',
            'jenis_sarana' => 'Kios',
            'ukuran' => 5,
            'tarif_per_meter' => 25000,
            'tarif_bulanan' => 125000,
            'jenis_usaha' => 'Pakaian',
            'alamat_usaha' => 'Blok C No 9',
            'keterangan' => 'Lunas bulan ini',
            'pasar_id' => $this->pasar->id,
        ]);

        $fileName = 'test_pedagang_export_' . uniqid() . '.xlsx';
        $fullPath = storage_path('app/public/exports/' . $fileName);

        try {
            $job = new ExportPedagangJob($this->pasar->id, $fileName);
            $job->handle();

            $this->assertFileExists($fullPath);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($fullPath) === true);
            $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();

            $this->assertIsString($sheetXml);
            $this->assertStringContainsString('Nama Tempat', $sheetXml);
            $this->assertStringContainsString('Keterangan', $sheetXml);
            $this->assertStringContainsString('Kios C No 9', $sheetXml);
            $this->assertStringContainsString('Lunas bulan ini', $sheetXml);
        } finally {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
