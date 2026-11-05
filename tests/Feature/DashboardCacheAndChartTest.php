<?php

namespace Tests\Feature;

use App\Helpers\PasarsCacheHelper;
use App\Helpers\PendapatanCacheHelper;
use App\Livewire\Dashboard\Dashboard;
use App\Models\Pasar;
use App\Models\PendapatanPasar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardCacheAndChartTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Pasar $pasarA;
    protected Pasar $pasarB;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create([
            'email' => 'admin_dashboard@sangugade.test',
        ]);
        $this->adminUser->assignRole($role);

        $this->pasarA = Pasar::create([
            'nama_pasar'         => 'Pasar Rau Trade Center',
            'tahun_pembangunan'  => 2015,
            'luas_lahan'         => 5000,
            'ada_sertifikat'     => true,
            'kecamatan'          => 'Serang',
            'kelurahan'          => 'Cimuncang',
            'status_pasar'       => 'harian',
            'jumlah_pedagang'    => 200,
            'kios'               => 100,
            'los'                => 80,
            'lapak'              => 20,
            'ruko'               => 10,
            'kondisi_baik'       => 180,
            'kondisi_rusak'      => 20,
            'kondisi_terpakai'   => 190,
            'kepala_pasar'       => 1,
            'petugas_kebersihan' => 5,
            'petugas_keamanan'   => 3,
            'retribusi'          => 10000000,
            'ptt'                => 2,
        ]);

        $this->pasarB = Pasar::create([
            'nama_pasar'         => 'Pasar Kragilan',
            'tahun_pembangunan'  => 2019,
            'luas_lahan'         => 3000,
            'ada_sertifikat'     => false,
            'kecamatan'          => 'Kragilan',
            'kelurahan'          => 'Kragilan',
            'status_pasar'       => 'harian',
            'jumlah_pedagang'    => 80,
            'kios'               => 40,
            'los'                => 30,
            'lapak'              => 10,
            'ruko'               => 5,
            'kondisi_baik'       => 70,
            'kondisi_rusak'      => 10,
            'kondisi_terpakai'   => 75,
            'kepala_pasar'       => 1,
            'petugas_kebersihan' => 2,
            'petugas_keamanan'   => 2,
            'retribusi'          => 4000000,
            'ptt'                => 1,
        ]);

        // Buat record pendapatan
        PendapatanPasar::create([
            'pasar_id'           => $this->pasarA->id,
            'tahun_ssrd'         => 2026,
            'tahun_retribusi'    => 2026,
            'jenis_retribusi'    => 'SKRD',
            'jumlah_retribusi'   => 3000000,
            'tanggal_penyetoran' => '2026-05-10',
            'nama_penyetor'      => 'Ahmad',
        ]);

        PendapatanPasar::create([
            'pasar_id'           => $this->pasarB->id,
            'tahun_ssrd'         => 2026,
            'tahun_retribusi'    => 2026,
            'jenis_retribusi'    => 'SKRD',
            'jumlah_retribusi'   => 1500000,
            'tanggal_penyetoran' => '2026-06-15',
            'nama_penyetor'      => 'Budi',
        ]);
    }

    public function test_pasars_cache_helper_fetches_and_invalidates_cache(): void
    {
        $totalInitial = PasarsCacheHelper::getTotalPasar();
        $this->assertEquals(2, $totalInitial);
        $this->assertTrue(Cache::has('cache_pasars_total'));

        // Tambah pasar baru
        Pasar::create([
            'nama_pasar' => 'Pasar Tambahan',
            'kecamatan'  => 'Ciruas',
            'kelurahan'  => 'Citerep',
        ]);

        // Refresh / Invalidate cache
        PasarsCacheHelper::refreshAll();

        $totalAfter = PasarsCacheHelper::getTotalPasar();
        $this->assertEquals(3, $totalAfter);
    }

    public function test_pendapatan_cache_helper_chart_data(): void
    {
        $chartBulan = PendapatanCacheHelper::getChartData('bulan', null, 2026);
        $this->assertCount(12, $chartBulan['labels']);
        $this->assertEquals(4500000, $chartBulan['total']);

        // Filter per pasar A
        $chartPasarA = PendapatanCacheHelper::getChartData('bulan', $this->pasarA->id, 2026);
        $this->assertEquals(3000000, $chartPasarA['total']);

        // Filter per hari
        $chartHari = PendapatanCacheHelper::getChartData('hari', null, 2026, 5);
        $this->assertNotEmpty($chartHari['labels']);
        $this->assertEquals(3000000, $chartHari['total']);

        // Filter per tahun
        $chartTahun = PendapatanCacheHelper::getChartData('tahun', null, 2026);
        $this->assertNotEmpty($chartTahun['labels']);
        $this->assertEquals(4500000, $chartTahun['total']);

        // Filter periode tanggal
        $chartPeriode = PendapatanCacheHelper::getChartData('periode', null, null, null, '2026-05-01', '2026-05-31');
        $this->assertEquals(3000000, $chartPeriode['total']);
    }

    public function test_dashboard_renders_with_cached_data(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard Monitoring Sangu Gade');
        $response->assertSee('Pasar Rau Trade Center');
        $response->assertSee('Pasar Kragilan');
        $response->assertSee('Global Cache Active');
    }

    public function test_dashboard_livewire_component_updates_timeframe(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(Dashboard::class)
            ->call('setTimeframe', 'tahun')
            ->assertSet('timeframe', 'tahun')
            ->assertDispatched('chart-data-updated')
            ->call('setTimeframe', 'hari')
            ->assertSet('timeframe', 'hari')
            ->assertDispatched('chart-data-updated')
            ->call('setTimeframe', 'periode')
            ->assertSet('timeframe', 'periode')
            ->assertDispatched('chart-data-updated');
    }
}

