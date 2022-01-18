<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Models\Plan;
use App\Models\Subscription;
use App\Repositories\LogRepository;
use App\Repositories\PersonalTokenRepository;
use App\Repositories\SubscriptionRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Routing\Controller as BaseController;

class PersonalTokenController extends BaseController
{
    private PersonalTokenRepository $personalTokenRepository;
    private LogRepository $logRepository;


    public function __construct(PersonalTokenRepository $personalTokenRepository,LogRepository $logRepository)
    {
        $this->personalTokenRepository = $personalTokenRepository;
        $this->logRepository = $logRepository;

    }

    public function CreatePersonalToken(Request $request, $subscription_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:create')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(),[
            'subscription_id' => $subscription_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'hours_to_expire' => ['bail', 'required', 'integer'],
            'permissions' => ['bail', 'required' ,'array'],
            'permissions.*' => ['bail', 'required_if:permissions,array', 'string'],
            'whitelist_range' => ['bail', 'required' ,'array'],
            'whitelist_range.*' => ['bail', 'required_if:whitelist_range,array', 'ip'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $key = $this->generateSubscriptionKey();
            $salt = Str::random(16);

            $newPersonalToken = $this->personalTokenRepository->Create($subscription_id, [
                'key' => $key,
                'salt' => $salt,
                'permissions' => json_decode($request->input('permissions')),
                'whitelist_range' => json_decode( $request->input('whitelist_range')),
                'activated_at' => Carbon::now(),
                'hours_to_expire' => $request->input('hours_to_expire')
            ])->toArray();

            if (!$newPersonalToken) {
                return response()->json(MessagesCenter::E500(), 500);
            }
            return response()->json($this->getUserToken($newPersonalToken,$key), 201);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function UpdatePersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:update')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
            'hours_to_expire' => ['bail', 'sometimes', 'integer'],
            'permissions' => ['bail', 'sometimes' ,'array'],
            'permissions.*' => ['bail', 'required_if:permissions,array', 'string'],
            'whitelist_range' => ['bail', 'sometimes' ,'array'],
            'whitelist_range.*' => ['bail', 'required_if:whitelist_range,array', 'ip'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $updatedToken = $this->personalTokenRepository->Update($subscription_id, $token_id, $request->all())->toArray();
            if (!$updatedToken) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($this->getUserToken($updatedToken));
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function ResetPersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:reset')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }


        try {
            $newKey = $this->generateSubscriptionKey();
            $newSalt = Str::random(16);

            $resetToken = $this->personalTokenRepository->Reset($subscription_id, $token_id,
                [
                    'key'=>$newKey,
                    'salt'=>$newSalt
                ]
            )->toArray();

            if (!$resetToken) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($this->getUserToken($resetToken,$newKey));
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function DeletePersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:delete')) {
            return response()->json(MessagesCenter::E401(), 401);
        }
        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $result = $this->personalTokenRepository->Delete($subscription_id, $token_id);
            if (!$result) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($result);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPersonalToken(Request $request, $subscription_id, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:list')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'subscription_id' => $subscription_id,
            'token_id' => $token_id
        ]), [
            'subscription_id' => ['required', 'uuid', 'bail', 'exists:subscriptions,id'],
            'token_id' => ['required', 'uuid', 'bail', 'exists:personal_tokens,id'],
        ]);

        try {
            $token = $this->personalTokenRepository->Find($subscription_id, $token_id)->toArray();

            if (!$token) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($this->getUserToken($token));
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPersonalTokens(Request $request, $subscription_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
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
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $tokens = $this->personalTokenRepository->FindAll($subscription_id ,$search, $page, $limit);

            $userTokens = [];
            foreach ($tokens->items() as $item) {
                $userTokens[] = $this->getUserToken($item->toArray());
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
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public  function GetPersonalTokenLogs(Request $request,$subscription_id,$token_id):JsonResponse{
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:logs')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $search = $request->input('search',"");
        $page =$request->input('page',1);
        $limit = $request->input('limit',50);

        $validator = Validator::make(array_merge([
            'subscription_id' => $subscription_id,
            'token_id'=>$token_id,
            'page'=>$page,
            'limit'=>$limit
        ]), [
            'subscription_id' => ['bail', 'required', 'exists:subscriptions,id'],
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
            '$page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $logs = $this->logRepository->FindLogsByToken($token_id,$search,$page,$limit);

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



    private function generateSubscriptionKey()
    {
        return Str::random(64);
    }

    private function getUserToken(array $item, $key = null): array
    {
        $result = [
            'id' => $item['id'],
            'user_id' => $item['user_id'],
            'key' => null,
            'max_count' => $item['max_count'],
            'permissions' => $item['permissions'],
            'whitelist_range' => $item['whitelist_range'],
            'subscription' => [
                'is_expired' => $item['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($item['expires_at'])),
                'activated_at' => $item['activated_at'],
                'expires_at' => $item['expires_at']
            ],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ];

        if ($key) {
            $result['key'] = $key;
        } else {
            unset($result['key']);
        }

        return $result;
    }

}
