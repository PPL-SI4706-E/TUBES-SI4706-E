<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penugasan extends Model
{
    use HasFactory;

    protected $table = 'penugasan';

    protected $fillable = [
        'laporan_id',
        'user_id',
        'tanggal_penugasan',
        'foto_bukti',
        'status_tugas',
    ];

    protected $casts = [
        'tanggal_penugasan' => 'date',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function penyelesaianTugas()
    {
        return $this->hasOne(PenyelesaianTugas::class, 'penugasan_id');
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class, 'laporan_id', 'laporan_id');
    }
}
