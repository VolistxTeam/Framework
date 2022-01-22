<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Repositories\SubscriptionRepository;
use App\Repositories\UserLogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class SubscriptionController extends BaseController
{
    private SubscriptionRepository $subscriptionRepository;
    private UserLogRepository $logRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository, UserLogRepository $logRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->logRepository = $logRepository;
    }

    public function CreateSubscription(Request $request): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:create')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['bail', 'required', 'integer'],
            'plan_id' => ['bail', 'required', 'string', 'exists:plans,id'],
            'plan_activated_at' => ['bail', 'required', 'date'],
            'plan_expires_at' => ['bail', 'required', 'date', 'after:plan_activated_at']
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
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:update')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id
        ]), [
            'subscription_id' => ['bail', 'required', 'uuid', 'exists:subscriptions,id'],
            'plan_expires_at' => ['bail', 'sometimes', 'string'],
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
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:delete')) {
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
            return response()->json(null, 204);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetSubscription(Request $request, $subscription_id): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:list')) {
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
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $search = $request->input('search', "");
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        $validator = Validator::make([
            'page' => $page,
            'limit' => $limit
        ], [
            '$page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $subs = $this->subscriptionRepository->FindAll($search, $page, $limit);
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
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetSubscriptionLogs(Request $request, $subscription_id): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:logs')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $search = $request->input('search', "");
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        $validator = Validator::make(array_merge([
            'subscription_id' => $subscription_id,
            'page' => $page,
            'limit' => $limit
        ]), [
            'subscription_id' => ['bail', 'required', 'exists:subscriptions,id'],
            '$page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $logs = $this->logRepository->FindLogsBySubscription($subscription_id, $search, $page, $limit);

            return response()->json([
                'pagination' => [
                    'per_page' => $logs->perPage(),
                    'current' => $logs->currentPage(),
                    'total' => $logs->lastPage(),
                ],
                'items' => $logs->items()
            ]);
        } catch (Exception $exception) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }
}
