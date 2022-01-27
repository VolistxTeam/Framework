<?php

namespace App\Http\Controllers\Auth;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\DataTransferObjects\AdminLogDTO;
use App\Repositories\AdminLogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class AdminLogController extends BaseController
{
    private AdminLogRepository $adminLogRepository;

    public function __construct(AdminLogRepository $adminLogRepository)
    {
        $this->adminLogRepository = $adminLogRepository;
    }

    public function GetAdminLog(Request $request, $log_id): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $validator = Validator::make([
            'log_id' => $log_id
        ], [
            'log_id' => ['bail', 'required', 'uuid', 'exists:admin_logs,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $log = $this->adminLogRepository->Find($log_id);

            if (!$log) {
                return response()->json(MessagesCenter::E404(), 404);
            }
            return response()->json(AdminLogDTO::fromModel($log)->GetDTO());
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }

    public function GetAdminLogs(Request $request): JsonResponse
    {
        if (!PermissionsCenter::checkPermission($request->input('X-ACCESS-TOKEN'), 'key:list')) {
            return response()->json(MessagesCenter::E401(), 401);
        }

        $search = $request->input('search',"");
        $page =$request->input('page',1);
        $limit = $request->input('limit',50);

        $validator = Validator::make([
            'page'=>$page,
            'limit'=>$limit
        ], [
            '$page' => ['bail', 'sometimes', 'numeric'],
            'limit' => ['bail', 'sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(MessagesCenter::E400($validator->errors()->first()), 400);
        }

        try {
            $logs = $this->adminLogRepository->FindAll($search,$page,$limit);
            if (!$logs) {
                return response()->json(MessagesCenter::E500(), 500);
            }

            $items = [];
            foreach ($logs->items() as $item){
                $items[] = AdminLogDTO::fromModel($item)->GetDTO();
            }

            return response()->json([
                'pagination' => [
                    'per_page' => $logs->perPage(),
                    'current' => $logs->currentPage(),
                    'total' => $logs->lastPage(),
                ],
                'items' => $items
            ]);
        } catch (Exception $ex) {
            return response()->json(MessagesCenter::E500(), 500);
        }
    }
}
