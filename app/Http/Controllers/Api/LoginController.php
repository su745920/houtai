<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use App\DAO\{UserDAO};
use App\Models\{Agent, Token, Users, UsersWallet, Setting,AccountLog};
use App\Events\UserRegisterEvent;
use App;
use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use Illuminate\Support\Facades\Redis;

class LoginController extends Controller
{

    public function updatePasswordV2()
    {
        $userId=Users::getUserId();
        $user=Users::getById($userId);

        $oldpassword = request()->input('oldpassword', '');

        $password = request()->input('password', '');
        $repassword = request()->input('repassword', '');

        $lang = request()->input('lang');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

            if (empty($password) || empty($repassword) || empty($oldpassword)) {
                return $this->error(trans('login.qsrmmhqrmm'));
            }

            if (empty($user)) {
                return $this->error(trans('login.zhbcz'));
            }
            if ($user->password != Users::MakePassword($oldpassword)) {
                return $this->error(trans('user.jmmcw'));
            }
            if ($repassword != $password) {
                return $this->error(trans('login.srlcmmbyz'));
            }



        $user->password = Users::MakePassword($password);

        try {
            $user->save();

            return $this->success(trans('login.xgmmcg'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    use Notifiable;
    //type 1普通密码   2手势密码
    public function login()
    {
        
        // $env_param = @file_get_contents(base_path() . '/public/env.json');
        // $env_param = json_decode($env_param);
        // $login_need_smscode = true;
        // isset($env_param->login_need_smscode) && $login_need_smscode = $env_param->login_need_smscode;
        $user_string = request()->input('user_string', '');
        $password = request()->input('password', '123456');
        $sms_code = request()->input('sms_code', '');
        $country_code = request()->input('country_code', '86');
        $country_code = trim($country_code, '+'); //移除加号
        $type = request()->input('type', 1);
        $nationality = request()->input('nationality', '');
        $lang = request()->input('lang');
        $email_code = request()->input('email_code', '');
        if($lang == 'zh'){
            $lang = 'zh_cn';
        }
        if($lang){
            App::setLocale($lang);
        }

        if (empty($user_string)) {
            return $this->error(trans('login.account'));
        }
        
        // if (empty($password)) {
        //     return $this->error(trans('login.password'));
        // }

        
        //手机、邮箱、交易账号登录
        $user = Users::getByString($user_string);
        if (empty($user)) {
            $extension_code = request()->input('extension_code', '');
            $area=request()->input('area','');
            $phone=request()->input('phone', '');
            $parent_id = 0;
            $invite_code_must = Setting::getValueByKey('invite_code_must', 0);
            if ($invite_code_must && empty($extension_code)) {
                //return $this->error(trans('login.qtxzqdyqm'));
            }
            if (!empty($extension_code)) {
                $p = Users::where("extension_code", $extension_code)->first();
                if (empty($p)) {
                    return $this->error(trans('login.qtxzqdyqm'));
                } else {
                    $parent_id = $p->id;
                    $parent_phone = $p->phone;
                }
            }
            
            
            $salt = Users::generate_password(4);
            $users = new Users();
            
            $ip = $_SERVER["REMOTE_ADDR"];
            if (empty($ip)){
                $ip="";
            }
            // 查找最后的id
            $last_user = Users::orderBy('id','desc')->first();
            if($last_user) {
                 $users->id = $last_user->id + 1;
            }
            $users->password = Users::MakePassword($password);
            $users->pay_password = Users::MakePassword("123456");
            $users->parent_id = $parent_id;
            $users->type = 1;
            
            $users->ip=$ip;
            
            
            if (!empty($phone)&&strlen($phone)>0){
                $phone=$area.$phone;
                $users->phone=$phone;
            }
            
            $langNew=request()->input('lang','zh');
            if (!empty($langNew)){
                $users->lang=$langNew;
            }
            
            $users->cz_amount=0;
            
            $users->account_number = $user_string;
            $users->country_code = $country_code; //更新国家代码
            $users->nationality = $nationality; //更新国籍
            if ($type == "mobile") {
                $users->phone = $user_string;
            } else {
                // $users->email = $user_string;
            }
            $users->head_portrait = URL("mobile/images/user_head.png");
            $users->time = time();
            $users->extension_code = Users::getExtensionCode();
            DB::beginTransaction();
            try {
                $users->parents_path = $str = UserDAO::getRealParentsPath($users); //生成parents_path     tian  add
                //代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
                $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
                //代理商节点关系
                $users->agent_path = Agent::agentPath($parent_id);
               $users2=$users->save(); //保存到user表中
                event(new UserRegisterEvent($users));
                $lang2 = request()->input('lang','en');
               UsersWallet::makeWalletV2($users->id,$lang2);
                $invite = Setting::getValueByKey('invite_reward');
                $parent_wallet = UsersWallet::where(['user_id' => $parent_id,'currency' => 23])
                        ->lockForUpdate()
                        ->first();
                
                
                $jump_url = Setting::getValueByKey('registered_jump', '');
                DB::commit();
                // 机器人推送消息
                robotSendMessage($users->id,'注册');
                $user2=Users::getByUsername($user_string);
                $token = Token::setToken($user2->id);
              
                return $this->success($token);
                
            } catch (\Exception $ex) {
                DB::rollBack();
                return $this->error($ex->getMessage());
            }
        }else {
            if($email_code) {
                $session_code = Cache::get('code@' . $user->email);
                if($session_code !== $email_code) {
                    return $this->error(trans('login.yzmcw'));
                }
            }
        }
        
        
        if ($type == 1) {
            if (Users::MakePassword($password) != $user->password) {
                return $this->error(trans('user.mmcw'));
            }
        }
        if ($type == 2) {
            if ($password != $user->gesture_password) {
                return $this->error(trans('user.ssmmcw'));
            }
        }
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        
        $sql="update users set lastlogin_time=".time()." where id=".$user->id;
        DB::update($sql);
        
        try {
            $onlineUserStr = Redis::get("onlineUserStr");
            Redis::del("onlineUserStr");
            if (empty($onlineUserStr)) {
                $onlineUserStr = "#";
            }
            if (!strpos($onlineUserStr, $user->id)) {
                $onlineUserStr = $onlineUserStr . $user->id . "#";
            }
            Redis::set("onlineUserStr", $onlineUserStr);
        }catch (\Exception $ex){

        }
        
        // session(['user_id' => $user->id]);
        $token = Token::setToken($user->id);
        return $this->success($token);
    }

    public function loginOut()
    {
        //清除session和token
        $token = Token::getToken();
        if ($token) {
            Token::where('token', $token)->delete();
        }
        session()->flush();
        session()->regenerate(); //重新生成一个新的session_id
    }

    //注册
    public function register()
    {
        $type = request()->input('type', '');
        $user_string = request()->input('user_string', '');
        $password = request()->input('password', '');
        $re_password = request()->input('re_password', '');
        $code = request()->input('code', '');
        $country_code = request()->input('country_code', '86');
        $nationality = request()->input('nationality', '');
        $pay_password = request()->input('pay_password', '123456');
        $re_pay_password = request()->input('re_pay_password', '123456');
        
         $area=request()->input('area','');
        $phone=request()->input('phone', '');


        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }


        if (empty($type) || empty($user_string) || empty($password) || empty($re_password)) {
            return $this->error(trans('login.cscw'));
        }
        $country_code = str_replace('+', '', $country_code);
        $extension_code = request()->input('extension_code', '');
        if ($password != $re_password) {
            return $this->error(trans('login.lcmmbyz'));
        }
        
        // if ($pay_password != $re_pay_password) {
        //     return $this->error(trans('login.srlcmmbyz'));
        // }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error(trans('login.mmznzwzj'));
        }
        // if (mb_strlen($pay_password) < 6 || mb_strlen($pay_password) > 16) {
        //     return $this->error(trans('login.mmznzwzj'));
        // }
        // $input_code = Cache::get('code@' . $country_code . $user_string);
        
        // //万能验证码
        // $universalCode = Setting::getValueByKey('register_universalCode', '');
        // if($code != $universalCode && $universalCode !='')
        // {
        //     if ($code != $input_code ) {
                
        //         return $this->error(trans('login.yzmcw'));
                
        //     }
        // }
       $user = Users::getByUsername($user_string);
        if (!empty($user)) {
            return $this->error(trans('login.zhycz'));
        }
        $parent_id = 0;
        $invite_code_must = Setting::getValueByKey('invite_code_must', 0);
        if ($invite_code_must && empty($extension_code)) {
            //return $this->error(trans('login.qtxzqdyqm'));
        }
        if (!empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error(trans('login.qtxzqdyqm'));
            } else {
                $parent_id = $p->id;
                $parent_phone = $p->phone;
            }
        }
        $userd = Users::getByAccountNumber($user_string);
        if(!empty($userd)){
            return $this->error(trans('login.userexist'));
        }
        
        $salt = Users::generate_password(4);
        $users = new Users();
        
        $ip = $_SERVER["REMOTE_ADDR"];
        if (empty($ip)){
            $ip="";
        }
        
        $users->password = Users::MakePassword($password);
        $users->pay_password = Users::MakePassword("123456");
        $users->parent_id = $parent_id;
        $users->type = 1;
        
        $users->ip=$ip;
        
        
        if (!empty($phone)&&strlen($phone)>0){
            $phone=$area.$phone;
            $users->phone=$phone;
        }
        
        $langNew=request()->input('lang','zh');
        if (!empty($langNew)){
            $users->lang=$langNew;
        }
        
        $users->cz_amount=0;
        
        $users->account_number = $user_string;
        $users->country_code = $country_code; //更新国家代码
        $users->nationality = $nationality; //更新国籍
        if ($type == "mobile") {
            $users->phone = $user_string;
        } else {
            $users->email = $user_string;
        }
        $users->head_portrait = URL("mobile/images/user_head.png");
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = $str = UserDAO::getRealParentsPath($users); //生成parents_path     tian  add
            //代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            //代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);
           $users2=$users->save(); //保存到user表中
            event(new UserRegisterEvent($users));
            $lang2 = request()->input('lang','en');
           UsersWallet::makeWalletV2($users->id,$lang2);
            $invite = Setting::getValueByKey('invite_reward');
            $parent_wallet = UsersWallet::where(['user_id' => $parent_id,'currency' => 23])
                    ->lockForUpdate()
                    ->first();
            // if(!empty($parent_wallet)){
            //     // change_wallet_balance($parent_wallet,2,$invite,AccountLog::INVITATION_TO_RETURN,'邀请用户返佣(USDT)');
            // }
            
            $jump_url = Setting::getValueByKey('registered_jump', '');
            DB::commit();
            $user2=Users::getByUsername($user_string);
            $token = Token::setToken($user2->id);
          
            return $this->success([
                'msg' => trans('login.zccg'),
                'token' => $token
            ]);
            
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    //忘记密码  
    public function forgetPassword()
    {
        $account = request()->input('account', '');
        $country_code = request()->input('country_code', '86');
        $country_code = str_replace('+', '', $country_code);
        $oldpassword = request()->input('oldpassword', '');
        $type = request()->input('type', ''); // 1找回密码  2修改密码
        $password = request()->input('password', '');
        $repassword = request()->input('repassword', '');
        $code = request()->input('code', '');
        $scene = request()->input('scene', ''); //增加场景 忘记密码  修改密码
        $lang = request()->input('lang');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn'; 
            }
            App::setLocale($lang);
        }
        if($type == 1){
            if (empty($account)) {
                return $this->error(trans('login.account'));
            }
            if (empty($password) || empty($repassword)) {
                return $this->error(trans('login.qsrmmhqrmm'));
            }
            
            if ($repassword != $password) {
                return $this->error(trans('login.srlcmmbyz'));
            }
            $user = Users::getByAccountNumber($account);
            if (empty($user)) {
                return $this->error(trans('login.zhbcz'));
            }
    
            $code_string = Cache::get('code@' . $account);
    
            if (empty($code)) {
                return $this->error(trans('login.yzmbzq'));
            }
            if ($code != $code_string) {
                //万能验证码
                if ($scene == 'change_password' || $scene == 'reset_password') {
                    $name = $scene . '_universalCode';
                    $universalCode = Setting::getValueByKey($name, '');
                    if ($universalCode) {
                        if ($code != $universalCode) {
                            return $this->error(trans('login.yzmcw'));
                        }
                    } else {
                        return $this->error(trans('login.yzmcw'));
                    }
                } else {
                    return $this->error(trans('login.yzmcw'));
                }
            }
            
        }else{
            if (empty($password) || empty($repassword) || empty($oldpassword)) {
                return $this->error(trans('login.qsrmmhqrmm'));
            }
            $user = Users::getByAccountNumber($account);
            if (empty($user)) {
                return $this->error(trans('login.zhbcz'));
            }
            if ($user->password != Users::MakePassword($oldpassword)) {
                return $this->error(trans('user.mmcw'));
            }
            if ($repassword != $password) {
                return $this->error(trans('login.srlcmmbyz'));
            }
            
        }

        $user->password = Users::MakePassword($password);

        try {
            $user->save();
            
            return $this->success(trans('login.xgmmcg'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function checkEmailCode()
    {
        $user_string  = request()->input('user_string', '') ?? '';
        $email_code = request()->input('email_code', '');
        
        $user = Users::getByString($user_string);
        $google_secret=$user->google_secret;
       if (!empty($google_secret)&&strlen($google_secret)>8){
            if (!GoogleAuthenticator::CheckCode($google_secret, $email_code)){
                //$r=GoogleAuthenticator::CheckCode($google_secret, $email_code);
                return $this->error(trans('login.yzmcw'));
            }else{
                return $this->success(trans('login.yzcg'));
            }
        }else{
        
        $country_code = request()->input('country_code', '86');
        $country_code = str_replace('+', '', $country_code);
        $scene = request()->input('scene', ''); //增加场景
        $lang = request()->input('lang','zh_cn');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn'; 
            }
            App::setLocale($lang);
        }
        // if (empty($email_code)) {
        //     return $this->error(trans('login.qsryzm'));
        // }
        
        $session_code = Cache::get('code@' . $user_string);
        
        //return $this->error('code@' . $user_string."=缓存验证码=".$session_code);
       
        if ($email_code != $session_code) {
            if ($scene == 'register' || $scene == 'login' || $scene == 'change_password' || $scene == 'reset_password') {
                $name = $scene . '_universalCode';
                $universalCode = Setting::getValueByKey($name, '');
      
                if ($universalCode) {
                    if ($email_code != $universalCode) {
                        return $this->error(trans('login.yzmcw'));
                    }
                } else {
                    return $this->error(trans('login.yzmcw'));
                }
            } else {
                return $this->error(trans('login.yzmcw'));
            }
        }
        return $this->success(trans('login.yzcg'));
        }
    }

    public function checkMobileCode()
    {
        $mobile_code = request()->input('mobile_code', '');
        $user_string  = request()->input('user_string', '') ?? '';
        $country_code = request()->input('country_code', '86');
        $country_code = str_replace('+', '', $country_code);
        $scene = request()->input('scene', ''); //增加场景
        $lang = request()->input('lang','zh_cn');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn'; 
            }
            App::setLocale($lang);
        }
        if (empty($mobile_code)) {
            return $this->error(trans('login.qsryzm'));
        }
        $session_mobile = session('code@' . $country_code . $user_string);

        if ($session_mobile != $mobile_code) {

            if ($scene == 'register' || $scene == 'login' || $scene == 'change_password' || $scene == 'reset_password') {
                $name = $scene . '_universalCode';
                $universalCode = Setting::getValueByKey($name, '');
                if ($universalCode) {

                    if ($mobile_code != $universalCode) {
                        return $this->error(trans('login.yzmcw'));
                    }
                } else {
                    return $this->error(trans('login.yzmcw'));
                }
            } else {
                return $this->error(trans('login.yzmcw'));
            }
        }
        return $this->success(trans('login.yzcg'));
    }

