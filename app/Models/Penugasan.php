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
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_penugasan' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────

    /** Laporan yang terkait dengan penugasan ini. */
    public function laporan()
    {
        return $this->belongsTo(Laporan::class, 'laporan_id');
    }

    /** Petugas lapangan yang ditugaskan. */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Ulasan/rating yang diberikan oleh warga setelah tugas selesai (via laporan). */
    public function ulasan()
    {
        return $this->hasOne(Ulasan::class, 'laporan_id', 'laporan_id');
    }

    /** Bukti penyelesaian yang diupload petugas. */
    public function penyelesaian()
    {
        return $this->hasOne(PenyelesaianTugas::class, 'penugasan_id');
    }
}
