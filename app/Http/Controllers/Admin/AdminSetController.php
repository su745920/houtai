<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Admin, AdminRole, AdminRolePermission, AdminToken, Users,Cuetone,RechargeRecord};
use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use Google;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class AdminSetController extends Controller
{
    public function  update_pwd(){
        $admin_username=session()->get("admin_username");
        return view('admin.admin_set.update_pwd')->with('admin_username',$admin_username);
    }
    public function updatePwdAjax()
    {

        $oldpassword = request()->input('oldpassword', '');

        if (empty($oldpassword)) {
            return $this->error('原密码必须填写');
        }
        $md5Pwd = Users::MakePassword($oldpassword);
        $admin_id=session()->get("admin_id");

        $admin = Admin::getById($admin_id);
        $dbPwd=$admin->password;
        if ($md5Pwd!=$dbPwd){
            return $this->error('原密码错误');
        }
        $newpassword=request()->input('newpassword', '');
        if (empty($newpassword)){
            return $this->error('请输入密码错误');
        }
        $newpasswordMD5=Users::MakePassword($newpassword);
        $admin->password=$newpasswordMD5;
        $admin->save();



        return $this->success('修改密码成功');
    }
}