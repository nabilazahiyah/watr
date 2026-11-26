<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Depot extends Model
{
    use HasFactory;

    protected $table = 'depot';

    protected $fillable = [
        'kelurahan_id',
        'nama_depot',
        'alamat',
        'link_google_maps',
        'latitude',
        'longitude',
        'harga_per_galon',
        'jam_buka',
        'jam_tutup',
        'buka_setiap_hari',
        'is_delivery',
        'telepon',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'harga_per_galon' => 'integer',
            'buka_setiap_hari' => 'boolean',
            'is_delivery' => 'boolean',
        ];
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    public function hariLibur(): HasMany
    {
        return $this->hasMany(DepotHariLibur::class, 'depot_id');
    }
}
