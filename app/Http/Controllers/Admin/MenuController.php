<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Admin, AdminModuleAction, AdminRolePermission};

class MenuController extends Controller
{
    
    public function desktopmenu()
    {
        $admin = session()->get('admin_username');
        if (empty($admin)) {
            return response()->json(['code' => 401, 'message' => '请先登录', 'data' => []]);
            // return redirect('/login');
        }
        $admin_user = Admin::where('username', $admin)->select()->first();

        $admin_action = AdminRolePermission::where('role_id', $admin_user->role_id)->get()->pluck('action')->toArray();
        $menu = AdminModuleAction::where('level', 0)
            ->whereIn('action', $admin_action)
            ->select('id','name as title','action as pageURL','name','icon')
            ->get()->toArray();
        foreach($menu as &$item){
            $item['pageURL'] = '/'.trim($item['pageURL'],'/');
            $item['openType'] = 2;
            $item['maxOpen'] = -1;
            $item['extend'] = false;
            $item['childs'] = null;
        }

        return response()->json(['code' => 1, 'message' => '成功', 'data' => $menu]);
    }

    
}
