<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $fillable = ['nama_wilayah', 'tipe', 'kode_wilayah'];
    protected $table = 'wilayah';

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function laporans()
    {
        return $this->hasMany(Laporan::class);
    }

    public function getTipeLabelAttribute(): string
    {
        return ucfirst($this->tipe);
    }
}