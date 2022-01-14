<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Repositories\PersonalTokenRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller as BaseController;

class AdminController extends BaseController
{
    private PersonalTokenRepository $personalTokenRepository;

    public function __construct(PersonalTokenRepository $personalTokenRepository)
    {
        $this->personalTokenRepository = $personalTokenRepository;
    }

    public function CreatePersonalToken(Request $request): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:create')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['bail', 'required', 'integer'],
            'max_count' => ['bail', 'required', 'integer', 'min:-1'],
            'permissions' => ['bail', 'array'],
            'permissions.*' => ['bail', 'required_if:permissions,array', 'string'],
            'whitelist_range' => ['bail', 'array'],
            'whitelist_range.*' => ['bail', 'required_if:whitelist_range,array', 'ip'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $key = $this->generateAPIKey();
            $salt = Str::random(16);

            $newPersonalToken = $this->personalTokenRepository->Create(array_merge($request->all(),
                [
                    'key' => $key,
                    'salt' => $salt
                ]
            ))->toArray();

            if (!$newPersonalToken) {
                return response()->json(MessagesCenter::E500(), 500);
            }

            return response()->json($this->getUserToken($newPersonalToken, $key), 201);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    private function generateAPIKey(): string
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

    public function UpdatePersonalToken(Request $request, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (!PermissionsCenter::checkPermission($adminKey, 'key:update')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'token_id' => $token_id
        ]), [
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
            'max_count' => ['bail', 'sometimes', 'integer', 'min:-1'],
            'permissions' => ['bail', 'sometimes', 'array'],
            'permissions.*' => ['bail', 'required_if:permissions,array', 'string'],
            'whitelist_range' => ['bail', 'sometimes', 'array'],
            'whitelist_range.*' => ['bail', 'required_if:whitelist_range,array', 'ip'],
            'hours_to_expire' => ['bail', 'sometimes', 'integer', 'min:-1'],
        ]);

        if ($validator->fails()) {
            ray($validator->errors()->first());
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $updatedToken = $this->personalTokenRepository->Update($token_id, $request->all())->toArray();
            if (!$updatedToken) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($this->getUserToken($updatedToken));
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function ResetPersonalToken(Request $request, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:reset')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'token_id' => $token_id
        ]), [
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $newKey = $this->generateAPIKey();
            $newSalt = Str::random(16);

            $resetToken = $this->personalTokenRepository->Reset($token_id, [
                'key' => $newKey,
                'salt' => $newSalt
            ])->toArray();

            if (!$resetToken) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($this->getUserToken($resetToken, $newKey));
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function DeletePersonalToken(Request $request, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:delete')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'token_id' => $token_id
        ]), [
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $result = $this->personalTokenRepository->Delete($token_id);
            if (!$result) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json(null, 204);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPersonalToken(Request $request, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'token_id' => $token_id
        ]), [
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $personalToken = $this->personalTokenRepository->Find($token_id)->toArray();
            if (!$personalToken) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json($this->getUserToken($personalToken));
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPersonalTokens(Request $request): JsonResponse
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
            $tokens = $this->personalTokenRepository->FindAll($search, $page, $limit);

            $userTokens = [];
            foreach ($tokens->items() as $item) {
                $userTokens[] = $this->getUserToken($item->toArray());
            }

            $buildResponse = [
                'pagination' => [
                    'per_page' => $tokens->perPage(),
                    'current' => $tokens->currentPage(),
                    'total' => $tokens->lastPage(),
                ],
                'items' => $userTokens
            ];
            return response()->json($buildResponse);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPersonalTokenLogs(Request $request, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:logs')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'token_id' => $token_id
        ]), [
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $personalToken = $this->personalTokenRepository->Find($token_id);
            if (!$personalToken) {
                return response()->json(MessagesCenter::E404(), 404);
            }

            $logs = $personalToken->logs()->orderBy('created_at', 'DESC')->paginate(25);

            $buildResponse = [
                'pagination' => [
                    'per_page' => $logs->perPage(),
                    'current' => $logs->currentPage(),
                    'total' => $logs->lastPage(),
                ],
                'items' => $logs->items()
            ];
            return response()->json($buildResponse);
        } catch (Exception $exception) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetPersonalTokenStats(Request $request, $token_id): JsonResponse
    {
        $adminKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());
        if (!PermissionsCenter::checkPermission($adminKey, 'key:stats')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $date = $request->input('date', Carbon::now()->format('Y-m'));

        $validator = Validator::make([
            'token_id' => $token_id,
            'date' => $date
        ], [
            'token_id' => ['bail', 'required', 'exists:personal_tokens,id'],
            'date' => ['bail', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $specifiedDate = Carbon::parse($date);
            $thisDate = Carbon::now();
            $lastDay = $specifiedDate->format('Y-m') == $thisDate->format('Y-m') ? $thisDate->day : (int)$specifiedDate->format('t');

            $personalToken = $this->personalTokenRepository->Find($token_id);

            $logMonth = $personalToken
                ->logs()
                ->whereYear('created_at', $specifiedDate->format('Y'))
                ->whereMonth('created_at', $specifiedDate->format('m'))
                ->get()
                ->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('j'); // grouping by days
                })->toArray();

            $totalCount = $personalToken
                ->logs()
                ->whereYear('created_at', $specifiedDate->format('Y'))
                ->whereMonth('created_at', $specifiedDate->format('m'))
                ->count();

            $statArr = [];

            for ($i = 1; $i <= $lastDay; $i++) {
                $statArr[] = [
                    'date' => $specifiedDate->format('Y-m-') . sprintf("%02d", $i),
                    'count' => isset($logMonth[$i]) ? count($logMonth[$i]) : 0
                ];
            }

            return response()->json([
                'usage' => [
                    'current' => $totalCount,
                    'max' => $personalToken->max_count == -1 ? null : $personalToken->max_count,
                    'percent' => $personalToken->max_count == -1 ? null : (float)number_format(($totalCount * 100) / $personalToken->max_count, 2),
                ],
                'details' => $statArr
            ]);
        } catch (Exception) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }
}
