<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TestimoniPublik extends Model
{
    use HasFactory;

    protected $table = 'testimoni_publik';

    protected $fillable = [
        'nama',
        'email',
        'pesan',
        'status_validasi',
        'edit_token',
        'editable_until',
        'validated_at',
    ];

    protected $casts = [
        'editable_until' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function scopeApproved($query)
    {
        return $query->where('status_validasi', 'disetujui');
    }

    public function isEditable(): bool
    {
        return $this->editable_until instanceof Carbon && $this->editable_until->isFuture();
    }
}
