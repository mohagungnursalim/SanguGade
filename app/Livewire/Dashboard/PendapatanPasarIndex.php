<?php
namespace App\Livewire\Dashboard;

use App\Helpers\PasarsCacheHelper;
use App\Helpers\PendapatanCacheHelper;
use App\Helpers\TerbilangHelper;
use App\Models\PendapatanPasar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class PendapatanPasarIndex extends Component
{
    use WithFileUploads;

    // ─── Filter & Search ──────────────────────────────────────────────────
    public string $search = '';

    public ?string $filterPasar = null;

    public ?string $filterTahun = null;

    public ?string $filterStartDate = null;

    public ?string $filterEndDate = null;

    public ?string $filterJenis = null;

    public int $perPage = 10;

    // ─── State Delete ─────────────────────────────────────────────────────
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;
    public string $deleteInfo = '';

    // ─── State Form Create ────────────────────────────────────────────────
    public ?int $pasar_id = null;
    public ?int $tahun_ssrd = null;
    public string $no_rekening_bank = '2501300007827';
    public string $masa_retribusi = '';
    public ?int $tahun_retribusi = null;
    public string $nomor_urut_ssrd = '';
    public string $jenis_retribusi = 'SKRD';
    public string $nomor_ayat = '01.02.2002';
    public $jumlah_retribusi = 0;
    public string $terbilang = '';
    public string $tanggal_penyetoran = '';
    public string $nama_penyetor = '';
    public string $petugas_bank = '';
    public string $keterangan = '';
    public string $keterangan_lainnya = '';
    public $bukti_ssrd = null;

    // ─── State Form Edit ──────────────────────────────────────────────────
    public ?int $editId = null;
    public ?int $edit_pasar_id = null;
    public ?int $edit_tahun_ssrd = null;
    public string $edit_no_rekening_bank = '2501300007827';
    public string $edit_masa_retribusi = '';
    public ?int $edit_tahun_retribusi = null;
    public string $edit_nomor_urut_ssrd = '';
    public string $edit_jenis_retribusi = 'SKRD';
    public string $edit_nomor_ayat = '01.02.2002';
    public $edit_jumlah_retribusi = 0;
    public string $edit_terbilang = '';
    public string $edit_tanggal_penyetoran = '';
    public string $edit_nama_penyetor = '';
    public string $edit_petugas_bank = '';
    public string $edit_keterangan = '';
    public string $edit_keterangan_lainnya = '';
    public $edit_bukti_ssrd = null;
    public ?string $edit_bukti_ssrd_path = null;

    // ─── State Modal Detail Pasar ─────────────────────────────────────────
    public bool $showDetailModal = false;

    public ?int $detailPasarId = null;

    public function mount(): void
    {
        $currentYear = (int) date('Y');

        $this->tahun_ssrd = $currentYear;
        $this->tahun_retribusi = $currentYear;
        $this->tanggal_penyetoran = date('Y-m-d');
        $this->terbilang = TerbilangHelper::make(0);

        if ($this->isOperator()) {
            $this->pasar_id = auth()->user()->pasar_id;
        }

        $this->nomor_urut_ssrd = $this->generateNomorUrutSsrD();
    }

    public function updatedPasarId(): void
    {
        $this->nomor_urut_ssrd = $this->generateNomorUrutSsrD();
    }

    public function updatedTahunSsrd(): void
    {
        $this->nomor_urut_ssrd = $this->generateNomorUrutSsrD();
    }

    // ─── Load More ────────────────────────────────────────────────────────

    public function loadMore(): void
    {
        $this->perPage += 10;
    }

    private function resetLoadMore(): void
    {
        $this->perPage = 10;
    }

    // ─── Filter & Search Updates ──────────────────────────────────────────

    public function updatingSearch(): void
    {
        $this->resetLoadMore();
    }

    public function updatingFilterPasar(): void
    {
        $this->resetLoadMore();
    }

    public function updatingFilterTahun(): void
    {
        $this->resetLoadMore();
    }

    public function updatingFilterStartDate(): void
    {
        $this->resetLoadMore();
    }

    public function updatingFilterEndDate(): void
    {
        $this->resetLoadMore();
    }

    public function updatingFilterJenis(): void
    {
        $this->resetLoadMore();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filterPasar',
            'filterTahun',
            'filterStartDate',
            'filterEndDate',
            'filterJenis'
        ]);

        $this->resetLoadMore();
    }


    // ─── Validation Rules ─────────────────────────────────────────────────

    protected function createRules(): array
    {
        return [
            'pasar_id'           => 'required|exists:pasars,id',
            'tahun_ssrd'         => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'no_rekening_bank'   => 'nullable|string|max:50',
            'masa_retribusi'     => 'nullable|string|max:100',
            'tahun_retribusi'    => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'nomor_urut_ssrd'    => 'nullable|string|max:50',
            'jenis_retribusi'    => 'required|string|max:50',
            'nomor_ayat'         => 'nullable|string|max:50',
            'jumlah_retribusi'   => 'required|numeric|min:1000',
            'tanggal_penyetoran' => 'required|date',
            'nama_penyetor'      => 'required|string|max:150',
            'petugas_bank'       => 'nullable|string|max:150',
            'keterangan'         => 'required|string',
            'keterangan_lainnya' => 'required_if:keterangan,Lainnya|nullable|string',
            'bukti_ssrd'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    protected function createMessages(): array
    {
        return [
            'pasar_id.required' => 'Pasar wajib dipilih.',
            'pasar_id.exists' => 'Pasar yang dipilih tidak valid.',

            'tahun_ssrd.required' => 'Tahun SSRD wajib diisi.',
            'tahun_ssrd.integer' => 'Tahun SSRD harus berupa angka.',
            'tahun_ssrd.digits' => 'Tahun SSRD harus terdiri dari 4 digit.',
            'tahun_ssrd.min' => 'Tahun SSRD minimal 1900.',
            'tahun_ssrd.max' => 'Tahun SSRD maksimal ' . (date('Y') + 5) . '.',

            'no_rekening_bank.string' => 'Nomor rekening bank harus berupa teks.',
            'no_rekening_bank.max' => 'Nomor rekening bank maksimal 50 karakter.',

            'masa_retribusi.string' => 'Masa retribusi harus berupa teks.',
            'masa_retribusi.max' => 'Masa retribusi maksimal 100 karakter.',

            'tahun_retribusi.required' => 'Tahun retribusi wajib diisi.',
            'tahun_retribusi.integer' => 'Tahun retribusi harus berupa angka.',
            'tahun_retribusi.digits' => 'Tahun retribusi harus terdiri dari 4 digit.',
            'tahun_retribusi.min' => 'Tahun retribusi minimal 1900.',
            'tahun_retribusi.max' => 'Tahun retribusi maksimal ' . (date('Y') + 5) . '.',

            'nomor_urut_ssrd.string' => 'Nomor urut SSRD harus berupa teks.',
            'nomor_urut_ssrd.max' => 'Nomor urut SSRD maksimal 50 karakter.',

            'jenis_retribusi.required' => 'Jenis retribusi wajib diisi.',
            'jenis_retribusi.string' => 'Jenis retribusi harus berupa teks.',
            'jenis_retribusi.max' => 'Jenis retribusi maksimal 50 karakter.',

            'nomor_ayat.string' => 'Nomor ayat harus berupa teks.',
            'nomor_ayat.max' => 'Nomor ayat maksimal 50 karakter.',

            'jumlah_retribusi.required' => 'Jumlah retribusi wajib diisi.',
            'jumlah_retribusi.numeric' => 'Jumlah retribusi harus berupa angka.',
            'jumlah_retribusi.min' => 'Jumlah retribusi minimal Rp1.000.',

            'tanggal_penyetoran.required' => 'Tanggal penyetoran wajib diisi.',
            'tanggal_penyetoran.date' => 'Tanggal penyetoran tidak valid.',

            'nama_penyetor.required' => 'Nama penyetor wajib diisi.',
            'nama_penyetor.string' => 'Nama penyetor harus berupa teks.',
            'nama_penyetor.max' => 'Nama penyetor maksimal 150 karakter.',

            'petugas_bank.string' => 'Nama petugas bank harus berupa teks.',
            'petugas_bank.max' => 'Nama petugas bank maksimal 150 karakter.',

            'keterangan.required' => 'Keterangan wajib dipilih.',
            'keterangan.string' => 'Keterangan harus berupa teks.',

            'keterangan_lainnya.required_if' => 'Keterangan lainnya wajib diisi.',
            'keterangan_lainnya.string' => 'Keterangan lainnya harus berupa teks.',
            'bukti_ssrd.image' => 'Bukti SSRD harus berupa gambar.',
            'bukti_ssrd.mimes' => 'Format bukti SSRD harus JPG, JPEG, PNG, atau WEBP.',
            'bukti_ssrd.max' => 'Ukuran bukti SSRD maksimal 5 MB.',
        ];
    }

    protected function editRules(): array
    {
        return [
            'editId'                  => 'required|exists:pendapatan_pasars,id',
            'edit_pasar_id'           => 'required|exists:pasars,id',
            'edit_tahun_ssrd'         => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'edit_no_rekening_bank'   => 'nullable|string|max:50',
            'edit_masa_retribusi'     => 'nullable|string|max:100',
            'edit_tahun_retribusi'    => 'required|integer|digits:4|min:1900|max:' . (date('Y') + 5),
            'edit_nomor_urut_ssrd'    => 'nullable|string|max:50',
            'edit_jenis_retribusi'    => 'required|string|max:50',
            'edit_nomor_ayat'         => 'nullable|string|max:50',
            'edit_jumlah_retribusi'   => 'required|numeric|min:1000',
            'edit_tanggal_penyetoran' => 'required|date',
            'edit_nama_penyetor'      => 'required|string|max:150',
            'edit_petugas_bank'       => 'nullable|string|max:150',
            'edit_keterangan'         => 'required|string',
            'edit_keterangan_lainnya' => 'required_if:edit_keterangan,Lainnya|nullable|string',
            'edit_bukti_ssrd'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    protected function editMessages(): array
    {
        return [
            'editId.required' => 'Data SSRD yang akan diubah tidak valid.',
            'editId.exists' => 'Data SSRD tidak ditemukan.',

            'edit_pasar_id.required' => 'Pasar wajib dipilih.',
            'edit_pasar_id.exists' => 'Pasar yang dipilih tidak valid.',

            'edit_tahun_ssrd.required' => 'Tahun SSRD wajib diisi.',
            'edit_tahun_ssrd.integer' => 'Tahun SSRD harus berupa angka.',
            'edit_tahun_ssrd.digits' => 'Tahun SSRD harus terdiri dari 4 digit.',
            'edit_tahun_ssrd.min' => 'Tahun SSRD minimal 1900.',
            'edit_tahun_ssrd.max' => 'Tahun SSRD maksimal ' . (date('Y') + 5) . '.',

            'edit_no_rekening_bank.string' => 'Nomor rekening bank harus berupa teks.',
            'edit_no_rekening_bank.max' => 'Nomor rekening bank maksimal 50 karakter.',

            'edit_masa_retribusi.string' => 'Masa retribusi harus berupa teks.',
            'edit_masa_retribusi.max' => 'Masa retribusi maksimal 100 karakter.',

            'edit_tahun_retribusi.required' => 'Tahun retribusi wajib diisi.',
            'edit_tahun_retribusi.integer' => 'Tahun retribusi harus berupa angka.',
            'edit_tahun_retribusi.digits' => 'Tahun retribusi harus terdiri dari 4 digit.',
            'edit_tahun_retribusi.min' => 'Tahun retribusi minimal 1900.',
            'edit_tahun_retribusi.max' => 'Tahun retribusi maksimal ' . (date('Y') + 5) . '.',

            'edit_nomor_urut_ssrd.string' => 'Nomor urut SSRD harus berupa teks.',
            'edit_nomor_urut_ssrd.max' => 'Nomor urut SSRD maksimal 50 karakter.',

            'edit_jenis_retribusi.required' => 'Jenis retribusi wajib diisi.',
            'edit_jenis_retribusi.string' => 'Jenis retribusi harus berupa teks.',
            'edit_jenis_retribusi.max' => 'Jenis retribusi maksimal 50 karakter.',

            'edit_nomor_ayat.string' => 'Nomor ayat harus berupa teks.',
            'edit_nomor_ayat.max' => 'Nomor ayat maksimal 50 karakter.',

            'edit_jumlah_retribusi.required' => 'Jumlah retribusi wajib diisi.',
            'edit_jumlah_retribusi.numeric' => 'Jumlah retribusi harus berupa angka.',
            'edit_jumlah_retribusi.min' => 'Jumlah retribusi minimal Rp1.000.',

            'edit_tanggal_penyetoran.required' => 'Tanggal penyetoran wajib diisi.',
            'edit_tanggal_penyetoran.date' => 'Tanggal penyetoran tidak valid.',

            'edit_nama_penyetor.required' => 'Nama penyetor wajib diisi.',
            'edit_nama_penyetor.string' => 'Nama penyetor harus berupa teks.',
            'edit_nama_penyetor.max' => 'Nama penyetor maksimal 150 karakter.',

            'edit_petugas_bank.string' => 'Nama petugas bank harus berupa teks.',
            'edit_petugas_bank.max' => 'Nama petugas bank maksimal 150 karakter.',

            'edit_keterangan.required' => 'Keterangan wajib dipilih.',
            'edit_keterangan.string' => 'Keterangan harus berupa teks.',

            'edit_keterangan_lainnya.required_if' => 'Keterangan lainnya wajib diisi.',
            'edit_keterangan_lainnya.string' => 'Keterangan lainnya harus berupa teks.',
            'edit_bukti_ssrd.image' => 'Bukti SSRD harus berupa gambar.',
            'edit_bukti_ssrd.mimes' => 'Format bukti SSRD harus JPG, JPEG, PNG, atau WEBP.',
            'edit_bukti_ssrd.max' => 'Ukuran bukti SSRD maksimal 5 MB.',
        ];
    }


    // ─── CRUD Actions ─────────────────────────────────────────────────────

    public function store(): void
    {
        abort_if(!auth()->user()->can('create'), 403, 'Anda tidak memiliki hak akses untuk menambah data SSRD.');

        if ($this->isOperator()) {
            abort_unless(auth()->user()->pasar_id, 403, 'Akun operator belum ditugaskan ke pasar.');
            $this->pasar_id = (int) auth()->user()->pasar_id;
        }

        $this->nomor_urut_ssrd = $this->generateNomorUrutSsrD();

        $this->validate($this->createRules(), $this->createMessages());

        $buktiPath = $this->storeBuktiSsrd($this->bukti_ssrd);

        DB::transaction(function () use ($buktiPath) {
            $terbilangText = TerbilangHelper::make($this->jumlah_retribusi);

            PendapatanPasar::create([
                'pasar_id'           => $this->pasar_id,
                'user_id'            => auth()->id(),
                'tahun_ssrd'         => $this->tahun_ssrd,
                'no_rekening_bank'   => $this->no_rekening_bank,
                'masa_retribusi'     => $this->masa_retribusi,
                'tahun_retribusi'    => $this->tahun_retribusi,
                'nomor_urut_ssrd'    => $this->nomor_urut_ssrd,
                'jenis_retribusi'    => $this->jenis_retribusi,
                'nomor_ayat'         => $this->nomor_ayat,
                'jumlah_retribusi'   => $this->jumlah_retribusi,
                'terbilang'          => $terbilangText,
                'tanggal_penyetoran' => $this->tanggal_penyetoran,
                'nama_penyetor'      => $this->nama_penyetor,
                'petugas_bank'       => $this->petugas_bank,
                'keterangan'         => $this->keterangan === 'Lainnya'
                    ? $this->keterangan_lainnya
                    : $this->keterangan,
                'bukti_ssrd'         => $buktiPath,
            ]);
        });

        $this->resetCreateForm();
        PendapatanCacheHelper::refreshAll();

        $this->dispatch('ssrd-saved');
        $this->dispatch('notify', type: 'success', message: 'Data SSRD Pendapatan Pasar berhasil disimpan!');
    }

    public function update(): void
    {
        abort_if(!auth()->user()->can('update'), 403, 'Anda tidak memiliki hak akses untuk memperbarui data SSRD.');

        $item = $this->accessiblePendapatanQuery()->findOrFail($this->editId);

        if ($this->isOperator()) {
            abort_unless(auth()->user()->pasar_id, 403, 'Akun operator belum ditugaskan ke pasar.');
            $this->edit_pasar_id = (int) auth()->user()->pasar_id;
        }

        $this->edit_nomor_urut_ssrd = $this->edit_nomor_urut_ssrd ?: $this->generateNomorUrutSsrD($this->edit_pasar_id, $this->edit_tahun_ssrd);

        $this->validate($this->editRules(), $this->editMessages());

        $oldBuktiPath = $item->bukti_ssrd;
        $buktiPath = $this->edit_bukti_ssrd
            ? $this->storeBuktiSsrd($this->edit_bukti_ssrd)
            : $oldBuktiPath;

        DB::transaction(function () use ($item, $buktiPath) {
            $terbilangText = TerbilangHelper::make($this->edit_jumlah_retribusi);

            $item->update([
                'pasar_id'           => $this->edit_pasar_id,
                'tahun_ssrd'         => $this->edit_tahun_ssrd,
                'no_rekening_bank'   => $this->edit_no_rekening_bank,
                'masa_retribusi'     => $this->edit_masa_retribusi,
                'tahun_retribusi'    => $this->edit_tahun_retribusi,
                'nomor_urut_ssrd'    => $this->edit_nomor_urut_ssrd,
                'jenis_retribusi'   => $this->edit_jenis_retribusi,
                'nomor_ayat'         => $this->edit_nomor_ayat,
                'jumlah_retribusi'   => $this->edit_jumlah_retribusi,
                'terbilang'          => $terbilangText,
                'tanggal_penyetoran' => $this->edit_tanggal_penyetoran,
                'nama_penyetor'      => $this->edit_nama_penyetor,
                'petugas_bank'       => $this->edit_petugas_bank,
                'keterangan'         => $this->edit_keterangan === 'Lainnya'
                    ? $this->edit_keterangan_lainnya
                    : $this->edit_keterangan,
                'bukti_ssrd'         => $buktiPath,
            ]);
        });

        if ($this->edit_bukti_ssrd && $oldBuktiPath) {
            Storage::disk('public')->delete($oldBuktiPath);
        }

        $this->resetEditForm();
        PendapatanCacheHelper::refreshAll();

        $this->dispatch('ssrd-updated');
        $this->dispatch('notify', type: 'success', message: 'Data SSRD Pendapatan Pasar berhasil diperbarui!');
    }

    public function confirmDelete(int $id): void
    {
        abort_if(!auth()->user()->can('delete'), 403, 'Anda tidak memiliki hak akses untuk menghapus data SSRD.');

        $item = $this->accessiblePendapatanQuery()->with('pasar')->findOrFail($id);

        $this->deleteId = $item->id;

        $this->deleteInfo = 'SSRD No: ' . ($item->nomor_urut_ssrd ?: '-') . ' (' . ($item->pasar->nama_pasar ?? '-') . ' - Rp ' . number_format($item->jumlah_retribusi, 0, ',', '.') . ')';

        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        abort_if(!auth()->user()->can('delete'), 403, 'Anda tidak memiliki hak akses untuk menghapus data SSRD.');

        if ($this->deleteId) {
            $item = $this->accessiblePendapatanQuery()->findOrFail($this->deleteId);
            $buktiPath = $item->bukti_ssrd;

            DB::transaction(function () {
                $item = $this->accessiblePendapatanQuery()->findOrFail($this->deleteId);
                $item->delete();
            });

            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }

            $this->reset(['deleteId', 'deleteInfo', 'showDeleteModal']);

            PendapatanCacheHelper::refreshAll();

            $this->dispatch('notify', type: 'success', message: 'Data SSRD berhasil dihapus!');
        }
    }

    // ─── Detail Modal Pasar ───────────────────────────────────────────────

    public function viewPasarDetail(int $pasarId): void
    {
        abort_unless(auth()->user()->can('read'), 403, 'Anda tidak memiliki hak akses untuk melihat data SSRD.');

        $this->detailPasarId = $pasarId;
        $this->showDetailModal = true;
    }

    public function closePasarDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailPasarId = null;
    }

    public function removeBuktiSsrd(): void
    {
        $this->bukti_ssrd = null;
    }

    public function removeEditBuktiSsrd(): void
    {
        $this->edit_bukti_ssrd = null;
    }

    // ─── Reset Forms ──────────────────────────────────────────────────────

    private function resetCreateForm(): void
    {
        $currentYear = (int) date('Y');

        $this->reset([
            'pasar_id', 'no_rekening_bank', 'masa_retribusi',
            'nomor_urut_ssrd', 'nomor_ayat', 'jumlah_retribusi',
            'nama_penyetor', 'petugas_bank', 'keterangan', 'keterangan_lainnya',
            'bukti_ssrd'
        ]);

        $this->tahun_ssrd = $currentYear;
        $this->tahun_retribusi = $currentYear;
        $this->jenis_retribusi = 'SKRD';
        $this->tanggal_penyetoran = date('Y-m-d');
        $this->terbilang = TerbilangHelper::make(0);
        if ($this->isOperator()) {
            $this->pasar_id = auth()->user()->pasar_id;
        }

        $this->nomor_urut_ssrd = $this->generateNomorUrutSsrD();
        $this->resetValidation();
    }

    private function resetEditForm(): void
    {
        $this->reset([
            'editId', 'edit_pasar_id', 'edit_tahun_ssrd', 'edit_no_rekening_bank',
            'edit_masa_retribusi', 'edit_tahun_retribusi', 'edit_nomor_urut_ssrd',
            'edit_nomor_ayat', 'edit_jumlah_retribusi', 'edit_terbilang',
            'edit_tanggal_penyetoran', 'edit_nama_penyetor', 'edit_petugas_bank',
            'edit_keterangan', 'edit_keterangan_lainnya',
            'edit_bukti_ssrd', 'edit_bukti_ssrd_path'
        ]);

        $this->edit_jenis_retribusi = 'SKRD';

        $this->resetValidation();
    }

    private function storeBuktiSsrd(?TemporaryUploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        $path = $file->store('pendapatan-pasar', 'public');

        if (!$path) {
            throw new \RuntimeException('Gagal menyimpan file bukti SSRD.');
        }

        return $path;
    }

    private function generateNomorUrutSsrD(?int $pasarId = null, ?int $tahunSsrd = null): string
    {
        $pasarId ??= $this->pasar_id;
        $tahunSsrd ??= $this->tahun_ssrd;

        if (!$pasarId || !$tahunSsrd) {
            return '';
        }

        $maxSequence = 0;

        $records = PendapatanPasar::query()
            ->where('pasar_id', $pasarId)
            ->where('tahun_ssrd', $tahunSsrd)
            ->whereNotNull('nomor_urut_ssrd')
            ->where('nomor_urut_ssrd', '!=', '')
            ->select('nomor_urut_ssrd')
            ->get();

        foreach ($records as $record) {
            $nomor = trim((string) $record->nomor_urut_ssrd);

            if (preg_match('/^(\d+)\b/', $nomor, $matches)) {
                $maxSequence = max($maxSequence, (int) $matches[1]);
            }
        }

        return sprintf('%03d/SSRD/%d', $maxSequence + 1, $tahunSsrd);
    }

    private function accessiblePendapatanQuery(): Builder
    {
        $user = auth()->user();
        $query = PendapatanPasar::query();

        if ($this->isOperator()) {
            if (!$user->pasar_id) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('pasar_id', $user->pasar_id);
            }
        } elseif (!$this->isAdmin() && !$this->isOpd()) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function cachePasarScope(): ?int
    {
        $user = auth()->user();

        return $this->isOperator() ? ($user->pasar_id ? (int) $user->pasar_id : -1) : null;
    }

    private function isOperator(): bool
    {
        $user = auth()->user();

        return $user->hasRole('operator') && !$this->isAdmin();
    }

    private function isAdmin(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    private function isOpd(): bool
    {
        return auth()->user()->hasRole('opd') && !$this->isAdmin();
    }

    // ─── Render ───────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();
        if (!$user->can('read')) {
            $pasars = PasarsCacheHelper::getAllPasar();
            if ($this->isOperator()) {
                $pasars = $user->pasar_id
                    ? $pasars->where('id', $user->pasar_id)->values()
                    : collect();
            }

            return view('livewire.dashboard.pendapatan-pasar-index', [
                'ssrdList' => collect(),
                'hasMorePages' => false,
                'pasars' => $pasars,
                'namaPasarTerpilih' => null,
                'totalNominalFiltered' => 0,
                'totalTransaksiFiltered' => 0,
                'totalNominalAll' => 0,
                'totalTransaksiAll' => 0,
                'ringkasanPerPasar' => collect(),
                'detailPasar' => null,
                'detailRiwayat' => collect(),
                'tahunList' => [(int) date('Y')],
            ])->layout('layouts.dashboard.main');
        }

        $ownerId = ($this->isAdmin() || $this->isOperator() || $this->isOpd()) ? null : (int) $user->id;
        $pasarScope = $this->cachePasarScope();

        // Query List Pendapatan SSRD
        $query = $this->accessiblePendapatanQuery()->with('pasar')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';

                $q->where(function ($sub) use ($term) {
                    $sub->where('nomor_urut_ssrd', 'like', $term)
                        ->orWhere('no_rekening_bank', 'like', $term)
                        ->orWhere('nama_penyetor', 'like', $term)
                        ->orWhere('petugas_bank', 'like', $term)
                        ->orWhere('nomor_ayat', 'like', $term)
                        ->orWhereHas('pasar', fn ($p) => $p->where('nama_pasar', 'like', $term));
                });
            })
            ->when($this->filterPasar, fn ($q) => $q->where('pasar_id', $this->filterPasar))
            ->when($this->filterTahun, fn ($q) => $q->where(function ($sub) {
                $sub->where('tahun_ssrd', $this->filterTahun)
                    ->orWhere('tahun_retribusi', $this->filterTahun);
            }))
            ->when($this->filterJenis, fn ($q) => $q->where('jenis_retribusi', $this->filterJenis))
            ->when($this->filterStartDate, fn ($q) => $q->whereDate('tanggal_penyetoran', '>=', $this->filterStartDate))
            ->when($this->filterEndDate, fn ($q) => $q->whereDate('tanggal_penyetoran', '<=', $this->filterEndDate))
            ->latest('tanggal_penyetoran')
            ->latest('id');

        // Load More: Ambil satu data tambahan untuk mendeteksi data berikutnya
        $ssrdList = (clone $query)
            ->take($this->perPage + 1)
            ->get();

        $hasMorePages = $ssrdList->count() > $this->perPage;

        if ($hasMorePages) {
            $ssrdList = $ssrdList->take($this->perPage);
        }

        $filteredTotals = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as total_transaksi, COALESCE(SUM(jumlah_retribusi), 0) as total_nominal')
            ->first();

        $totalNominalFiltered = (float) $filteredTotals->total_nominal;
        $totalTransaksiFiltered = (int) $filteredTotals->total_transaksi;

        // Pasar operator dibatasi pada pasar yang ditugaskan kepadanya.
        $pasars = PasarsCacheHelper::getAllPasar();
        if ($this->isOperator()) {
            $pasars = $user->pasar_id
                ? $pasars->where('id', $user->pasar_id)->values()
                : collect();
        }

        // Dapatkan Nama Pasar yang difilter (jika ada)
        $namaPasarTerpilih = null;

        if ($this->filterPasar) {
            $pasarTerpilih = $pasars->firstWhere('id', $this->filterPasar);

            if ($pasarTerpilih) {
                $namaPasarTerpilih = $pasarTerpilih->nama_pasar;
            }
        }

        // Data Ringkasan Per Pasar dari Cache Global Pendapatan
        $ringkasanPerPasar = $pasarScope === -1
            ? collect()
            : PendapatanCacheHelper::getRingkasanPerPasar($ownerId, $pasarScope);

        // Detail memakai ringkasan yang sudah dimuat, tanpa query pasar/agregasi tambahan.
        $detailPasar = $this->detailPasarId
            ? $ringkasanPerPasar->firstWhere('id', (int) $this->detailPasarId)
            : null;
        $detailRiwayat = collect();

        if ($detailPasar) {
            $detailRiwayat = $this->accessiblePendapatanQuery()
                ->where('pasar_id', $this->detailPasarId)
                ->latest('tanggal_penyetoran')
                ->latest('id')
                ->take(50)
                ->get();
        }

        // List Tahun dari Cache
        $tahunList = $pasarScope === -1
            ? [(int) date('Y')]
            : PendapatanCacheHelper::getTahunList($ownerId, $pasarScope);

        $allTimeTotals = $pasarScope === -1
            ? ['total_nominal' => 0.0, 'total_transaksi' => 0]
            : PendapatanCacheHelper::getAllTimeTotals($ownerId, $pasarScope);

        return view('livewire.dashboard.pendapatan-pasar-index', [
            'ssrdList'               => $ssrdList,
            'hasMorePages'           => $hasMorePages,
            'pasars'                 => $pasars,
            'namaPasarTerpilih'      => $namaPasarTerpilih,
            'totalNominalFiltered'   => $totalNominalFiltered,
            'totalTransaksiFiltered' => $totalTransaksiFiltered,
            'totalNominalAll'        => $allTimeTotals['total_nominal'],
            'totalTransaksiAll'      => $allTimeTotals['total_transaksi'],
            'ringkasanPerPasar'      => $ringkasanPerPasar,
            'detailPasar'            => $detailPasar,
            'detailRiwayat'          => $detailRiwayat,
            'tahunList'              => $tahunList,
        ])->layout('layouts.dashboard.main');
    }
}
