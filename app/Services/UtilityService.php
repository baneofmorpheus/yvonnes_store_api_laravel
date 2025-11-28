<?php

namespace App\Services;


use Illuminate\Support\Str;

class UtilityService{





    static function generateUniqueId(mixed $Model,string $unique_field,$id_length=200){


        do {
            $token = Str::random($id_length);
            $existing_token = $Model::where($unique_field, $token)->exists();
        } while ($existing_token);
        return $token;
    }
}
