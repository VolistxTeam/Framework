<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Repositories\PlanRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class PlanController extends BaseController
{
    private PlanRepository $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->planRepository = $planRepository;
    }

    public function CreatePlan(Request $request): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:create')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'string'],
            'description' => ['bail', 'required', 'string'],
            'requests' => ['bail', 'required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $newPlan = $this->planRepository->Create($request->all())->toArray();
            if (!$newPlan) {
                return response()->json(MessagesCenter::E500(), 500);
            }
            return response()->json($newPlan, 201);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function UpdatePlan(Request $request, $plan_id): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:update')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'plan_id' => $plan_id
        ]), [
            'plan_id' => ['bail', 'required', 'uuid', 'exists:plans,id'],
            'name' => ['bail', 'sometimes', 'string'],
            'description' => ['bail', 'sometimes', 'string'],
            'requests' => ['bail', 'sometimes', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $updatedPlan = $this->planRepository->Update($plan_id, $request->all());

            if (!$updatedPlan) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($updatedPlan);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function DeletePlan(Request $request, $plan_id): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:delete')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make([
            'plan_id' => $plan_id
        ], [
            'plan_id' => ['bail', 'required', 'uuid', 'exists:plans,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $result = $this->planRepository->Delete($plan_id);
            if (!$result) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json(null, 204);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPlan(Request $request, $plan_id): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make([
            'plan_id' => $plan_id
        ], [
            'plan_id' => ['bail', 'required', 'uuid', 'exists:plans,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $plan = $this->planRepository->Find($plan_id);

            if (!$plan) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($plan);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPlans(Request $request): JsonResponse
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
            'page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $plans = $this->planRepository->FindAll($search, (int) $page, (int) $limit);
            if (!$plans) {
                return response()->json(MessagesCenter::E500(), 500);
            }

            return response()->json([
                'pagination' => [
                    'per_page' => $plans->perPage(),
                    'current' => $plans->currentPage(),
                    'total' => $plans->lastPage(),
                ],
                'items' => $plans->items()
            ]);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }
}
