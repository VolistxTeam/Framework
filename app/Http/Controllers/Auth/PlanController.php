<?php

namespace App\Http\Controllers\Auth;

use App\DataTransferObjects\Auth\PlanDTO;
use App\Facades\Messages;
use App\Facades\Permissions;
use App\Http\Controllers\Controller;
use App\Repositories\Auth\PlanRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    private PlanRepository $planRepository;

    public function __construct(PlanRepository $planRepository)
    {
        $this->module = "plans";
        $this->planRepository = $planRepository;
    }

    public function CreatePlan(Request $request): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module, 'create')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['bail', 'required', 'string'],
            'description' => ['bail', 'required', 'string'],
            'data' => ['bail', 'required', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $newPlan = $this->planRepository->Create($request->all());
            if (!$newPlan) {
                return response()->json(Messages::E500(), 500);
            }
            return response()->json(PlanDTO::fromModel($newPlan)->GetDTO(), 201);
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function UpdatePlan(Request $request, $plan_id): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module, 'update')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'plan_id' => $plan_id
        ]), [
            'plan_id' => ['bail', 'required', 'uuid', 'exists:plans,id'],
            'name' => ['bail', 'sometimes', 'string'],
            'description' => ['bail', 'sometimes', 'string'],
            'data' => ['bail', 'sometimes', 'json'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $updatedPlan = $this->planRepository->Update($plan_id, $request->all());

            if (!$updatedPlan) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(PlanDTO::fromModel($updatedPlan)->GetDTO());
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function DeletePlan(Request $request, $plan_id): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module, 'delete')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make([
            'plan_id' => $plan_id
        ], [
            'plan_id' => ['bail', 'required', 'uuid', 'exists:plans,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $result = $this->planRepository->Delete($plan_id);
            if ($result === null) {
                return response()->json(Messages::E404(), 404);
            }
            if ($result === false) {
                return response()->json(Messages::E409(), 409);
            }
            return response()->json(null, 204);
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetPlan(Request $request, $plan_id): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module, 'view')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make([
            'plan_id' => $plan_id
        ], [
            'plan_id' => ['bail', 'required', 'uuid', 'exists:plans,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $plan = $this->planRepository->Find($plan_id);

            if (!$plan) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(PlanDTO::fromModel($plan)->GetDTO());
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetPlans(Request $request): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module, 'view-all')) {
            return response()->json(Messages::E401(), 401);
        }

        $search = $request->input('search', "");
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        $validator = Validator::make([
            'page' => $page,
            'limit' => $limit
        ], [
            'page' => ['bail', 'sometimes', 'integer'],
            'limit' => ['bail', 'sometimes', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $plans = $this->planRepository->FindAll($search, (int)$page, (int)$limit);
            if (!$plans) {
                return response()->json(Messages::E500(), 500);
            }

            $items = [];
            foreach ($plans->items() as $item) {
                $items[] = PlanDTO::fromModel($item)->GetDTO();
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
            return response()->json(Messages::E500(), 500);
        }
    }
}
