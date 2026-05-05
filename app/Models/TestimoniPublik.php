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
        'status',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isEditableUntil(?Carbon $now = null): bool
    {
        $now ??= now();

        return $this->created_at !== null
            && $now->lessThanOrEqualTo($this->created_at->copy()->addMinutes(5));
    }
}
