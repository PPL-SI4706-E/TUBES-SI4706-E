<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'kategori',
        'tanggal_post',
        'is_penting',
    ];

    protected $casts = [
        'tanggal_post' => 'date',
        'is_penting' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
