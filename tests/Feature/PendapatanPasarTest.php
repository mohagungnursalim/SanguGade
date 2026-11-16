<?php

namespace Tests\Feature;

use App\Helpers\TerbilangHelper;
use App\Livewire\Dashboard\PendapatanPasarIndex;
use App\Models\Pasar;
use App\Models\PendapatanPasar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PendapatanPasarTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Pasar $pasar;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create([
            'email' => 'admin@sangugade.test',
        ]);
        $this->adminUser->assignRole($role);

        $this->pasar = Pasar::create([
            'nama_pasar'         => 'Pasar Kepandean Indah',
            'tahun_pembangunan'  => 2018,
            'luas_lahan'         => 3500.5,
            'ada_sertifikat'     => true,
            'kecamatan'          => 'Serang',
            'kelurahan'          => 'Lopang',
            'status_pasar'       => 'harian',
            'jumlah_pedagang'    => 120,
            'kios'               => 50,
            'los'                => 40,
            'lapak'              => 30,
            'ruko'               => 10,
            'kondisi_baik'       => 100,
            'kondisi_rusak'      => 10,
            'kondisi_terpakai'   => 110,
            'kepala_pasar'       => 1,
            'petugas_kebersihan' => 4,
            'petugas_keamanan'   => 2,
            'retribusi'          => 5000000,
            'ptt'                => 1,
        ]);
    }

    public function test_terbilang_helper_works_correctly(): void
    {
        $this->assertEquals('Nol Rupiah', TerbilangHelper::make(0));
        $this->assertEquals('Lima Belas Juta Dua Ratus Lima Puluh Ribu Rupiah', TerbilangHelper::make(15250000));
        $this->assertEquals('Satu Juta Lima Ratus Ribu Rupiah', TerbilangHelper::make(1500000));
    }

    public function test_guest_cannot_access_pendapatan_pasar(): void
    {
        $response = $this->get(route('dashboard.pendapatan-pasar'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_pendapatan_pasar_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.pendapatan-pasar'));
        $response->assertOk();
        $response->assertSee('Pendapatan Pasar (SSRD)');
    }

    public function test_can_create_ssrd_record(): void
    {
        $customKeterangan = str_repeat('Keterangan bebas ', 40);

        Livewire::actingAs($this->adminUser)
            ->test(PendapatanPasarIndex::class)
            ->set('pasar_id', $this->pasar->id)
            ->set('tahun_ssrd', 2026)
            ->set('no_rekening_bank', '123.456.7890')
            ->set('masa_retribusi', 'Oktober 2026')
            ->set('tahun_retribusi', 2026)
            ->set('nomor_urut_ssrd', '001/SSRD/2026')
            ->set('jenis_retribusi', 'SKRD')
            ->set('nomor_ayat', '4.1.2.01')
            ->set('jumlah_retribusi', 2500000)
            ->set('tanggal_penyetoran', '2026-10-15')
            ->set('nama_penyetor', 'Ahmad Pedagang')
            ->set('petugas_bank', 'Budi Teller BJB')
            ->set('keterangan', 'Lainnya')
            ->set('keterangan_lainnya', $customKeterangan)
            ->call('store')
            ->assertDispatched('ssrd-saved')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('pendapatan_pasars', [
            'pasar_id'         => $this->pasar->id,
            'user_id'          => $this->adminUser->id,
            'nomor_urut_ssrd'  => '001/SSRD/2026',
            'jumlah_retribusi' => 2500000,
            'nama_penyetor'    => 'Ahmad Pedagang',
            'keterangan'       => $customKeterangan,
        ]);
    }

    public function test_can_update_ssrd_record(): void
    {
        $ssrd = PendapatanPasar::create([
            'pasar_id'           => $this->pasar->id,
            'tahun_ssrd'         => 2026,
            'no_rekening_bank'   => '111.222.333',
            'masa_retribusi'     => 'September 2026',
            'tahun_retribusi'    => 2026,
            'nomor_urut_ssrd'    => '002/SSRD/2026',
            'jenis_retribusi'    => 'SKRD',
            'nomor_ayat'         => '4.1.2.01',
            'jumlah_retribusi'   => 1000000,
            'terbilang'          => 'Satu Juta Rupiah',
            'tanggal_penyetoran' => '2026-09-10',
            'nama_penyetor'      => 'Siti Hawa',
            'petugas_bank'       => 'Teller 1',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(PendapatanPasarIndex::class)
            ->set('editId', $ssrd->id)
            ->set('edit_pasar_id', $this->pasar->id)
            ->set('edit_tahun_ssrd', 2026)
            ->set('edit_no_rekening_bank', '111.222.333')
            ->set('edit_masa_retribusi', 'September 2026')
            ->set('edit_tahun_retribusi', 2026)
            ->set('edit_nomor_urut_ssrd', '002/SSRD/2026-REV')
            ->set('edit_jenis_retribusi', 'STRD')
            ->set('edit_nomor_ayat', '4.1.2.01')
            ->set('edit_jumlah_retribusi', 1750000)
            ->set('edit_tanggal_penyetoran', '2026-09-11')
            ->set('edit_nama_penyetor', 'Siti Hawa Updated')
            ->set('edit_petugas_bank', 'Teller 2')
            ->set('edit_keterangan', 'Lainnya')
            ->set('edit_keterangan_lainnya', 'Keterangan bebas saat mengedit')
            ->call('update')
            ->assertDispatched('ssrd-updated')
            ->assertDispatched('notify');

        $this->assertDatabaseHas('pendapatan_pasars', [
            'id'               => $ssrd->id,
            'nomor_urut_ssrd'  => '002/SSRD/2026-REV',
            'jenis_retribusi'  => 'STRD',
            'jumlah_retribusi' => 1750000,
            'nama_penyetor'    => 'Siti Hawa Updated',
            'keterangan'       => 'Keterangan bebas saat mengedit',
        ]);
    }

    public function test_can_delete_ssrd_record(): void
    {
        $ssrd = PendapatanPasar::create([
            'pasar_id'           => $this->pasar->id,
            'tahun_ssrd'         => 2026,
            'tahun_retribusi'    => 2026,
            'jenis_retribusi'    => 'SKRD',
            'jumlah_retribusi'   => 500000,
            'tanggal_penyetoran' => '2026-08-01',
            'nama_penyetor'      => 'Joko',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(PendapatanPasarIndex::class)
            ->call('confirmDelete', $ssrd->id)
            ->assertSet('deleteId', $ssrd->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('pendapatan_pasars', [
            'id' => $ssrd->id,
        ]);
    }

    public function test_validation_fails_when_required_fields_empty(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(PendapatanPasarIndex::class)
            ->set('pasar_id', null)
            ->set('nama_penyetor', '')
            ->set('keterangan', 'Lainnya')
            ->call('store')
            ->assertHasErrors(['pasar_id', 'nama_penyetor', 'keterangan_lainnya']);
    }

    public function test_operator_sees_all_records_for_their_assigned_market(): void
    {
        $otherPasar = Pasar::create([
            'nama_pasar' => 'Pasar B',
            'kecamatan' => 'Serang',
            'kelurahan' => 'Cipare',
        ]);
        $operator = User::factory()->create(['pasar_id' => $this->pasar->id]);
        $role = Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        $role->syncPermissions(
            collect(['read', 'create', 'update', 'delete'])
                ->map(fn (string $name) => Permission::findOrCreate($name, 'web'))
        );
        $operator->assignRole($role);

        $recordAttributes = [
            'tahun_ssrd' => 2026,
            'tahun_retribusi' => 2026,
            'jenis_retribusi' => 'SKRD',
            'tanggal_penyetoran' => '2026-10-15',
            'nama_penyetor' => 'Penyetor',
        ];

        PendapatanPasar::create($recordAttributes + [
            'pasar_id' => $this->pasar->id,
            'user_id' => $operator->id,
            'nomor_urut_ssrd' => 'OWN-SSRD',
            'jumlah_retribusi' => 1500,
        ]);
        PendapatanPasar::create($recordAttributes + [
            'pasar_id' => $this->pasar->id,
            'user_id' => $this->adminUser->id,
            'nomor_urut_ssrd' => 'OTHER-USER-SSRD',
            'jumlah_retribusi' => 2500,
        ]);
        PendapatanPasar::create($recordAttributes + [
            'pasar_id' => $otherPasar->id,
            'user_id' => $operator->id,
            'nomor_urut_ssrd' => 'OTHER-MARKET-SSRD',
            'jumlah_retribusi' => 3500,
        ]);

        Livewire::actingAs($operator)
            ->test(PendapatanPasarIndex::class)
            ->assertSee('OWN-SSRD')
            ->assertSee('OTHER-USER-SSRD')
            ->assertDontSee('OTHER-MARKET-SSRD')
            ->assertViewHas('totalNominalAll', 4000.0)
            ->assertViewHas('ringkasanPerPasar', fn ($pasars) => $pasars->count() === 1
                && $pasars->first()->id === $this->pasar->id
                && $pasars->first()->pendapatan_pasars_count === 2);

        Livewire::actingAs($operator)
            ->test(PendapatanPasarIndex::class)
            ->set('pasar_id', $otherPasar->id)
            ->set('tahun_ssrd', 2026)
            ->set('tahun_retribusi', 2026)
            ->set('jumlah_retribusi', 1000)
            ->set('tanggal_penyetoran', '2026-10-16')
            ->set('nama_penyetor', 'Operator')
            ->set('keterangan', 'Ret. Harian')
            ->call('store')
            ->assertDispatched('ssrd-saved');

        $this->assertDatabaseHas('pendapatan_pasars', [
            'user_id' => $operator->id,
            'pasar_id' => $this->pasar->id,
            'nama_penyetor' => 'Operator',
        ]);
    }

    public function test_non_operator_only_sees_records_they_uploaded(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'opd', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::findOrCreate('read', 'web'));
        $user->assignRole($role);

        $attributes = [
            'pasar_id' => $this->pasar->id,
            'tahun_ssrd' => 2026,
            'tahun_retribusi' => 2026,
            'jenis_retribusi' => 'SKRD',
            'tanggal_penyetoran' => '2026-10-15',
            'nama_penyetor' => 'Penyetor',
        ];

        PendapatanPasar::create($attributes + [
            'user_id' => $user->id,
            'nomor_urut_ssrd' => 'MY-SSRD',
            'jumlah_retribusi' => 1500,
        ]);
        PendapatanPasar::create($attributes + [
            'user_id' => $this->adminUser->id,
            'nomor_urut_ssrd' => 'ADMIN-SSRD',
            'jumlah_retribusi' => 2500,
        ]);

        Livewire::actingAs($user)
            ->test(PendapatanPasarIndex::class)
            ->assertSee('MY-SSRD')
            ->assertDontSee('ADMIN-SSRD')
            ->assertViewHas('totalNominalAll', 1500.0);
    }
}
