<?php

namespace App\Http\Controllers\Auth;

use App\Models\AccessKeys;
use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
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

        if (empty($userID) || empty($maxCount) || empty($permissions)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in or invalid format.'
                ]
            ], 400);
        }

        if (filter_var($maxCount, FILTER_VALIDATE_INT) === false) {
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

        $newPersonalKey = new PersonalKeys();
        $newPersonalKey->user_id = $userID;
        $newPersonalKey->key = $newKey;
        $newPersonalKey->max_count = $maxCount;
        $newPersonalKey->permissions = $permissions;
        $newPersonalKey->save();

        return response()->json([
            'id' => $newPersonalKey->id,
            'user_id' => $newPersonalKey->user_id,
            'key' => $newPersonalKey->key,
            'max_count' => $newPersonalKey->max_count,
            'permissions' => json_decode($newPersonalKey->permissions),
            'created_at' => $newPersonalKey->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $newPersonalKey->updated_at->format('Y-m-d H:i:s')
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
        $userToken = $request->input('token', '');
        $maxCount = $request->input('max_count', '');
        $permissions = $request->input('permissions', '');

        if (empty($userID) || empty($userToken) || empty($maxCount) || empty($permissions)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
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

        $newPersonalKey = PersonalKeys::where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($newPersonalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        $newPersonalKey->max_count = $maxCount;
        $newPersonalKey->permissions = $permissions;
        $newPersonalKey->save();

        return response()->json([
            'id' => $newPersonalKey->id,
            'user_id' => $newPersonalKey->user_id,
            'key' => $newPersonalKey->key,
            'max_count' => $newPersonalKey->max_count,
            'permissions' => json_decode($newPersonalKey->permissions),
            'created_at' => $newPersonalKey->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $newPersonalKey->updated_at->format('Y-m-d H:i:s')
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
        $userToken = $request->input('token', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $newPersonalKey = PersonalKeys::where('user_id', $userID)->where('key', $userToken)->first();

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

        return response()->json([
            'id' => $newPersonalKey->id,
            'user_id' => $newPersonalKey->user_id,
            'key' => $newPersonalKey->key,
            'max_count' => $newPersonalKey->max_count,
            'permissions' => json_decode($newPersonalKey->permissions),
            'created_at' => $newPersonalKey->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $newPersonalKey->updated_at->format('Y-m-d H:i:s')
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
        $userToken = $request->input('token', '');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $newPersonalKey = PersonalKeys::where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($newPersonalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        Logs::where('key_id', $newPersonalKey->id)->delete();
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
        $userToken = $request->input('token', '');
        $count = $request->input('count', '150');

        if (empty($userID) || empty($userToken)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidParameters',
                    'info' => 'The required parameters are not filled in.'
                ]
            ], 400);
        }

        $personalKey = PersonalKeys::where('user_id', $userID)->where('key', $userToken)->first();

        if (empty($personalKey)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidItem',
                    'info' => 'No item found with provided parameters.'
                ]
            ], 404);
        }

        if (filter_var($count, FILTER_VALIDATE_INT) === false) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidFormat',
                    'info' => 'Provided parameters are unsupported format.'
                ]
            ], 400);
        }

        $count = (int) $count;

        if ($count > 5000) {
            return response()->json([
                'error' => [
                    'type' => 'xTooLargeData',
                    'info' => 'The server cannot return more than 5000 logs.'
                ]
            ], 400);
        }

        $logs = Logs::where('key_id', $personalKey->id)->orderBy('created_at','DESC')->limit($count)->get()->toArray();

        return response()->json($logs);
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

        $personalKey = PersonalKeys::where('user_id', $userID)->get()->toArray();

        return response()->json($personalKey);
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
        $userToken = $request->input('token', '');
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

        $personalKey = PersonalKeys::where('user_id', $userID)->where('key', $userToken)->first();

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

        $logMonth = Logs::where('key_id', $personalKey->id)
            ->whereYear('created_at', $specifiedDate->format('Y'))
            ->whereMonth('created_at', $specifiedDate->format('m'))
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('d'); // grouping by months
            })->toArray();

        $totalCount = Logs::where('key_id', $personalKey->id)
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

    private function generateUniqueKey()
    {
        $factory = new Factory;
        $generator = $factory->getGenerator(new Strength(Strength::HIGH));

        $generatedToken = $generator->generateString(32, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $nonExistConfirmed = false;

        while ($nonExistConfirmed == false) {
            $checkDB = PersonalKeys::where('key', $generatedToken)->first();

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
        $accessKey = AccessKeys::where('token', $token)->first();

        if (empty($accessKey)) {
            return false;
        }

        $permissions = json_decode($accessKey->permissions);

        if (in_array("*", $permissions)) {
            return true;
        }

        if (in_array($permissionName, $permissions)) {
            return true;
        }

        return false;
    }
}
