<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Tag;
use App\Helpers\TagsCacheHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Url;

class Tags extends Component
{
    public $name, $showModalAdd;
    public $editId, $editName, $showEditModal;
    public $deleteId, $deleteName, $showDeleteModal;
    public $tags = [], $totalTags;

    #[Url()]
    public $search;

    public $limit = 5;
    public $loaded = false;
    public $ttl = 60 * 60 * 24 * 7; // 1 minggu


    public function mount()
    {
        // Ambil total tags dari helper (cached, versioned)
        $this->totalTags = TagsCacheHelper::getTotalTags();

        // Cek apakah daftar tags sudah ada di cache (versioned key)
        $key = TagsCacheHelper::vKey('list_' . md5((string) $this->search) . '_' . $this->limit);
        if (Cache::has($key)) {
            $this->tags   = Cache::get($key);
            $this->loaded = true;
        }
    }


    public function updatingSearch()
    {
        $this->limit = 5;
    }


    public function updatedSearch()
    {
        $this->loadInitialTags();
    }


    public function loadInitialTags()
    {
        // Versioned key — otomatis stale setelah refreshAll() / invalidate()
        $key = TagsCacheHelper::vKey('list_' . md5((string) $this->search) . '_' . $this->limit);

        $this->tags = Cache::remember($key, $this->ttl, function () {
            return Tag::where('name', 'like', '%' . $this->search . '%')
                ->latest()
                ->take($this->limit)
                ->get();
        });

        $this->loaded = true;
    }


    public function loadMore()
    {
        $this->limit += 5;
        $this->loadInitialTags();
    }


    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        Tag::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ]);

        // Invalidasi cache (versioned — O(1)) lalu reload
        TagsCacheHelper::refreshAll();
        $this->totalTags = TagsCacheHelper::getTotalTags();
        $this->loadInitialTags();

        $this->reset(['name', 'showModalAdd']);
        $this->dispatch('addSuccess', [
            'message' => 'Tag successfully created.',
        ]);
    }


    public function edit($id)
    {
        $tag = Tag::findOrFail($id);
        $this->editId   = $tag->id;
        $this->editName = $tag->name;
        $this->showEditModal = true;
    }


    public function update()
    {
        $this->validate([
            'editName' => 'required|string|max:255|unique:tags,name,' . $this->editId,
        ]);

        $tag = Tag::findOrFail($this->editId);
        $tag->update([
            'name' => $this->editName,
            'slug' => Str::slug($this->editName),
        ]);

        // Invalidasi cache (versioned — O(1)) lalu reload
        TagsCacheHelper::refreshAll();
        $this->totalTags = TagsCacheHelper::getTotalTags();
        $this->loadInitialTags();

        $this->reset(['editId', 'editName', 'showEditModal']);
        $this->dispatch('updateSuccess', [
            'message' => 'Tag successfully updated.',
        ]);
    }


    public function confirmDelete($id)
    {
        $tag = Tag::findOrFail($id);
        $this->deleteId   = $tag->id;
        $this->deleteName = $tag->name;
        $this->showDeleteModal = true;
    }


    public function delete()
    {
        $tag = Tag::findOrFail($this->deleteId);
        $tag->delete();

        // Invalidasi cache (versioned — O(1)) lalu reload
        TagsCacheHelper::refreshAll();
        $this->totalTags = TagsCacheHelper::getTotalTags();
        $this->loadInitialTags();

        $this->reset(['deleteId', 'deleteName', 'showDeleteModal']);
        $this->dispatch('deleteSuccess', [
            'message' => 'Tag successfully deleted.',
        ]);
    }


    public function render()
    {
        return view('livewire.dashboard.tags')
            ->layout('layouts.dashboard.main');
    }
}