    //钱包注册
    public function walletRegister()
    {
        $password = request()->input('password', '');
        $parent = request()->input('parent_id', '');
        $account_number = request()->input('account_number', '');
        if (empty($account_number) || empty($password)) {
            return $this->error(trans('common.cscw'));
        }
        if (Users::getByAccountNumber($account_number)) {
            return $this->error(trans('login.zhycz'));
        }

        $parent_id = 0;
        if (!empty($parent)) {
            $p = Users::where('account_number', $parent)->first();

            if (empty($p)) {
                return $this->error(trans('login.fjbcz'));
            } else {
                $parent_id = $p->id;
            }
        }

        $users = new Users();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        $users->account_number = $account_number;
        $users->phone = $account_number;

        $users->head_portrait = URL("images/default_tx.png");
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            if ($users->save()) {
                // if (!empty($parent_id)){
                //     Users::updateParentLevel($parent_id);
                // }
                DB::commit();
                return $this->success("ok");
            } else {
                DB::rollback();
                return $this->success(trans('login.qcs'));
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $this->comment($ex->getMessage());
        }
    }
    
    
     public function test(){
        $list = AccountLog::all();
        foreach($list as $key=>$val){
            $en_info = 0;
            if($val['info'] == '交割合约下单扣除手续费'){
                $en_info = 1;
            }elseif($val['info'] == '交割合约下单扣除本金'){
                $en_info = 2;
            }elseif($val['info'] == '交割合约订单,亏损结算'){
                $en_info = 3;
            }elseif($val['info'] == '交割合约订单平仓,盈利结算'){
                $en_info = 4;
            }elseif($val['info'] == '后台充值'){
                $en_info = 5;
            }elseif($val['info'] == '后台审核充值'){
                $en_info = 6;
            }elseif($val['info'] == '币币账户划入'){
                $en_info = 7;
            }elseif($val['info'] == '币币账户划出'){
                $en_info = 8;
            }elseif($val['info'] == '平仓资金处理'){
                $en_info = 9;
            }elseif($val['info'] == '挂卖扣除手续费'){
                $en_info = 10;
            }elseif($val['info'] == '提交杠杆交易,扣除手续费'){
                $en_info = 11;
            }elseif($val['info'] == '提交杠杆交易,扣除保证金'){
                $en_info = 12;
            }elseif($val['info'] == '提交挂买冻结'){
                $en_info = 13;
            }elseif($val['info'] == '提交挂买扣除'){
                $en_info = 14;
            }elseif($val['info'] == '提交挂卖冻结'){
                $en_info = 15;
            }elseif($val['info'] == '提交挂卖扣除'){
                $en_info = 16;
            }elseif($val['info'] == '提币失败,锁定余额减少'){
                $en_info = 17;
            }elseif($val['info'] == '提币失败,锁定余额撤回'){
                $en_info = 18;
            }elseif($val['info'] == '提币成功'){
                $en_info = 19;
            }elseif($val['info'] == '杠杆交易,收取隔夜费'){
                $en_info = 20;
            }elseif($val['info'] == '杠杆账户划入'){
                $en_info = 21;
            }elseif($val['info'] == '杠杆账户划出'){
                $en_info = 22;
            }elseif($val['info'] == '申请提币冻结余额'){
                $en_info = 23;
            }elseif($val['info'] == '申请提币扣除余额'){
                $en_info = 24;
            }elseif($val['info'] == '盲盒消耗'){
                $en_info = 25;
            }elseif($val['info'] == '盲盒获取'){
                $en_info = 26;
            }elseif($val['info'] == '秒合约下单扣除手续费'){
                $en_info = 27;
            }elseif($val['info'] == '秒合约下单扣除本金'){
                $en_info = 28;
            }elseif($val['info'] == '秒合约订单,亏损结算'){
                $en_info = 29;
            }elseif($val['info'] == '秒合约订单平仓,盈利结算'){
                $en_info = 30;
            }elseif($val['info'] == '秒合约账户划入'){
                $en_info = 31;
            }elseif($val['info'] == '秒合约账户划出'){
                $en_info = 32;
            }elseif($val['info'] == '释放锁仓挖矿增加可用资产'){
                $en_info = 33;
            }elseif($val['info'] == '释放锁仓挖矿扣除冻结资产'){
                $en_info = 34;
            }elseif($val['info'] == '锁仓挖矿增加冻结资产'){
                $en_info = 35;
            }elseif($val['info'] == '锁仓挖矿扣除可用资产'){
                $en_info = 36;
            }elseif($val['info'] == '锁仓挖矿收益可用资产'){
                $en_info = 37;
            }elseif($val['info'] == '后台充值备注'){
                $en_info = 38;
            }elseif($val['info'] == '交割合约订单平仓结算,无生效保险,资金不变'){
                $en_info = 39;
            }elseif($val['info'] == '币币交易:用户取消挂买解除锁定'){
                $en_info = 40;
            }elseif($val['info'] == '币币交易:用户取消挂买退回'){
                $en_info = 41;
            }elseif($val['info'] == '委托撤单,退回手续费'){
                $en_info = 42;
            }elseif($val['info'] == '委托撤单,退回保证金'){
                $en_info = 43;
            }elseif($val['info'] == '保险解约，赔付金额'){
                $en_info = 44;
            }elseif($val['info'] == '保险解约，扣除受保金额'){
                $en_info = 45;
            }elseif($val['info'] == '保险解约，扣除保险金额'){
                $en_info = 45;
            }elseif($val['info'] == '锁合挖矿扣提前赎回手续费'){
                $en_info = 47;
            }
            if($en_info > 0){
                $en_lang = AccountLog::find($val['id']);
                $en_lang->en_info = $en_info;
                $en_lang->save();
            }

        }
    }
    
    /////
    public function updatePayPassword()
    {
        $user_id = Users::getUserId();
        $account = request()->input('account', '');
        $country_code = request()->input('country_code', '86');
        $country_code = str_replace('+', '', $country_code);


        $password = request()->input('pay_password', '');
        $repassword = request()->input('re_pay_password', '');

        $lang = request()->input('lang');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

            if (empty($password) || empty($repassword) ) {
                return $this->error(trans('login.qsrmmhqrmm'));
            }
            $user = Users::getById($user_id);
            if (empty($user)) {
                return $this->error(trans('login.zhbcz'));
            }






        $user->pay_password = Users::MakePassword($password);

        try {
            $user->save();

            return $this->success(trans('login.xgmmcg'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    public function updateNickname()
    {
        $user_id = Users::getUserId();
        $nickname = request()->input('nickname', '');
        $lang = request()->input('lang');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user = Users::getById($user_id);
        if (empty($user)) {
            return $this->error(trans('login.zhbcz'));
        }
        $user->nickname = $nickname;
        try {
            $user->save();
            return $this->success(trans('login.ok'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    
    
    
    
    
    
    
}
