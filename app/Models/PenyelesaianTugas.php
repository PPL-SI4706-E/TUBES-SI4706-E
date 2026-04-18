<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyelesaianTugas extends Model
{
    use HasFactory;
    protected $table = 'penyelesaian_tugas';
    protected $primaryKey = 'penyelesaian_id';
}
