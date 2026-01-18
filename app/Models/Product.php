<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'name',
        'sku',
        'store_id',
        'image_url',
        'unit_price',
        'unit',
        'quantity_remaining'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
