<?php

namespace App\Http\Controllers\Auth;

use App\Models\AccessKeys;
use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
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

    public function CreateInfo(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:create');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');
        $maxCount = $request->input('max_count', '');
        $permissions = $request->input('permissions', '');
        $hoursToExpire = $request->input('hours', '');

        if (empty($userID) || empty($maxCount) || empty($permissions) || empty($hoursToExpire) || filter_var($maxCount, FILTER_VALIDATE_INT) === false || filter_var($hoursToExpire, FILTER_VALIDATE_INT) === false) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in or invalid format.'
                ]
            ], 400);
        }

        if ($this->isJson($permissions) === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidPermissions',
                    'info' => 'The permission format is invalid. It should be in JSON array format.'
                ]
            ], 400);
        }

        $newKey = $this->generateUniqueKey();

        $expireDate = $hoursToExpire != -1 ? Carbon::now()->addHours($hoursToExpire) : null;

        $newPersonalKey = PersonalKeys::query()->create([
            'user_id' => $userID,
            'key' => $newKey,
            'max_count' => $maxCount,
            'permissions' => json_decode($permissions),
            'activated_at' => Carbon::now(),
            'expires_at' => $expireDate
        ]);

        $newPersonalKey = $newPersonalKey->toArray();

        return response()->json([
            'user_id' => $newPersonalKey['user_id'],
            'key' => $newPersonalKey['key'],
            'monthly_usage' => $newPersonalKey['max_count'],
            'permissions' => $newPersonalKey['permissions'],
            'subscription' => [
                'is_expired' => $newPersonalKey['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($newPersonalKey['expires_at'])),
                'activated_at' => $newPersonalKey['activated_at'],
                'expires_at' => $newPersonalKey['expires_at']
            ]
        ]);
    }

    public function UpdateInfo(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:update');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');
        $userToken = $request->input('user_token', '');
        $maxCount = $request->input('max_count', '');
        $permissions = $request->input('permissions', '');
        $activatedAt = $request->input('activated_at', '');
        $expiresAt = $request->input('expires_at', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $newPersonalKey = PersonalKeys::query()->where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($newPersonalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        if (!empty($maxCount) || !empty($permissions) || !empty($activatedAt) || !empty($expiresAt)) {
            if (!empty($maxCount)) {
                $newPersonalKey->max_count = $maxCount;
            }


            if (!empty($permissions)) {
                if ($this->isJson($permissions) === FALSE) {
                    return response()->json([
                        'error' => [
                            'type' => 'xInvalidPermissions',
                            'info' => 'The permission format is invalid. It should be in JSON array format.'
                        ]
                    ], 400);
                }

                $newPersonalKey->permissions = json_decode($permissions);
            }

            if (!empty($activatedAt) && $this->isValidDate($activatedAt, 'Y-m-d H:i:s')) {
                $newPersonalKey->activated_at = $activatedAt;
            }

            if (!empty($expiresAt)) {
                if ($expiresAt == 'null') {
                    $newPersonalKey->expires_at = null;
                } else if ($this->isValidDate($expiresAt, 'Y-m-d H:i:s')) {
                    $newPersonalKey->expires_at = $expiresAt;
                }
            }

            $newPersonalKey->save();
        }

        $newPersonalKey = $newPersonalKey->toArray();

        return response()->json([
            'user_id' => $newPersonalKey['user_id'],
            'key' => $newPersonalKey['key'],
            'monthly_usage' => $newPersonalKey['max_count'],
            'permissions' => $newPersonalKey['permissions'],
            'subscription' => [
                'is_expired' => $newPersonalKey['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($newPersonalKey['expires_at'])),
                'activated_at' => $newPersonalKey['activated_at'],
                'expires_at' => $newPersonalKey['expires_at']
            ]
        ]);
    }

    public function ResetInfo(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:reset');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');
        $userToken = $request->input('user_token', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $newPersonalKey = PersonalKeys::query()->where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($newPersonalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        $newKey = $this->generateUniqueKey();

        $newPersonalKey->key = $newKey;
        $newPersonalKey->save();

        $newPersonalKey = $newPersonalKey->toArray();

        return response()->json([
            'user_id' => $newPersonalKey['user_id'],
            'key' => $newPersonalKey['key'],
            'monthly_usage' => $newPersonalKey['max_count'],
            'permissions' => $newPersonalKey['permissions'],
            'subscription' => [
                'is_expired' => $newPersonalKey['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($newPersonalKey['expires_at'])),
                'activated_at' => $newPersonalKey['activated_at'],
                'expires_at' => $newPersonalKey['expires_at']
            ]
        ]);
    }

    public function DeleteInfo(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:delete');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');
        $userToken = $request->input('user_token', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $newPersonalKey = PersonalKeys::query()->where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($newPersonalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        Logs::query()->where('key_id', $newPersonalKey->id)->delete();

        $newPersonalKey->delete();

        return response()->json([
            'result' => 'true'
        ]);
    }

    public function GetLogs(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:logs');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');
        $userToken = $request->input('user_token', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $personalKey = PersonalKeys::query()->where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($personalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        $logs = Logs::query()->where('key_id', $personalKey->id)->orderBy('created_at','DESC')->paginate(25);

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

    public function GetTokens(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:list');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');

        if (empty($userID)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $personalKey = PersonalKeys::query()->where('user_id', $userID)->get()->toArray();

        $reconstructedArray = [];

        foreach ($personalKey as $item) {
            $reconstructedArray[] = [
                'user_id' => $item['user_id'],
                'key' => $item['key'],
                'monthly_usage' => $item['max_count'],
                'permissions' => $item['permissions'],
                'subscription' => [
                    'is_expired' => $item['expires_at'] != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($item['expires_at'])),
                    'activated_at' => $item['activated_at'],
                    'expires_at' => $item['expires_at']
                ]
            ];
        }
        return response()->json($reconstructedArray);
    }

    public function Stats(Request $request)
    {
        $permissionCheck = $this->checkPermission($request->input('access_key', ''), 'key:stats');

        if ($permissionCheck === FALSE) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'Invalid token was specified or do not have permission.'
                ]
            ], 403);
        }

        $userID = $request->input('user_id', '');
        $userToken = $request->input('user_token', '');
        $dateCo = $request->input('date', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        if (empty($dateCo)) {
            $dateCo = Carbon::now()->format('Y-m');
        }

        $personalKey = PersonalKeys::query()->where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($personalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        $specifiedDate = Carbon::parse($dateCo);
        $thisDate = Carbon::now();

        if ($specifiedDate->format('Y-m') == $thisDate->format('Y-m')) {
            $lastDay = $thisDate->day;
        } else {
            $lastDay = (int)$specifiedDate->format('t');
        }

        $logMonth = Logs::query()->where('key_id', $personalKey->id)
            ->whereYear('created_at', $specifiedDate->format('Y'))
            ->whereMonth('created_at', $specifiedDate->format('m'))
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d'); // grouping by months
            })->toArray();

        $totalCount = Logs::query()->where('key_id', $personalKey->id)
            ->whereYear('created_at', $specifiedDate->format('Y'))
            ->whereMonth('created_at', $specifiedDate->format('m'))
            ->count();

        $statCount = [];
        $statArr = [];

        foreach ($logMonth as $key => $value) {
            $statCount[(int)$key] = count($value);
        }

        for ($i = 1; $i <= $lastDay; $i++) {
            if (!empty($statCount[$i])) {
                $statArr[] = [
                    'date' => $specifiedDate->format('Y-m-') . sprintf("%02d", $i),
                    'count' => $statCount[$i]
                ];
            } else {
                $statArr[] = [
                    'date' => $specifiedDate->format('Y-m-') . sprintf("%02d", $i),
                    'count' => 0
                ];
            }
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

    private function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    private function isValidDate($string, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $string);
        return $d && $d->format($format) == $string;
    }

    private function generateUniqueKey()
    {
        $factory = new Factory;
        $generator = $factory->getGenerator(new Strength(Strength::HIGH));

        $generatedToken = $generator->generateString(32, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $nonExistConfirmed = false;

        while ($nonExistConfirmed == false) {
            $checkDB = PersonalKeys::query()->where('key', $generatedToken)->first();

            if (empty($checkDB)) {
                $nonExistConfirmed = true;
            } else {
                $generatedToken = $generator->generateString(32, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            }
        }

        return $generatedToken;
    }

    private function checkPermission($token, $permissionName)
    {
        $accessKey = AccessKeys::query()->where('token', $token)->first();

        if (empty($accessKey)) {
            return false;
        }

        if (in_array("*", $accessKey->permissions)) {
            return true;
        }

        if (in_array($permissionName, $accessKey->permissions)) {
            return true;
        }

        return false;
    }
}
