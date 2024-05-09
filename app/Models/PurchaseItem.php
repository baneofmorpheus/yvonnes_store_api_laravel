<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'store_id',
        'quantity_purchased',
        'quantity_available',
        'unit_price',
        'total',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }



}
