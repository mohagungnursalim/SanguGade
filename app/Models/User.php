<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pasar_id',
        'name',
        'email',
        'password',
    ];

    /**
     * Relasi ke Pasar yang dimounting ke user ini
     */
    public function pasar()
    {
        return $this->belongsTo(Pasar::class, 'pasar_id');
    }

    public function isOnline(): bool
    {
        return Cache::has('user-online:' . $this->id);
    }

    public function lastSeenLabel(): string
    {
        if ($this->isOnline()) {
            return 'Sedang online';
        }

        if (!$this->last_seen_at) {
            return 'Belum pernah terlihat';
        }

        return 'Terakhir dilihat ' . $this->last_seen_at->diffForHumans();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
