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
        'quantity_purchased',
        'quantity_remaining',
        'unit_price',
        'product_id',
    ];


    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
    public function prodcut(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
