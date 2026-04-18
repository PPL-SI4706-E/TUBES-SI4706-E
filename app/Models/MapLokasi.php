<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapLokasi extends Model
{
    use HasFactory;
    protected $table = 'map_lokasi';
    protected $primaryKey = 'map_id';

    protected $fillable = ['laporan_id', 'latitude', 'longitude'];
}
