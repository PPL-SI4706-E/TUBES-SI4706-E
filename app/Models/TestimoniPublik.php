<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestimoniPublik extends Model
{
    use HasFactory;

    protected $table = 'testimoni_publik';

    protected $fillable = [
        'nama',
        'email',
        'rating',
        'pesan',
        'status',
        'catatan_admin',
        'approved_at',
        'editable_until',
        'session_token',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'editable_until' => 'datetime',
    ];

    public function isEditableFor(string $sessionToken): bool
    {
        return $this->session_token === $sessionToken && now()->lte($this->editable_until);
    }
}
