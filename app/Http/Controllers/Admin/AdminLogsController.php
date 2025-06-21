<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog, AdminLogs, Currency, Setting, Users, UsersWallet, RechargeRecord,WalletSetting};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminLogsController extends Controller
{

    public function index()
    {
        $currency_type = Currency::where('parent_id', 0)->get();
        return view("admin.logs.index", [
            'currency_type' => $currency_type
        ]);
    }

    public function lists(Request $request)
    {
        $limit = $request->input('limit', 10);
        $admin = $request->input('admin', '');
        $uri = $request->input('uri', '');
        $start_time = strtotime($request->input('start_time', 0));
        $end_time = strtotime($request->input('end_time', 0));
        $method = $request->input('method', '');

        $list = AdminLogs::query();
        
        if (!empty($admin)) {
            $list = $list->where(function($query)use($admin){
                if (is_numeric($admin)){
                    $query = $query->where('admin_id', $admin);
                }
                return $query->orWhere('admin_name', 'like', '%'.$admin.'%');
            });
        }
        if (!empty($uri)) {
            $list = $list->where('uri','like', '%'.$uri.'%');
        }
        if (!empty($start_time)) {
            $list = $list->where('created_at', '>=', Carbon::parse($start_time)->format('Y-m-d h:i:s'));
        }
        if (!empty($end_time)) {
            $list = $list->where('created_at', '<=', Carbon::parse($end_time)->format('Y-m-d h:i:s'));
        }
        if (!empty($method)) {
            $list = $list->where('method', $method);
        }

        $list = $list->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($list);
    }

}
