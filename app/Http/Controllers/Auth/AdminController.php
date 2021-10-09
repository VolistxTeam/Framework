<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;
use RandomLib\Factory;
use SecurityLib\Strength;

class AdminController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //Post
    public function CreateInfo(Request $request)
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

        $newPersonalKey = PersonalKeys::query()->create([
            'user_id' => $request->input('user_id'),
            'key' => $this->generateUniqueKey(),
            'max_count' => $request->input('monthly_usage'),
            'permissions' => json_decode($request->input('permissions')),
            'whitelist_range' => json_decode($whitelistRange),
            'activated_at' => Carbon::now(),
            'expires_at' => $hoursToExpire != -1 ? Carbon::now()->addHours($hoursToExpire) : null
        ])->toArray();

        return response()->json($this->convertItemToArray($newPersonalKey), 201);
    }

    public function UpdateInfo(Request $request, $id, $token): \Illuminate\Http\JsonResponse
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:update')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make(array_merge($request->all(), [
            'user_id' => $id,
            'user_token' => $token
        ]), [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required'],
            'monthly_usage' => ['bail', 'sometimes', 'numeric'],
            'permissions' => ['bail', 'sometimes', 'json'],
            'whitelistRange' => ['bail', 'sometimes', 'json'],
            'activated_at' => ['bail', 'sometimes', 'numeric'],
            'expires_at' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $newPersonalKey = PersonalKeys::query()->where('user_id', $id)->where('key', $token)->first();

        if (empty($newPersonalKey)) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        $monthlyUsage = $request->input('monthly_usage');
        $permissions = $request->input('permissions');
        $whitelistRange = $request->input('whitelist_range');
        $activatedAt = $request->input('activated_at');
        $expiresAt = $request->input('expires_at');

        if (!$monthlyUsage && !$permissions && !$activatedAt && !$expiresAt && !$whitelistRange) {
            return response()->json($this->convertItemToArray($newPersonalKey));
        }

        if ($monthlyUsage) $newPersonalKey->max_count = $monthlyUsage;

        if ($permissions) $newPersonalKey->permissions = json_decode($permissions);

        if ($whitelistRange) {
            foreach (json_decode($whitelistRange, true) as $item) {
                if (!filter_var($item, FILTER_VALIDATE_IP)) {
                    return response()->json(MessagesCenter::E400('IP in the whitelist range field is invalid.'), 400);
                }
            }
            $newPersonalKey->whitelist_range = json_decode($whitelistRange);
        }

        if ($activatedAt && $this->isValidDate($activatedAt)) $newPersonalKey->activated_at = $activatedAt;

        if ($expiresAt) {
            if ($expiresAt == '-1') {
                $newPersonalKey->expires_at = null;
            } else if ($this->isValidDate($expiresAt)) {
                $newPersonalKey->expires_at = $expiresAt;
            }
        }

        $newPersonalKey->save();

        return response()->json($this->convertItemToArray($newPersonalKey->toArray()));
    }

    //Put
    public function ResetInfo(Request $request, $id, $token)
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:reset')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make([
            'user_id' => $id,
            'user_token' => $token
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $toBeResetKey = PersonalKeys::query()->where('user_id', $id)->where('key', $token)->first();

        if (empty($toBeResetKey)) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        $newKey = $this->generateUniqueKey();

        $toBeResetKey->key = $newKey;

        $toBeResetKey->save();

        return response()->json($this->convertItemToArray($toBeResetKey->toArray()));
    }

    //DELETE -> Not sure if can do cascade delete, to avoid extra query for logs, it should be possible, google later
    public function DeleteInfo(Request $request, $id, $token)
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:delete')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make([
            'user_id' => $id,
            'user_token' => $token
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $toBeDeletedKey = PersonalKeys::query()->where('user_id', $id)->where('key', $token)->first();

        if (empty($toBeDeletedKey)) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        Logs::query()->where('key_id', $toBeDeletedKey->id)->delete();

        $toBeDeletedKey->delete();

        return response()->json([
            'result' => 'true'
        ]);
    }

    //GET
    public function GetLogs(Request $request, $id, $token)
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:logs')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make([
            'user_id' => $id,
            'user_token' => $token
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $personalKey = PersonalKeys::query()->where('user_id', $id)->where('key', $token)->first();

        if (empty($personalKey)) {
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

    //GET
    public function GetToken(Request $request, $id, $token)
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:list')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $validator = Validator::make([
            'user_id' => $id,
            'user_token' => $token
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        $personalKey = PersonalKeys::query()->where('user_id', $id)->where('key', $token)->first();

        if (empty($personalKey)) {
            return response()->json(MessagesCenter::E404(), 404);
        }

        return response()->json($this->convertItemToArray($personalKey->toArray()));
    }

    //GET
    public function GetTokens(Request $request, $id)
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
            $reconstructedArray[] = $this->convertItemToArray($item);
        }

        return response()->json($reconstructedArray);
    }

    //GET
    public function GetStats(Request $request, $id, $token)
    {
        if (!PermissionsCenter::checkAdminPermission($request->bearerToken(), 'key:stats')) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }
        $personalKey = PersonalKeys::query()->where('user_id', $id)->where('key', $token)->first();

        if (empty($personalKey)) {
            return response()->json(MessagesCenter::E404(), 404);
        }
        $date = $request->input('date', Carbon::now()->format('Y-m'));

        $validator = Validator::make([
            'user_id' => $id,
            'user_token' => $token,
            'date' => $date
        ], [
            'user_id' => ['bail', 'required', 'integer'],
            'user_token' => ['bail', 'required'],
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
                return Carbon::parse($date->created_at)->format('d'); // grouping by days
            })->toArray();

        $totalCount = Logs::query()->where('key_id', $personalKey->id)
            ->whereYear('created_at', $specifiedDate->format('Y'))
            ->whereMonth('created_at', $specifiedDate->format('m'))
            ->count();

        $statArr = [];

        for ($i = 1; $i <= $lastDay; $i++) {
            $statArr[] = [
                'date' => $specifiedDate->format('Y-m-') . sprintf("%02d", $i),
                'count' => isset($logMonth[$i]) && !empty($logMonth[$i]) ? count($logMonth[$i]) : 0
            ];
        }

        return response()->json([
            'usage' => [
                'current' => $totalCount,
                'max' => $personalKey->max_count,
                'percent' => (float)number_format(($totalCount * 100) / $personalKey->max_count, 2),
            ],
            'details' => $statArr
        ]);
    }


    //MUST BE REFACTORED LATER, SECURITY ISSUES.
    private function generateUniqueKey()
    {
        $factory = new Factory();
        $generator = $factory->getGenerator(new Strength(Strength::HIGH));

        $generatedToken = $generator->generateString(100, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $nonExistConfirmed = false;

        while ($nonExistConfirmed == false) {
            $checkDB = PersonalKeys::query()->where('key', $generatedToken)->first();

            if (empty($checkDB)) {
                $nonExistConfirmed = true;
            } else {
                $generatedToken = $generator->generateString(100, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            }
        }

        return $generatedToken;
    }

    private function convertItemToArray($item)
    {
        return [
            'user_id' => $item['user_id'],
            'key' => $item['key'],
            'monthly_usage' => $item['max_count'],
            'permissions' => $item['permissions'],
            'whitelist_ip' => $item['whitelist_range'],
            'subscription' => [
                'is_expired' => $item['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($item['expires_at'])),
                'activated_at' => $item['activated_at'],
                'expires_at' => $item['expires_at']
            ]
        ];
    }

    private function isValidDate($string, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $string);
        return $d && $d->format($format) == $string;
    }

}
