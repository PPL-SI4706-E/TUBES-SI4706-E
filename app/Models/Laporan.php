<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
    ];

    protected $casts = [
        'tanggal_lapor' => 'datetime',
    ];

    public function mapLokasi()
    {
        return $this->hasOne(MapLokasi::class, 'laporan_id');
    }

    public function kategoriLaporan()
    {
        return $this->belongsTo(KategoriLaporan::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriLaporan::class, 'kategori_laporan_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warga()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function scopeFilterKeyword(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        $keyword = trim($keyword);

        return $query->where(function (Builder $query) use ($keyword) {
            $query->where('id', 'like', "%{$keyword}%")
                ->orWhere('alamat', 'like', "%{$keyword}%")
                ->orWhereHas('user', function (Builder $userQuery) use ($keyword) {
                    $userQuery->where('name', 'like', "%{$keyword}%");
                });
        });
    }

    public function scopeFilterStatusBayar(Builder $query, ?string $statusBayar): Builder
    {
        if (blank($statusBayar)) {
            return $query;
        }

        return match ($statusBayar) {
            'lunas' => $query->whereHas('pembayaran', function (Builder $pembayaranQuery) {
                $pembayaranQuery->where('status_pembayaran', 'Lunas');
            }),
            'menunggu_verifikasi' => $query->whereHas('pembayaran', function (Builder $pembayaranQuery) {
                $pembayaranQuery->where('status_pembayaran', 'Terverifikasi');
            }),
            'belum_lunas' => $query->where(function (Builder $query) {
                $query->whereDoesntHave('pembayaran')
                    ->orWhereHas('pembayaran', function (Builder $pembayaranQuery) {
                        $pembayaranQuery->whereIn('status_pembayaran', ['Menunggu', 'Ditolak', 'Kadaluarsa']);
                    });
            }),
            default => $query,
        };
    }

    public function scopeFilterRentangBulan(
        Builder $query,
        ?string $bulanAwal,
        ?string $bulanAkhir
    ): Builder {
        if (filled($bulanAwal)) {
            $tanggalAwal = Carbon::createFromFormat('Y-m-d', "{$bulanAwal}-01")->startOfMonth();
            $query->where('tanggal_lapor', '>=', $tanggalAwal);
        }

        if (filled($bulanAkhir)) {
            $tanggalAkhir = Carbon::createFromFormat('Y-m-d', "{$bulanAkhir}-01")->endOfMonth();
            $query->where('tanggal_lapor', '<=', $tanggalAkhir);
        }

        return $query;
    }

    public function scopeFilterWilayah(Builder $query, ?int $wilayahId): Builder
    {
        if (blank($wilayahId)) {
            return $query;
        }

        return $query->where('wilayah_id', $wilayahId);
    }

    public function scopeFilterKategori(Builder $query, ?int $kategoriId): Builder
    {
        if (blank($kategoriId)) {
            return $query;
        }

        return $query->where('kategori_laporan_id', $kategoriId);
    }
}
