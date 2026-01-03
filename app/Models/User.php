<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;


class User extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'provider_id',
        'provider_name',
        'is_active',
        'token',
        'token_expires_at'


    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_users')
            ->using(StoreUser::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function storeBelongsToUser(int $store_id): bool
    {

        return  StoreUser::where('id', $store_id)
            ->where('user_id', $this->id)->exists();
    }
    public function getStoreRole(int $store_id): string
    {

        return  StoreUser::where('store_id', $store_id)
            ->where('user_id', $this->id)->first()?->role;
    }
}
