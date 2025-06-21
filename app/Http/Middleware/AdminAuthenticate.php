<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\AdminModuleAction;
use App\Models\AdminRole;
use App\Models\AdminRolePermission;
use Closure;
use Illuminate\Support\Facades\Route;


class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $admin = session()->get('admin_username');

        if (empty($admin)) {
            //return response()->json(['error' => '999', 'message' => '请先登录']);
            if ($request->ajax()) {
                return response()->json([
                    'code'=>'401',
                    'type'=>'error',
                    'message'=>'登录失效，请重新登录',
                    'msg'=>'登录失效，请重新登录',
                ]);
            } else {
                return redirect('/login');
            }
        }
        $admin_user = Admin::where('username', $admin)->select()->first();

        $admin_role = AdminRole::where('id', $admin_user->role_id)->first();
        $admin_permit = AdminRolePermission::where('role_id', $admin_user->role_id)->get();

        $arr = [];
        foreach ($admin_permit as $v) {
            $arr[] = $v['action'];

        }

        $action = Route::getCurrentRoute()->uri();
        // 只判断菜单
        $module_action = AdminModuleAction::where('action', $action)
            ->first();
        $level = $module_action->level ?? -1;
        if ($level == 0 && !in_array($action, $arr) && $admin_role['is_super'] != 1) {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>'403',
                    'type'=>'error',
                    'message'=>'权限不足,请联系管理员',
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        return $next($request);
    }
}
