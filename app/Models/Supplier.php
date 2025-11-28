<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'address',
    ];
}
