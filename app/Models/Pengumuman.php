<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

<<<<<<< Updated upstream
    protected $table = 'pengumumans';
}
=======
    protected $table = 'pengumuman';

    protected $fillable = [
        'user_id',
        'judul',
        'isi',
        'kategori',
        'is_penting',
        'tanggal_post',
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
>>>>>>> Stashed changes
