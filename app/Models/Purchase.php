<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'supplier_id',
        'store_id',
        'total',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }


    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }



    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier->name ?? null,
            'created_at' => $this->created_at->timestamp,
            'total' => $this->total,
        ];
    }
}
