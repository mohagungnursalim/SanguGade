<?php

namespace App\Livewire\Dashboard;

use App\Helpers\PasarsCacheHelper;
use App\Models\Pasar;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class SaranaPrasarana extends Component
{
    use WithFileUploads, WithPagination;

    // ─── State Modal & Delete ───────────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deleteId        = null;
    public string $deleteName    = '';

    // ─── Upload gambar (create) ─────────────────────────────────────────────
    public $image;

    // ─── Upload gambar (edit) ───────────────────────────────────────────────
    public $editImage;
    public ?string $editImagePath = null;

    // ─── Form create ────────────────────────────────────────────────────────
    public string $nama_pasar       = '';
    public string $tahun_pembangunan = '';
    public string $luas_lahan       = '';
    public bool   $ada_sertifikat   = false;
    public string $kecamatan        = '';
    public string $kelurahan        = '';
    public string $status_pasar     = 'harian';
    public int    $jumlah_pedagang  = 0;
    public int    $kios             = 0;
    public int    $los              = 0;
    public int    $lapak            = 0;
    public int    $ruko             = 0;
    public int    $kondisi_baik     = 0;
    public int    $kondisi_rusak    = 0;
    public int    $kondisi_terpakai = 0;
    public int    $kepala_pasar     = 0;
    public int    $petugas_kebersihan = 0;
    public int    $petugas_keamanan = 0;
    public int    $retribusi        = 0;
    public int    $ptt              = 0;

    // ─── Form edit ──────────────────────────────────────────────────────────
    public ?int   $editId               = null;
    public string $editNamaPasar        = '';
    public string $editTahunPembangunan  = '';
    public string $editLuasLahan        = '';
    public bool   $editAdaSertifikat    = false;
    public string $editKecamatan        = '';
    public string $editKelurahan        = '';
    public string $editStatusPasar      = 'harian';
    public int    $editJumlahPedagang   = 0;
    public int    $editKios             = 0;
    public int    $editLos              = 0;
    public int    $editLapak            = 0;
    public int    $editRuko             = 0;
    public int    $editKondisiBaik      = 0;
    public int    $editKondisiRusak     = 0;
    public int    $editKondisiTerpakai  = 0;
    public int    $editKepalaPasar      = 0;
    public int    $editPetugasKebersihan = 0;
    public int    $editPetugasKeamanan  = 0;
    public int    $editRetribusi        = 0;
    public int    $editPtt              = 0;

    // ─── Search ─────────────────────────────────────────────────────────────
    #[Url()]
    public string $search = '';

    // ─── Pagination ─────────────────────────────────────────────────────────
    public int $perPage = 10;

    // ─── Validation Rules ────────────────────────────────────────────────────
    protected function createRules(): array
    {
        return [
            'nama_pasar'         => 'required|string|max:255',
            'image'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tahun_pembangunan'  => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
            'luas_lahan'         => 'nullable|numeric|min:0',
            'ada_sertifikat'     => 'boolean',
            'kecamatan'          => 'required|string|max:255',
            'kelurahan'          => 'required|string|max:255',
            'status_pasar'       => 'required|in:harian,mingguan',
            'jumlah_pedagang'    => 'required|integer|min:0',
            'ptt'                => 'required|integer|min:0',
            'kios'               => 'required|integer|min:0',
            'los'                => 'required|integer|min:0',
            'lapak'              => 'required|integer|min:0',
            'ruko'               => 'required|integer|min:0',
            'kondisi_baik'       => 'required|integer|min:0',
            'kondisi_rusak'      => 'required|integer|min:0',
            'kondisi_terpakai'   => 'required|integer|min:0',
            'kepala_pasar'       => 'required|integer|min:0',
            'petugas_kebersihan' => 'required|integer|min:0',
            'petugas_keamanan'   => 'required|integer|min:0',
            'retribusi'          => 'required|integer|min:0',
        ];
    }

    protected function editRules(): array
    {
        return [
            'editNamaPasar'         => 'required|string|max:255',
            'editImage'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'editTahunPembangunan'  => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),
            'editLuasLahan'         => 'nullable|numeric|min:0',
            'editAdaSertifikat'     => 'boolean',
            'editKecamatan'         => 'required|string|max:255',
            'editKelurahan'         => 'required|string|max:255',
            'editStatusPasar'       => 'required|in:harian,mingguan',
            'editJumlahPedagang'    => 'required|integer|min:0',
            'editPtt'               => 'required|integer|min:0',
            'editKios'              => 'required|integer|min:0',
            'editLos'               => 'required|integer|min:0',
            'editLapak'             => 'required|integer|min:0',
            'editRuko'              => 'required|integer|min:0',
            'editKondisiBaik'       => 'required|integer|min:0',
            'editKondisiRusak'      => 'required|integer|min:0',
            'editKondisiTerpakai'   => 'required|integer|min:0',
            'editKepalaPasar'       => 'required|integer|min:0',
            'editPetugasKebersihan' => 'required|integer|min:0',
            'editPetugasKeamanan'   => 'required|integer|min:0',
            'editRetribusi'         => 'required|integer|min:0',
        ];
    }

    // ─── Reset pagination on search ─────────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ─── Hapus preview gambar (create) ──────────────────────────────────────
    public function removeImage(): void
    {
        $this->image = null;
    }

    // ─── Hapus preview gambar (edit) ────────────────────────────────────────
    public function removeEditImage(): void
    {
        $this->editImage = null;
    }

    // ─── Muat data edit ke Livewire properties (dipanggil dari Alpine) ───────
    /**
     * Dipanggil via wire:call dari Alpine ketika tombol edit diklik,
     * hanya untuk sinkronisasi file-upload state (editImage/editImagePath).
     * Data teks sudah dikirim melalui Alpine dari JSON yang dirender saat mount.
     */
    public function prepareEdit(int $id): void
    {
        $pasar = Pasar::findOrFail($id);

        $this->editId               = $pasar->id;
        $this->editNamaPasar        = $pasar->nama_pasar;
        $this->editTahunPembangunan  = (string) ($pasar->tahun_pembangunan ?? '');
        $this->editLuasLahan        = (string) ($pasar->luas_lahan ?? '');
        $this->editAdaSertifikat    = (bool)   $pasar->ada_sertifikat;
        $this->editKecamatan        = $pasar->kecamatan;
        $this->editKelurahan        = $pasar->kelurahan;
        $this->editStatusPasar      = $pasar->status_pasar;
        $this->editJumlahPedagang   = (int) $pasar->jumlah_pedagang;
        $this->editPtt              = (int) $pasar->ptt;
        $this->editKios             = (int) $pasar->kios;
        $this->editLos              = (int) $pasar->los;
        $this->editLapak            = (int) $pasar->lapak;
        $this->editRuko             = (int) $pasar->ruko;
        $this->editKondisiBaik      = (int) $pasar->kondisi_baik;
        $this->editKondisiRusak     = (int) $pasar->kondisi_rusak;
        $this->editKondisiTerpakai  = (int) $pasar->kondisi_terpakai;
        $this->editKepalaPasar      = (int) $pasar->kepala_pasar;
        $this->editPetugasKebersihan = (int) $pasar->petugas_kebersihan;
        $this->editPetugasKeamanan  = (int) $pasar->petugas_keamanan;
        $this->editRetribusi        = (int) $pasar->retribusi;

        $this->editImage     = null;
        $this->editImagePath = $pasar->image;

        // Bersihkan error validasi sebelumnya
        $this->resetValidation();
    }

    // ─── Simpan data baru ────────────────────────────────────────────────────
    public function store(): void
    {
        $this->validate($this->createRules());

        $imagePath = $this->image
            ? Storage::disk('public')->putFile('pasars', $this->image)
            : null;

        Pasar::create([
            'nama_pasar'         => $this->nama_pasar,
            'image'              => $imagePath,
            'tahun_pembangunan'  => $this->tahun_pembangunan ?: null,
            'luas_lahan'         => $this->luas_lahan ?: null,
            'ada_sertifikat'     => $this->ada_sertifikat,
            'kecamatan'          => $this->kecamatan,
            'kelurahan'          => $this->kelurahan,
            'status_pasar'       => $this->status_pasar,
            'jumlah_pedagang'    => $this->jumlah_pedagang,
            'ptt'                => $this->ptt,
            'kios'               => $this->kios,
            'los'                => $this->los,
            'lapak'              => $this->lapak,
            'ruko'               => $this->ruko,
            'kondisi_baik'       => $this->kondisi_baik,
            'kondisi_rusak'      => $this->kondisi_rusak,
            'kondisi_terpakai'   => $this->kondisi_terpakai,
            'kepala_pasar'       => $this->kepala_pasar,
            'petugas_kebersihan' => $this->petugas_kebersihan,
            'petugas_keamanan'   => $this->petugas_keamanan,
            'retribusi'          => $this->retribusi,
        ]);

        $this->resetCreateForm();
        PasarsCacheHelper::refreshAll();
        $this->dispatch('pasar-saved');
        $this->dispatch('notify', type: 'success', message: 'Data pasar berhasil ditambahkan!');
    }

    // ─── Perbarui data ───────────────────────────────────────────────────────
    public function update(): void
    {
        $this->validate($this->editRules());

        $pasar = Pasar::findOrFail($this->editId);

        // Tangani gambar
        $imagePath = $pasar->image;
        if ($this->editImage) {
            // Hapus gambar lama setelah upload berhasil
            $newPath = Storage::disk('public')->putFile('pasars', $this->editImage);
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $newPath;
        }

        $pasar->update([
            'nama_pasar'         => $this->editNamaPasar,
            'image'              => $imagePath,
            'tahun_pembangunan'  => $this->editTahunPembangunan ?: null,
            'luas_lahan'         => $this->editLuasLahan ?: null,
            'ada_sertifikat'     => $this->editAdaSertifikat,
            'kecamatan'          => $this->editKecamatan,
            'kelurahan'          => $this->editKelurahan,
            'status_pasar'       => $this->editStatusPasar,
            'jumlah_pedagang'    => $this->editJumlahPedagang,
            'ptt'                => $this->editPtt,
            'kios'               => $this->editKios,
            'los'                => $this->editLos,
            'lapak'              => $this->editLapak,
            'ruko'               => $this->editRuko,
            'kondisi_baik'       => $this->editKondisiBaik,
            'kondisi_rusak'      => $this->editKondisiRusak,
            'kondisi_terpakai'   => $this->editKondisiTerpakai,
            'kepala_pasar'       => $this->editKepalaPasar,
            'petugas_kebersihan' => $this->editPetugasKebersihan,
            'petugas_keamanan'   => $this->editPetugasKeamanan,
            'retribusi'          => $this->editRetribusi,
        ]);

        $this->resetEditForm();
        PasarsCacheHelper::refreshAll();
        $this->dispatch('pasar-updated');
        $this->dispatch('notify', type: 'success', message: 'Data pasar berhasil diperbarui!');
    }

    // ─── Konfirmasi hapus ────────────────────────────────────────────────────
    public function confirmDelete(int $id): void
    {
        $pasar = Pasar::findOrFail($id);
        $this->deleteId      = $pasar->id;
        $this->deleteName    = $pasar->nama_pasar;
        $this->showDeleteModal = true;
    }

    // ─── Hapus data ──────────────────────────────────────────────────────────
    public function delete(): void
    {
        $pasar = Pasar::findOrFail($this->deleteId);

        if ($pasar->image && Storage::disk('public')->exists($pasar->image)) {
            Storage::disk('public')->delete($pasar->image);
        }

        $pasar->delete();

        PasarsCacheHelper::refreshAll();
        $this->reset(['deleteId', 'deleteName', 'showDeleteModal']);
        $this->dispatch('notify', type: 'success', message: 'Data pasar berhasil dihapus!');
    }

    // ─── Reset form ──────────────────────────────────────────────────────────
    private function resetCreateForm(): void
    {
        $this->reset([
            'nama_pasar', 'image', 'tahun_pembangunan', 'luas_lahan', 'ada_sertifikat',
            'kecamatan', 'kelurahan', 'jumlah_pedagang', 'kios', 'los', 'lapak', 'ruko',
            'kondisi_baik', 'kondisi_rusak', 'kondisi_terpakai',
            'kepala_pasar', 'petugas_kebersihan', 'petugas_keamanan', 'retribusi', 'ptt',
        ]);
        $this->status_pasar = 'harian';
        $this->resetValidation();
    }

    private function resetEditForm(): void
    {
        $this->reset([
            'editId', 'editNamaPasar', 'editImage', 'editImagePath',
            'editTahunPembangunan', 'editLuasLahan', 'editAdaSertifikat',
            'editKecamatan', 'editKelurahan',
            'editJumlahPedagang', 'editKios', 'editLos', 'editLapak', 'editRuko',
            'editKondisiBaik', 'editKondisiRusak', 'editKondisiTerpakai',
            'editKepalaPasar', 'editPetugasKebersihan', 'editPetugasKeamanan',
            'editRetribusi', 'editPtt',
        ]);
        $this->editStatusPasar = 'harian';
        $this->resetValidation();
    }

    // ─── Render ──────────────────────────────────────────────────────────────
    public function render()
    {
        $pasars = Pasar::query()
            ->when($this->search, fn ($q) =>
                $q->where('nama_pasar', 'like', '%' . $this->search . '%')
                  ->orWhere('kecamatan',  'like', '%' . $this->search . '%')
                  ->orWhere('kelurahan',  'like', '%' . $this->search . '%')
            )
            ->oldest('tahun_pembangunan')
            ->paginate($this->perPage);

        // ─── Totals Global dari Cache ─────────────────────────────────────
        $totals = PasarsCacheHelper::getGlobalTotals();
        $totalPasar = PasarsCacheHelper::getTotalPasar();

        return view('livewire.dashboard.sarana-prasarana', [
            'pasars'     => $pasars,
            'totalPasar' => $totalPasar,
            'totals'     => $totals,
        ])->layout('layouts.dashboard.main');
    }
}

