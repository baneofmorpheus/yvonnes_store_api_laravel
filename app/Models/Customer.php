<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [
        'name',
        'store_id',
        'address',
        'phone_number',
    ];
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
