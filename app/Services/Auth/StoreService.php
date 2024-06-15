<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Log;

use App\Models\Store;
use Exception;

class StoreService
{
    public static function getUserDefaultStore(int $user_id): Store|null
    {

        return Store::where('user_id', $user_id)->where('is_deafult', true)->first();
    }
}
