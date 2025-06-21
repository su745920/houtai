<?php

namespace App\Http\Controllers\Admin;

use App\Models\{Admin, Users,Agent};
use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GoogleAuthController extends Controller
{
    public function getBindQrcode(Request $request)
    {


        $adminId=$request->input("id");
        // $admin_user=Users::getById($adminId);
        $admin_user= Admin::getById($adminId);
        $createSecret = GoogleAuthenticator::CreateSecret();
        $createSecret['qrcode'] = QrCode::encoding('UTF-8')->size(180)->margin(1)->generate($createSecret['codeurl']);
        
        print_r( $createSecret['qrcode']);


        return view('admin.manager.google', ['admin_user' => $admin_user, 'createSecret' => $createSecret]);
    }

    public function bind(Request $request)
    {
        $secret = $request->input('secret', '');
        // $admin_id = session()->get('admin_id');
        $admin_id = $request->input('admin_id','');
        $admin = Admin::where('id', $admin_id)->first();
        $admin->google_secret = $secret;
        $admin->save();
        return $this->success("绑定成功");
    }
    
    public function checkGoogle(Request $request) {
        $admin_id = session()->get('admin_id');
        $admin = Admin::where('id', $admin_id)->first();
        $googleCode = $request->input("google_code");
        $google_secret = $admin->google_secret;
        if(!$google_secret) {
            return $this->error("请先绑定谷歌验证码");
        }
        // 验证谷歌验证码
        if($admin->google_secret) {
            if (!GoogleAuthenticator::CheckCode($google_secret, $googleCode)){
                return $this->error("验证失败");
            }
        }
        return $this->success("验证成功");
    }
    
     public function getAgentBindQrcode(Request $request)
    {


        $agentId=$request->input("id");
        $agent_user= Agent::getById($agentId);
        $createSecret = GoogleAuthenticator::CreateSecret();
        $createSecret['qrcode'] = QrCode::encoding('UTF-8')->size(180)->margin(1)->generate($createSecret['codeurl']);
        
        print_r( $createSecret['qrcode']);


        return view('admin.agent.google', ['agent_user' => $agent_user, 'createSecret' => $createSecret]);
    }

    public function agentBind(Request $request)
    {
        $secret = $request->input('secret', '');
        $agent_id = $request->input('agent_id','');
        $agent = Agent::where('id', $agent_id)->first();
        $agent->google_secret = $secret;
        $agent->save();
        return $this->success("绑定成功");
    }
    
    public function checkAgentGoogle(Request $request) {
        $agent_id = session()->get('agent_id');
        $agent = Agent::where('id', $agent_id)->first();
        $googleCode = $request->input("google_code");
        $google_secret = $agent->google_secret;
        if(!$google_secret) {
            return $this->error("请先绑定谷歌验证码");
        }
        // 验证谷歌验证码
        if($agent->google_secret) {
            if (!GoogleAuthenticator::CheckCode($google_secret, $googleCode)){
                return $this->error("验证失败");
            }
        }
        return $this->success("验证成功");
    }
}
