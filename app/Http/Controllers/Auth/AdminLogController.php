<?php

namespace App\Http\Controllers\Auth;

use App\Facades\Messages;
use App\Facades\Permissions;
use App\DataTransferObjects\AdminLogDTO;
use App\Http\Controllers\Controller;
use App\Repositories\AdminLogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminLogController extends Controller
{
    private AdminLogRepository $adminLogRepository;

    public function __construct(AdminLogRepository $adminLogRepository)
    {
        $this->module = "logs";
        $this->adminLogRepository = $adminLogRepository;
    }

    public function GetAdminLog(Request $request, $log_id): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module,'view')) {
            return response()->json(Messages::E401(), 401);
        }

        $validator = Validator::make([
            'log_id' => $log_id
        ], [
            'log_id' => ['bail', 'required', 'uuid', 'exists:admin_logs,id'],
        ]);

        if ($validator->fails()) {
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $log = $this->adminLogRepository->Find($log_id);

            if (!$log) {
                return response()->json(Messages::E404(), 404);
            }
            return response()->json(AdminLogDTO::fromModel($log)->GetDTO());
        } catch (Exception $ex) {
            ray($ex->getMessage());
            return response()->json(Messages::E500(), 500);
        }
    }

    public function GetAdminLogs(Request $request): JsonResponse
    {
        if (!Permissions::check($request->X_ACCESS_TOKEN, $this->module,'view-all')) {
            return response()->json(Messages::E401(), 401);
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
            return response()->json(Messages::E400($validator->errors()->first()), 400);
        }

        try {
            $logs = $this->adminLogRepository->FindAll($search,$page,$limit);
            if (!$logs) {
                return response()->json(Messages::E500(), 500);
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
            return response()->json(Messages::E500(), 500);
        }
    }
}
