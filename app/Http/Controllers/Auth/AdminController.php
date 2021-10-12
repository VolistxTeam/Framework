<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Lumen\Routing\Controller as BaseController;

class AdminController extends BaseController
{

    public function CreateInfo(Request $request): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:create')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => ['bail', 'required', 'integer'],
            'monthly_usage' => ['bail', 'required', 'integer'],
            'permissions' => ['bail', 'required', 'json'],
            'whitelist_range' => ['bail', 'required', 'json'],
            'hours' => ['bail', 'required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $whitelistRange = $request->input('whitelist_range');
        $hoursToExpire = $request->input('hours');

        foreach (json_decode($whitelistRange, true) as $item) {
            if (!filter_var($item, FILTER_VALIDATE_IP)) {
                return response()->json(MessagesCenter::E400('IP in the whitelist range field is invalid.'), 400);
            }
        }

        $key = $this->generateAPIKey();
        $salt = Str::random(16);

        $newPersonalKey = PersonalKeys::query()->create([
            'key_id' => Str::uuid(),
            'user_id' => $request->input('user_id'),
            'key' => substr($key, 0, 32),
            'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
            'secret_salt' => $salt,
            'max_count' => $request->input('monthly_usage'),
            'permissions' => json_decode($request->input('permissions')),
            'whitelist_range' => json_decode($whitelistRange),
            'activated_at' => Carbon::now(),
            'expires_at' => $hoursToExpire != -1 ? Carbon::now()->addHours($hoursToExpire) : null
        ])->toArray();

        $userKey = $this->generateUserKey($newPersonalKey, $key);

        return response()->json($userKey, 201);
    }


