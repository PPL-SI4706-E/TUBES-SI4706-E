<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyelesaianTugas extends Model
{
    use HasFactory;

    protected $table = 'penyelesaian_tugas';

    protected $primaryKey = 'penyelesaian_id';

    protected $fillable = [
        'penugasan_id',
        'foto_bukti',
        'tanggal_selesai',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_selesai' => 'date',
    ];

    public function penugasan()
    {
        return $this->belongsTo(Penugasan::class, 'penugasan_id');
    }
}
