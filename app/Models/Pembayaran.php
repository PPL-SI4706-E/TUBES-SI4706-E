<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;
    protected $table = 'pembayaran';

    protected $fillable = [
        'laporan_id',
        'user_id',
        'harga',
        'metode_pembayaran',
        'qr_code_generate',
        'bukti_transaksi',
        'status_pembayaran', // Menunggu, Terverifikasi, Lunas, Ditolak, Kadaluarsa
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
