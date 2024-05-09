<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'store_id',
        'image_url',
        'unit',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }



}
