<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;


class Invoice extends Model
{
    use HasFactory, Searchable, SoftDeletes;

    protected $fillable = [
        'code',
        'customer_id',
        'store_id',
        'sub_total',
        'discount_amount',
        'total',
        'payment_balance',
        'tax_amount',
        'status',
        'notes',
        'tax_percentage',

    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }



    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer->name ?? null,
            'created_at' => $this->created_at->timestamp,
            'total' => $this->total,
        ];
    }
}
