<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'phone', 'avatar', 'is_active', 'wilayah_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // Relationships
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function laporans()
    {
        return $this->hasMany(Laporan::class);
    }

    public function penugasanSebagaiPetugas()
    {
        return $this->hasMany(Penugasan::class, 'petugas_id');
    }

    public function penugasanSebagaiAdmin()
    {
        return $this->hasMany(Penugasan::class, 'admin_id');
    }

    public function ulasans()
    {
        return $this->hasMany(Ulasan::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function pengumumans()
    {
        return $this->hasMany(Pengumuman::class);
    }

    // Scopes
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helpers
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isPetugas(): bool    { return $this->role === 'petugas'; }
    public function isMasyarakat(): bool { return $this->role === 'masyarakat'; }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'      => 'Admin',
            'petugas'    => 'Petugas',
            'masyarakat' => 'Masyarakat',
            default      => $this->role,
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0ea5e9&color=fff';
    }

    public function getAverageRatingAttribute(): ?float
    {
        if ($this->role !== 'petugas') return null;
        return $this->penugasanSebagaiPetugas()
            ->whereHas('ulasan')
            ->with('ulasan')
            ->get()
            ->flatMap(fn($p) => $p->ulasan ? [$p->ulasan->rating] : [])
            ->avg();
    }
}