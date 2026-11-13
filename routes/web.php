<?php

use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\CkeditorController;
use App\Livewire\Dashboard\Categories;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Dashboard\PermissionIndex;
use App\Livewire\Dashboard\PendapatanPasarIndex;
use App\Livewire\Dashboard\Posts;
use App\Livewire\Dashboard\PostsCreate;
use App\Livewire\Dashboard\PostsEdit;
use App\Livewire\Dashboard\RoleIndex;
use App\Livewire\Dashboard\SaranaPrasarana;
use App\Livewire\Dashboard\Tags;
use App\Livewire\Dashboard\UserIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// ========== Frontend ==========
Route::get('/', function () {
    return redirect()->route('dashboard');
});
// ========= End Frontend ==========

// ======== Logout Route ==========
Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');


// =========== Dashboard Page ==========
Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// =========== Api Routes ==========
Route::get('api/categories', [DataController::class, 'categories']);
Route::get('api/tags', [DataController::class, 'tags']);
Route::post('api/tags', [DataController::class, 'storeTag']); // ubah jadi storeTag khusus POST



// ========= CkEditor Image Upload & Delete =========
Route::middleware(['auth','role:admin'])->group(function () {

        // Upload gambar CKEditor
        Route::post('/ckeditor/upload', function (Request $request) {
            if ($request->hasFile('upload')) {
                $path = $request->file('upload')->store('ckeditor', 'public');
                $url = Storage::url($path);
    
                return response()->json(['url' => $url]);
            }
    
            return response()->json(['error' => ['message' => 'Tidak ada file yang dikirim.']], 400);
        })->name('ckeditor.upload');
    
        // Hapus gambar dari storage
        Route::post('/ckeditor/delete', function (Request $request) {
            $url = $request->url;
    
            if (!$url) {
                return response()->json(['error' => 'URL tidak dikirim'], 400);
            }
    
            $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH));
    
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return response()->json(['success' => true]);
            }
    
            return response()->json(['error' => 'File tidak ditemukan'], 404);
        })->name('ckeditor.delete');
});
   
    
// ============= Dashboard Prefix =============
Route::prefix('dashboard')->name('dashboard.')->middleware(['auth'])->group(function () {
    // ========== Posts ==========
    // Route::get('/posts', Posts::class)->name('posts.index');
    // Route::get('/posts/create', PostsCreate::class)->name('posts.create');
    // Route::get('/posts/{slug}/edit', PostsEdit::class)->name('posts.edit');

    // ========= Tags & Categories ==========
    // Route::get('/tags', Tags::class)->name('tags');
    // Route::get('/categories', Categories::class)->name('categories');

    // ========= Sarana Prasarana & Pendapatan Pasar ==========
    Route::get('/sarana-prasarana', SaranaPrasarana::class)->name('sarana-prasarana');
    Route::get('/pendapatan-pasar', PendapatanPasarIndex::class)->name('pendapatan-pasar');

        /**
         * SETTINGS (Hanya bisa diakses oleh role admin)
         */
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/roles', RoleIndex::class)->name('roles.index');
            Route::get('/permissions', PermissionIndex::class)->name('permissions.index');
            Route::get('/users', UserIndex::class)->name('users.index');
        });

});



Route::view('profile', 'livewire.dashboard.profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
