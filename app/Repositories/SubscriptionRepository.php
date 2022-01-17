<?php

namespace App\Repositories;

use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SubscriptionRepository
{
    public function Create(array $inputs)
    {
        return Subscription::query()->create([
            'user_id' => $inputs['user_id'],
            'plan_id' => $inputs['plan_id'],
            'plan_activated_at' => $inputs['plan_activated_at'],
            'plan_expires_at' => $inputs['plan_expires_at'],
        ]);
    }

    public function Update($subscriptionID, array $inputs)
    {
        $subscription = $this->Find($subscriptionID);

        if (!$subscription) {
            return null;
        }

        $user_id = $inputs['user_id'] ?? null;
        $plan_expires_at = $inputs['plan_expires_at'] ?? null;
        $plan_id = $inputs['plan_id'] ?? null;


        if (!$user_id && !$plan_expires_at && !$plan_id) {
            return $subscription;
        }

        if($user_id) $subscription->user_id = $user_id;
        if($plan_id) $subscription->plan_id = $plan_id;
        if($plan_expires_at) $subscription->plan_expires_at = $plan_expires_at;

        $subscription->save();

        return $subscription;
    }

    public function Find($subscriptionID)
    {
        return Subscription::query()->where('id', $subscriptionID)->first();
    }

    public function Delete($subscriptionID)
    {
        $toBeDeletedSub = $this->Find($subscriptionID);

        if (!$toBeDeletedSub) {
            return null;
        }

        $toBeDeletedSub->delete();

        return [
            'result' => 'true'
        ];
    }

    public function FindAll($needle, $page, $limit)
    {
        $columns = Schema::getColumnListing('subscriptions');

        return Subscription::query()->where(function ($query) use ($needle, $columns) {
            foreach ($columns as $column) {
                $query->orWhere("subscriptions.$column", 'LIKE', "%$needle%");
            }
        })->paginate($limit, ['*'], 'page', $page);
    }
}