    public function UpdateInfo(Request $request, $id, $token): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:update')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'user_id' => $id,
            'user_token' => $token
        ]), [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required', 'uuid'],
            'monthly_usage' => ['bail', 'sometimes', 'numeric'],
            'permissions' => ['bail', 'sometimes', 'json'],
            'whitelistRange' => ['bail', 'sometimes', 'json'],
            'activated_at' => ['bail', 'sometimes', 'date'],
            'expires_at' => ['bail', 'sometimes', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $personalKey = $this->retrievePersonalKey($id, $token);

        if (!$personalKey) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        $monthlyUsage = $request->input('monthly_usage');
        $permissions = $request->input('permissions');
        $whitelistRange = $request->input('whitelist_range');
        $activatedAt = $request->input('activated_at');
        $expiresAt = $request->input('expires_at');

        if (!$monthlyUsage && !$permissions && !$activatedAt && !$expiresAt && !$whitelistRange) {
            return response()->json($this->generateUserKey($personalKey));
        }

        if ($monthlyUsage) $personalKey->max_count = $monthlyUsage;

        if ($permissions) $personalKey->permissions = json_decode($permissions);

        if ($whitelistRange) {
            foreach (json_decode($whitelistRange, true) as $item) {
                if (!filter_var($item, FILTER_VALIDATE_IP)) {
                    return response()->json(MessagesCenter::E400('IP in the whitelist range field is invalid.'), 400);
                }
            }
            $personalKey->whitelist_range = json_decode($whitelistRange);
        }

        if ($activatedAt && $this->isValidDate($activatedAt)) $personalKey->activated_at = $activatedAt;

        if ($expiresAt) {
            if ($expiresAt == -1) {
                $personalKey->expires_at = null;
            } else if ($this->isValidDate($expiresAt)) {
                $personalKey->expires_at = $expiresAt;
            }
        }

        $personalKey->save();

        return response()->json($this->generateUserKey($personalKey->toArray()));
    }


    public function ResetInfo(Request $request, $id, $token): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:reset')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = $this->getDefaultValidator($id,$token);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $toBeResetKey = $this->retrievePersonalKey($id, $token);

        if (!$toBeResetKey) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        $newKey = $this->generateAPIKey();
        $salt = Str::random(16);

        $toBeResetKey->key = substr($newKey, 0, 32);
        $toBeResetKey->secret = Hash::make(substr($newKey, 32), ['salt' => $salt]);
        $toBeResetKey->secret_salt = $salt;
        $toBeResetKey->save();

        $userKey = $this->generateUserKey($toBeResetKey->toArray(), $newKey);

        return response()->json($userKey);
    }

    public function DeleteInfo(Request $request, $id, $token): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:delete')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = $this->getDefaultValidator($id,$token);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $toBeDeletedKey = $this->retrievePersonalKey($id, $token);

        if (!$toBeDeletedKey) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        Logs::query()->where('key_id', $toBeDeletedKey->id)->delete();

        $toBeDeletedKey->delete();

        return response()->json([
            'result' => 'true'
        ]);
    }

    public function GetLogs(Request $request, $id, $token): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:logs')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = $this->getDefaultValidator($id,$token);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $personalKey = $this->retrievePersonalKey($id, $token);

        if (!$personalKey) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        $logs = Logs::query()->where('key_id', $personalKey->id)->orderBy('created_at', 'DESC')->paginate(25);

        $buildResponse = [
            'pagination' => [
                'per_page' => $logs->perPage(),
                'current' => $logs->currentPage(),
                'total' => $logs->lastPage(),
            ],
            'items' => $logs->items()
        ];

        return response()->json($buildResponse);
    }

    public function GetToken(Request $request, $id, $token): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:list')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = $this->getDefaultValidator($id,$token);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $personalKey = $this->retrievePersonalKey($id, $token);

        if (!$personalKey) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        return response()->json($this->generateUserKey($personalKey->toArray()));
    }

    public function GetTokens(Request $request, $id): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:list')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make([
            'user_id' => $id,
        ], [
            'user_id' => ['bail', 'required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $keys = PersonalKeys::query()->where('user_id', $id)->get()->toArray();

        $reconstructedArray = [];

        foreach ($keys as $item) {
            $reconstructedArray[] = $this->generateUserKey($item);
        }

        return response()->json($reconstructedArray);
    }

    public function GetStats(Request $request, $id, $token): JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:stats')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $personalKey = $this->retrievePersonalKey($id, $token);

        if (!$personalKey) {
            return response()->json(MessagesCenter::E404(), 404);
        }
        $date = $request->input('date', Carbon::now()->format('Y-m'));

        $validator = Validator::make([
            'user_id' => $id,
            'user_token' => $token,
            'date' => $date
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required', 'uuid'],
            'date' => ['bail', 'date'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $specifiedDate = Carbon::parse($date);
        $thisDate = Carbon::now();
        $lastDay = $specifiedDate->format('Y-m') == $thisDate->format('Y-m') ? $thisDate->day : (int)$specifiedDate->format('t');

        $logMonth = Logs::query()->where('key_id', $personalKey->id)
            ->whereYear('created_at', $specifiedDate->format('Y'))
            ->whereMonth('created_at', $specifiedDate->format('m'))
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('j'); // grouping by days
            })->toArray();

        $totalCount = Logs::query()->where('key_id', $personalKey->id)
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
                'max' => $personalKey->max_count == -1 ? null : $personalKey->max_count,
                'percent' => $personalKey->max_count == -1 ? null : (float)number_format(($totalCount * 100) / $personalKey->max_count, 2),
            ],
            'details' => $statArr
        ]);
    }

    //Helper Functions
    private function retrievePersonalKey($id, $token)
    {
        return PersonalKeys::query()->where('user_id', $id)->where('key_id', $token)->first();
    }

    private function generateAPIKey(): string
    {
        return Str::random(64);
    }

    private function generateUserKey($item, $key = null): array
    {
        $result =  [
            'id' => $item['key_id'],
            'user_id' => $item['user_id'],
            'monthly_usage' => $item['max_count'],
            'permissions' => $item['permissions'],
            'whitelist_ip' => $item['whitelist_range'],
            'subscription' => [
                'is_expired' => $item['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($item['expires_at'])),
                'activated_at' => $item['activated_at'],
                'expires_at' => $item['expires_at']
            ],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ];

        if($key) $result['key'] = $key;

        return $result;
    }

    private function isValidDate($string): bool
    {
        $d = DateTime::createFromFormat('Y-m-d H:i:s', $string);
        return $d && $d->format('Y-m-d H:i:s') == $string;
    }

    private  function getDefaultValidator($id,$token): \Illuminate\Contracts\Validation\Validator
    {
       return Validator::make([
            'user_id' => $id,
            'user_token' => $token
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required', 'uuid'],
        ]);
    }
}
