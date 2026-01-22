<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'amount',
        'payment_type',
        'invoice_id',
        'notes'

    ];


    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
