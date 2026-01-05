<?php

namespace App\Services;


use App\Models\StoreUser;
use App\Models\Store;
use Exception;

class StoreService
{
    public static function createStore(int $user_id)
    {

        $store = Store::create([
            'name' =>  fake()->word() . " Store",
            'user_id' => $user_id,
        ]);

        $store_user = StoreUser::create([
            'user_id' => $user_id,
            'store_id' => $store->id,
            'role' => 'owner',
        ]);
        return compact('store', 'store_user');
    }
    public static function addUserToStore(int $user_id, int $store_id, $role)
    {



        $is_default = true;
        if (StoreUser::where('user_id', $user_id)->where('is_default', true)->exists()) {
            $is_default = false;
        }


        return StoreUser::create([
            'user_id' => $user_id,
            'store_id' => $store_id,
            'role' => $role,
            'is_default' => $is_default,
        ]);
    }
    public static function removeUserFromStore(int $user_id, int $store_id)
    {


        return StoreUser::where('user_id', $user_id)
            ->where('store_id', $store_id)->delete();
    }

    public static function getDefaultStoreUser(int $user_id)
    {

        $store_user = StoreUser::where('user_id', $user_id)->where('is_default', true)->first();

        if (!$store_user) {
            $store_user = StoreUser::where('user_id', $user_id)->firstOrFail();
        }
        return $store_user;
    }
}
