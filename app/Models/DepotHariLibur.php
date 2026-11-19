<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotHariLibur extends Model
{
    use HasFactory;

    protected $table = 'depot_hari_libur';

    protected $fillable = [
        'depot_id',
        'hari',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }
}
