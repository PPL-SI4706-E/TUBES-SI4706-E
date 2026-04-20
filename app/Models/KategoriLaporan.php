<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriLaporan extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kategori', 'deskripsi', 'tarif', 'icon', 'is_active'];
    protected $table = 'kategori_laporan';

    protected $casts = [
        'tarif'     => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function laporans()
    {
        return $this->hasMany(Laporan::class, 'kategori_laporan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedTarifAttribute(): string
    {
        return 'Rp ' . number_format($this->tarif, 0, ',', '.');
    }
}