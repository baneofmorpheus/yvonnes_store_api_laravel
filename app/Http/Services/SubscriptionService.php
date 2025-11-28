<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Widget;
use App\Services\UtilityService;


class SubscriptionService
{


    static function createInitalPlan(User $user)
    {



        if ($user->currentSubscription()) {

            return;
        }

        return  Subscription::create([
            'plan' => 'free',
            'user_id' => $user->id,
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ]);
    }
}
