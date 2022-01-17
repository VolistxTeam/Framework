<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Repositories\PersonalTokenRepository;
use App\Repositories\SubscriptionRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller as BaseController;

class SubscriptionController extends BaseController
{
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function CreateSubscription(Request $request): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:create')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['bail', 'required', 'integer'],
            'plan_id' => ['bail', 'required', 'string', 'exists:plans,id'],
            'plan_activated_at' => ['bail','required','string'],
            'plan_expires_at' => ['bail','required','string']
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $newSubscription = $this->subscriptionRepository->Create($request->all())->toArray();
            if (!$newSubscription) {
                return response()->json(MessagesCenter::E500(), 500);
            }
            return response()->json($newSubscription, 201);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function UpdateSubscription(Request $request, $subscription_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:update')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id
        ]), [
            'subscription_id' => ['bail', 'required', 'uuid', 'exists:subscriptions,id'],
            'plan_expires_at' => ['bail','sometimes','string'],
            'plan_id' => ['bail', 'sometimes', 'exists:plans,id']
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $updatedSub = $this->subscriptionRepository->Update($subscription_id, $request->all());

            if (!$updatedSub) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($updatedSub);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function DeleteSubscription(Request $request, $subscription_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:delete')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make([
            'subscription_id' => $subscription_id
        ], [
            'subscription_id' => ['bail', 'required', 'uuid', 'exists:subscriptions,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $result = $this->subscriptionRepository->Delete($subscription_id);
            if (!$result) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($result);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetSubscription(Request $request, $subscription_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make([
            'subscription_id' => $subscription_id
        ], [
            'subscription_id' => ['bail', 'required', 'uuid', 'exists:subscriptions,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $subscription = $this->subscriptionRepository->Find($subscription_id);

            if (!$subscription) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($subscription);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetSubscriptions(Request $request): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $search = $request->input('search',"");
        $page =$request->input('page',1);
        $limit = $request->input('limit',50);

        $validator = Validator::make([
            'page'=>$page,
            'limit'=>$limit
        ], [
            '$page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $subs = $this->subscriptionRepository->FindAll($search,$page,$limit);
            if (!$subs) {
                return response()->json(MessagesCenter::E500(), 500);
            }

            return response()->json([
                'pagination' => [
                    'per_page' => $subs->perPage(),
                    'current' => $subs->currentPage(),
                    'total' => $subs->lastPage(),
                ],
                'items' => $subs->items()
            ]);
        } catch (Exception $ex) {
            ray($ex);
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    private function generateSubscriptionKey(): string
    {
        return Str::random(64);
    }
}
