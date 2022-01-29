<?php

namespace App\Http\Controllers\Auth;

use App\Classes\Facades\Messages;
use App\Classes\Facades\Permissions;
use App\DataTransferObjects\PersonalTokenDTO;
use App\DataTransferObjects\UserLogDTO;
use App\Repositories\PersonalTokenRepository;
use App\Repositories\UserLogRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller as BaseController;

class PersonalTokenController extends BaseController
{
    private PersonalTokenRepository $personalTokenRepository;
    private UserLogRepository $logRepository;


    public function __construct(PersonalTokenRepository $personalTokenRepository, UserLogRepository $logRepository)
    {
        $this->personalTokenRepository = $personalTokenRepository;
        $this->logRepository = $logRepository;

    }

    public function CreatePersonalToken(Request $request, $subscription_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:create')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'hours_to_expire' => ['bail', 'required', 'integer'],
            'permissions' => ['bail', 'required', 'array'],
            'permissions.*' => ['bail', 'required_if:permissions,array', 'string'],
            'whitelist_range' => ['bail', 'required', 'array'],
            'whitelist_range.*' => ['bail', 'required_if:whitelist_range,array', 'ip'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $key = $this->generateSubscriptionKey();
            $salt = Str::random(16);

            $newPersonalToken = $this->personalTokenRepository->Create($subscription_id, [
                'key' => $key,
                'salt' => $salt,
                'permissions' => $request->input('permissions'),
                'whitelist_range' => $request->input('whitelist_range'),
                'activated_at' => Carbon::now(),
                'hours_to_expire' => $request->input('hours_to_expire')
            ]);

            if (!$newPersonalToken) {
                return response()->json(Messages::E500(), 500);
            }
            return response()->json(PersonalTokenDTO::fromModel($newPersonalToken)->GetDTO($key), 201);
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    private function generateSubscriptionKey()
    {
        return Str::random(64);
    }

    public function UpdatePersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:update')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
            'hours_to_expire' => ['bail', 'sometimes', 'integer'],
            'permissions' => ['bail', 'sometimes', 'array'],
            'permissions.*' => ['bail', 'required_if:permissions,array', 'string'],
            'whitelist_range' => ['bail', 'sometimes', 'array'],
            'whitelist_range.*' => ['bail', 'required_if:whitelist_range,array', 'ip'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $updatedToken = $this->personalTokenRepository->Update($subscription_id, $token_id, $request->all());
            if (!$updatedToken) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(PersonalTokenDTO::fromModel($updatedToken)->GetDTO());
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function ResetPersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:reset')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }


        try {
            $newKey = $this->generateSubscriptionKey();
            $newSalt = Str::random(16);

            $resetToken = $this->personalTokenRepository->Reset($subscription_id, $token_id,
                [
                    'key' => $newKey,
                    'salt' => $newSalt
                ]
            );

            if (!$resetToken) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(PersonalTokenDTO::fromModel($resetToken)->GetDTO($newKey));
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function DeletePersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:delete')) {
            return response()->json(Messages::E401(), 401);
        }
        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $result = $this->personalTokenRepository->Delete($subscription_id, $token_id);
            if (!$result) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(null, 204);
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetPersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:list')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
        ]);

        try {
            $token = $this->personalTokenRepository->Find($subscription_id, $token_id);

            if (!$token) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(PersonalTokenDTO::fromModel($token)->GetDTO());
        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetPersonalTokens(Request $request, $subscription_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:list')) {
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
            $tokens = $this->personalTokenRepository->FindAll($subscription_id, $search, $page, $limit);

            $userTokens = [];
            foreach ($tokens->items() as $item) {
                $userTokens[] = PersonalTokenDTO::fromModel($item)->GetDTO();
            }

            return response()->json([
                'pagination' => [
                    'per_page' => $tokens->perPage(),
                    'current' => $tokens->currentPage(),
                    'total' => $tokens->lastPage(),
                ],
                'items' => $userTokens
            ]);

        } catch (Exception $ex) {
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetPersonalTokenLogs(Request $request, $subscription_id, $token_id): JsonResponse
    {
        if (!Permissions::check($request->input('X-ACCESS-TOKEN'), 'key:logs')) {
            return response()->json(Messages::E401(), 401);
        }

        $search = $request->input('search', "");
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        $validator = Validator::make(array_merge([
            'subscription_id' => $subscription_id,
            'token_id' => $token_id,
            'page' => $page,
            'limit' => $limit
        ]), [
            'subscription_id' => ['bail', 'required', 'exists:subscriptions,id'],
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
            '$page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $logs = $this->logRepository->FindLogsByToken($token_id, $search, $page, $limit);

            $items = [];
            foreach ($logs->items() as $item){
                $items[] = UserLogDTO::fromModel($item)->GetDTO();
            }

            return response()->json([
                'pagination' => [
                    'per_page' => $logs->perPage(),
                    'current' => $logs->currentPage(),
                    'total' => $logs->lastPage(),
                ],
                'items' => $items
            ]);
        } catch (Exception $exception) {
            return response()->json(Messages::E500(), 500);
        }
    }

}
