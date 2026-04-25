<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;
    protected $table = 'laporan';

    protected $fillable = [
        'user_id', 
        'wilayah_id', 
        'kategori_laporan_id', 
        'judul', 
        'deskripsi', 
        'alamat', 
        'foto', 
        'status', 
        'tanggal_lapor', 
        'catatan_admin',
        'jenis_penanganan',
        'solusi',
        'alasan_penolakan',
        'validated_by',
        'validated_at'
    ];

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function mapLokasi()
    {
        return $this->hasOne(MapLokasi::class, 'laporan_id');
    }

    public function kategoriLaporan()
    {
        return $this->belongsTo(KategoriLaporan::class);
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }
}
