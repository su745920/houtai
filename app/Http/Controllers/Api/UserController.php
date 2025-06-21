<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\{AppApi, UserChat, Users, UserReal, Token, AccountLog, UsersWallet, Currency, InviteBg, Setting, UserCashInfo, ExchangeShiftTo,Mail,WalletAddress,WalletAddressList,WalletLog};
use GuzzleHttp\Client;
use App;

class UserController extends Controller
{
    public function uploadHeadPortrait()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $user_id = Users::getUserId();
        $head_portrait = request()->input("head_portrait", ""); //真实姓名


        try {
            $sql="update users set head_portrait='".$head_portrait."'  where  id=".$user_id;
            DB::update($sql);
            return $this->success(trans('user.bccg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function realState(Request $request)
    {
        $user_id = Users::getUserId();
        $real_status = 0;
        $review_status = 0;
        $advanced_review_status = 0;

        $real_data = DB::table('user_real')->where('user_id',$user_id)->orderBy("id","desc")
            ->first();
        if (!empty($real_data)){
            if ($real_data->review_status == 1){
                $real_status = 1;
                $review_status = 1;
            }
            if ($real_data->review_status == 2 && $real_data->advanced_user == 0){
                $real_status = 1;
                $review_status = 2;
            }
            if ($real_data->review_status == 1 && $real_data->advanced_user == 1){
                $real_status = 2;
                $review_status = 2;
                $advanced_review_status = 1;
            }
            if ($real_data->advanced_user == 2){
                $real_status = 2;
                $review_status = 2;
                $advanced_review_status = 2;
            }
        }

        $result = compact('real_status','review_status','advanced_review_status',"real_data");
        return $this->success($result);
    }
    public function Transfer(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if($user->frozen_funds==1){
            return $this->error('资金已冻结');
        }
        $from_currency_id  = $request->get('from_currency_id',''); //被划转币种
        $to_currency_id  = $request->get('to_currency_id','');//划转目标币种
        $number = $request->get('number',0);//划转数量
        if(Cache::has("on_the_way_$user_id")){
            return $this->error('Do not repeat the operation!'); 
        }
        Cache::put("on_the_way_$user_id", 1, Carbon::now()->addSeconds(3));
        if(!$from_currency_id || !$to_currency_id|| !$number || !$user_id){
            return $this->error('error, Parameter is empty!'); 
        }
        
        if($from_currency_id==$to_currency_id){
            return $this->error('The same currency cannot be transferred!');  //相同币种不可划转
        }
        $from_currency = Currency::where('id',$from_currency_id)->where('is_transfer',1)->first();
        $to_currency = Currency::where('id',$to_currency_id)->where('is_transfer',1)->first();
        $from_user_walllet_currency=UsersWallet::where("user_id","=",$user_id)->where("currency","=",$from_currency_id)->first();
        $to_user_walllet_currency=UsersWallet::where("user_id","=",$user_id)->where("currency","=",$to_currency_id)->first();
        if(!$from_user_walllet_currency || !$to_user_walllet_currency){
            return $this->error('User wallet does not exist'); 
        }
        if(!$to_currency){
            return $this->error('This currency ['.$to_currency.'] does not support transfers'); 
        }
        if(!$from_currency){
            return $this->error('This currency ['.$from_currency.'] does not support transfers'); 
        }
        
        
        $f_new_price = $from_currency['price'];//最新币种
        $t_new_price = $to_currency['price'];//最新币种
        $transfer_fee = Setting::getValueByKey('transfer_fee',0);//获取手续费比例
        $form_amount  = bc_mul($f_new_price ,$number);
        $to_amount = bc_div($form_amount, $t_new_price);
        if($transfer_fee<=0){
            $transfer_money = 0;
        }else{
            $transfer_money = bc_div(bc_div(bc_mul($transfer_fee,$form_amount),100),$f_new_price);
        }
        
        if($to_amount<=0 || $number<=0){
            return $this->error('Numerical error!'); 
        }
        if($transfer_money<0){
            $transfer_money = 0;
        }
        
        DB::beginTransaction();
        try{
            change_wallet_balance($from_user_walllet_currency , 1 , -$number , AccountLog::USER_EXCHANGE_DOWN,'划转减少' . $from_user_walllet_currency->name,AccountLog::USER_EXCHANGE_DOWN,'Decreased transfer ' . $from_user_walllet_currency->name);
            change_wallet_balance($to_user_walllet_currency , 1 , $to_amount , AccountLog::USER_EXCHANGE_ADD,'划转增加' . $to_user_walllet_currency->name,AccountLog::USER_EXCHANGE_ADD,'Transfer increase ' . $to_user_walllet_currency->name);
            change_wallet_balance($from_user_walllet_currency , 1 , -$transfer_money ,AccountLog::USER_EXCHANGE_FEE, '手续费' . $from_user_walllet_currency->name,AccountLog::USER_EXCHANGE_FEE, 'Service Charge ' . $from_user_walllet_currency->name); //从被划转的币种扣除
            DB::commit();
            return $this->success('Successful transfer!');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
        
        
    }
    
    public function getTransferList(){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        if(!$user_id){
            return $this->error('error!'); 
        }
        
        $get_transfer_list = Currency::where('is_transfer',1)->get(['id','name'])->toArray();
        UsersWallet::leftjoin("currency", "currency.id", "users_wallet.currency")
            ->where("users_wallet.user_id", $user_id)
            ->where("currency.is_transfer", 1)
            ->select();
        //$user_wallet = UsersWallet::where("user_id","=",$user_id)->get(['currency','change_balance','lever_balance'])->toArray();
        $user_wallet = UsersWallet::where("user_id","=",$user_id)->get(['currency','change_balance','lever_balance','legal_balance','earn_balance','micro_balance'])->toArray();
        
        return $this->success(['transfer'=>$get_transfer_list??[],'wallet'=>$user_wallet]);
    }
    
    
    
    
    
    
    public function updatePayPwdFirst()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $password = request()->input('pay_password', '');
        $re_password = request()->input('re_pay_password', '');
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error(trans('user.mmznz').'6-16'.trans('user.wzj'));
        }
        if ($password != $re_password) {
            return $this->error(trans('user.lcmmbyz'));
        }
        $user->pay_password = Users::MakePassword($password);
        try {
            $user->save();
            return $this->success(trans('user.jymmszcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    public function hasSetPayPwd(){
        $user_id = Users::getUserId();
        $po=Users::find($user_id);
        $pay_password=$po->pay_password;
        if (!empty($pay_password)){
            $jo["hasSetPayPwd"]=1;
            return $this->success($jo);
        }else{
            $jo["hasSetPayPwd"]=-1;
            return $this->success($jo);
        }
    }
    
    public function getUsdt()
    {
        $user_id = Users::getUserId();

        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();

       
        $usdt= isset($wal->lever_balance) ? $wal->lever_balance : '0.00000';
        $jo["usdt"]=$usdt;
        return $this->success($jo);
    }
      /*
     * 获得语言种类(默认是英文)
     * */
    public function languagetypes(){

        $user_id = Users::getUserId();
        $key = 'lan_type_'.$user_id;

        $type = Input::get('lang','zh_cn');

        if($type != Cache::get($key)){

            Cache::put($key,$type,24 * 3600 * 365);
        }
        App::setLocale($type);
    }
    //添加/修改收款方式
    public function saveCashInfo(Request $request)
    {
        $address=$request->input('address','');
        $bank_name = $request->input('bank_name', ''); //开户行
        $bank_account = $request->input('bank_account', ''); //银行账号
        $whatsapp = $request->input('whatsapp', ''); //真实姓名,渲染出来
        $real_name = $request->input('real_name', ''); //真实姓名,渲染出来
        $alipay_account = $request->input('alipay_account', ''); //支付宝账号
        $alipay_collect = $request->input('alipay_collect', ''); //支付宝收款码
        $wechat_nickname = $request->input('wechat_nickname', ''); //微信昵称
        $wechat_account = $request->input('wechat_account', ''); //微信账号
        $wechat_collect = $request->input('wechat_collect', ''); //微信收款码
        $user_id = Users::getUserId();
        if (empty($real_name)) {
            return $this->error(trans('user.zsxmbxtx'));
        }
        if (
            (empty($bank_name) || empty($bank_account))
            && (empty($wechat_nickname) || empty($wechat_account) || empty($wechat_collect))
            && (empty($alipay_account) || empty($alipay_collect))
        ) {
            return $this->error(trans('user.skxxzsxtyx'));
        }
        if (empty($user_id)) {
            return $this->error(trans('user.cscw'));
        }
        $cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (empty($cash_info)) {
            $cash_info = new UserCashInfo();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        }
        if (!empty($bank_name)) {
            $cash_info->bank_name = $bank_name;
        }
        if (!empty($whatsapp)) {
            $cash_info->whatsapp = $whatsapp;
        }
        if (!empty($bank_account)) {
            $cash_info->bank_account = $bank_account;
        }
        if (!empty($address)){
            $cash_info->address=$address;
        }

        $cash_info->real_name = $real_name;
        if (!empty($alipay_account)) {
            $cash_info->alipay_account = $alipay_account;
        }
        if (!empty($wechat_account)) {
            $cash_info->wechat_account = $wechat_account;
        }
        if (!empty($wechat_nickname)) {
            $cash_info->wechat_nickname = $wechat_nickname;
        }
        if (!empty($alipay_collect)) {
            $cash_info->alipay_collect = $alipay_collect;
        }
        if (!empty($wechat_collect)) {
            $cash_info->wechat_collect = $wechat_collect;
        }
        try {
            $cash_info->save();
            
            //return $this->success(trans('user.bccg'));
            if ($lang=="vi"){
                return $this->success("đã lưu thành công");
            }else{
                return $this->success(trans('user.bccg'));
            }
            
            
            
            
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    
    //多添加一个line收款功能
    public function saveCash(Request $request){
        $address=$request->input('address','');
        $bank_name = $request->input('bank_name', ''); //开户行
        $bank_account = $request->input('bank_account', ''); //银行账号
        $real_name = $request->input('real_name', ''); //真实姓名,渲染出来
        $alipay_account = $request->input('alipay_account', ''); //支付宝账号
        $alipay_collect = $request->input('alipay_collect', ''); //支付宝收款码
        $wechat_nickname = $request->input('wechat_nickname', ''); //微信昵称
        $wechat_account = $request->input('wechat_account', ''); //微信账号
        $wechat_collect = $request->input('wechat_collect', ''); //微信收款码
        $line_account = $request->input('line_account','');//Line账号
        $user_id = Users::getUserId();
        if (empty($real_name)) {
            return $this->error(trans('user.zsxmbxtx'));
        }
        if (
            (empty($bank_name) || empty($bank_account))
            && (empty($wechat_nickname) || empty($wechat_account) || empty($wechat_collect))
            && (empty($alipay_account) || empty($alipay_collect))
        ) {
            return $this->error(trans('user.skxxzsxtyx'));
        }
        if (empty($user_id)) {
            return $this->error(trans('user.cscw'));
        }
        $cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (empty($cash_info)) {
            $cash_info = new UserCashInfo();
            $cash_info->user_id = $user_id;
            $cash_info->create_time = time();
        }
        if (!empty($address)){
            $cash_info->address = $address;
        }
        if (!empty($bank_name)) {
            $cash_info->bank_name = $bank_name;
        }
        if (!empty($bank_account)) {
            $cash_info->bank_account = $bank_account;
        }
        $cash_info->real_name = $real_name;
        if (!empty($alipay_account)) {
            $cash_info->alipay_account = $alipay_account;
        }
        if (!empty($wechat_account)) {
            $cash_info->wechat_account = $wechat_account;
        }
        if (!empty($wechat_nickname)) {
            $cash_info->wechat_nickname = $wechat_nickname;
        }
        if (!empty($alipay_collect)) {
            $cash_info->alipay_collect = $alipay_collect;
        }
        if (!empty($wechat_collect)) {
            $cash_info->wechat_collect = $wechat_collect;
        }
        if (!empty($line_account)) {
            $cash_info->line_account = $line_account;
        }
        
        try {
            $cash_info->save();
            return $this->success(trans('user.bccg'));
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function checkPayPassword()
    {
        $password = request()->input('password', '');
        $user = Users::getById(Users::getUserId());
        if ($user->pay_password != Users::MakePassword($password)) {
            return $this->error(trans('user.mmcw'));
        } else {
            return $this->success(trans('user.czcg'));
        }
    }

    //获取本人收款方式信息
    public function cashInfo()
    {
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error(trans('user.cscw'));
        }
        $result = UserCashInfo::where('user_id', $user_id)->first();
        return $this->success($result);
    }
    //设置法币交易账号密码
    public function setAccount()
    {
        $account = request()->input('account', '');
        $password = request()->input('password', '');
        $repassword = request()->input('repassword', '');
        if (empty($account) || empty($password) || empty($repassword)) {
            return $this->error(trans('user.btxxxbwz'));
        }
        if ($password != $repassword) {
            return $this->error(trans('user.lcsrmmbyz'));
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error(trans('user.cyhbcz'));
        }
        if ($user->account_number) {
            return $this->error(trans('user.cjyzhyjsz'));
        }
        $res = Users::where('account_number', $account)->first();
        if ($res) {
            return $this->error(trans('user.czhyjcz'));
        }
        try {
            $user->account_number = $account;
            $user->pay_password = Users::MakePassword($password, $user->type);
            $user->save();
            return $this->success(trans('user.jyzhszcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->电话邮箱绑定信息
    public function safeCenter()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $safeInfo = array(
            'mobile' => $user->phone, //如果为空，未绑定
            'email' => $user->email,
            'gesture_password' => $user->gesture_password,
            //手势密码如果存在就是个蓝色的框，默认登录的时候就是手势密码登录
            //再次点击蓝色的框就是取消手势密码，取消就不用手势密码登录，删除字段中的值
            //如果不存在，是个灰色的框。点击之后是重新设置添加手势密码
        );
        return $this->success($safeInfo);
    }
    //安全中心-->绑定电话
    public function setMobile()
    {
        $user_id = Users::getUserId();
        $mobile = request()->input('mobile', '');
        $code = request()->input('code', '');
        if (empty($user_id) || empty($mobile) || empty($code)) {
            return $this->error(trans('user.cscw'));
        }
        if ($code != session('code')) {
            return $this->error(trans('user.yzmcw'));
        }
        try {
            $user = Users::find($user_id);
            $user->phone = $mobile;
            $user->save();
            return $this->success(trans('user.sjmdcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    //安全中心-->绑定邮箱
    public function setEmail()
    {
        $user_id = Users::getUserId();
        $email = request()->input('email', '');
        $code = request()->input('code', '');
        
         $lang = request()->input('lang');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        
        if (empty($user_id) || empty($email) || empty($code)) {
            return $this->error(trans('user.cscw'));
        }
         // if ($code != session('code')) {
        //     return $this->error(trans('user.yzmcw'));
        // }
        if($code !== Cache::get('code@' . $email)) {
            return $this->error(trans('user.yzmcw'));
        }
        try {
            $user = Users::find($user_id);
            $user->email = $email;
            $user->save();
            return $this->success(trans('user.yxmdcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->手势密码-->添加手势密码
    public function gesturePassAdd()
    {
        $password = request()->input('password', ''); //获取的是一个数组[1,2,3]
        $re_password = request()->input('re_password', '');
        if (mb_strlen($password) < 6) {
            return $this->error(trans('user.ssmmzsljlgd'));
        }
        if ($password != $re_password) {
            return $this->error(trans('user.lcssmmbyz'));
        }
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user->gesture_password = $password;
        try {
            $user->save();
            return $this->success(trans('user.ssmmtjcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    //安全中心-->手势密码-->取消手势密码
    public function gesturePassDel()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $user->gesture_password = "";
        try {
            $user->save();
            return $this->success(trans('user.qxssmmcg')); //按钮变成灰色的
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //安全中心-->修改交易密码
    public function updatePayPassword()
    {
        $lang = request()->input('lang','zh_cn');
        if($lang){
            App::setLocale($lang);
        }
        
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $oldpassword =request()->input('oldpassword', '');
        $password = request()->input('pay_password', '');
        $re_password = request()->input('re_pay_password', '');
        $code = request()->input('code', '');
        $country_code = $user->country_code;
        // if ($code == '') {
        //     return $this->error('验证码必须填写');
        // }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error(trans('user.mmznz').'6-16'.trans('user.wzj'));
        }
        if ($password != $re_password) {
            return $this->error(trans('user.lcmmbyz'));
        }

        // if ($code != session('code@' . $country_code . $user->account_number)) {
        //     //万能验证码
        //     $universalCode = Setting::getValueByKey('change_password_universalCode', '');
        //     if ($universalCode) {
        //         if ($code != $universalCode) {
        //             return $this->error('验证码错误');
        //         }
        //     } else {
        //         return $this->error('验证码错误');
        //     }
        // }

       $hasPayPwd=0;
       $pay_password=$user->pay_password;
        if (!empty($pay_password)){
            $hasPayPwd=1;
        }
        if ($hasPayPwd==1) {
          if (Users::MakePassword($oldpassword) != $user->pay_password) {
               return $this->error(trans('user.jmmcw'));
          }
        }

        $user->pay_password = Users::MakePassword($password);
        try {
            $user->save();
            return $this->success(trans('user.jymmszcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //邀请返佣榜单  前20名
    public function inviteList()
    {
        $time = request()->input('time', ''); //邀请返佣时间段
        if ($time) {
            $time = strtotime($time);
        } else {
            $time = 0;
        }


        $list = AccountLog::has('user')
            ->select(DB::raw('sum(value) as total, user_id'))
            ->where('type', AccountLog::INVITATION_TO_RETURN)
            ->where('created_time', '>=', $time)
            ->groupBy('user_id')
            ->orderBy('total', 'desc')

            ->limit(20)
            ->get()
            ->toArray();

        if (empty($list)) {
            return $this->error(trans('user.zsmyyqphb'));
        }


        foreach ($list as $key => $val) {

            $user = Users::find($val['user_id']);


            $list[$key]['account'] = $user->account;
        }

        return $this->success($list);
    }


    //邀请 
    public function invite()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }


        //邀请排行榜 前3
        $list = AccountLog::has('user')
            ->select(DB::raw('sum(value) as total, user_id'))
            ->where('type', AccountLog::INVITATION_TO_RETURN)

            ->groupBy('user_id')
            ->orderBy('total', 'desc')

            ->limit(3)
            ->get()
            ->toArray();
        if (empty($list)) {
            $list = [];
        } else {

            foreach ($list as $key => $val) {

                $users = Users::find($val['user_id']);

                $list[$key]['account'] = $users->account;
            }
        }

        //邀请广告图片及链接 
        $ad = [];
        $ad['image'] = "/upload/invite.png";

        $data = [];
        $data['extension_code'] = $user['extension_code'];
        $data['ad'] = $ad;
        $data['inviteList'] = $list;


        //获取用户邀请人数
        //邀请返佣金数量
        $num = Users::where('parent_id', $user_id)->count();

        if ($num > 0) {
            $data['invite_num'] = $num;
            $total = AccountLog::where('user_id', $user_id)->where('type', AccountLog::INVITATION_TO_RETURN)->sum('value');
            $data['invite_return_total'] = $total;
        } else {
            $data['invite_num'] = 0;
            $data['invite_return_total'] = 0;
        }

        return $this->success($data);
    }


    //我的邀请记录  0启用  1禁用 2全部 
    public function myInviteList()
    {

        $status = request()->input('status', 2); //邀请会员状态
        
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }

        $list = Users::where('parent_id', $user_id);
        if ($status != 2) {
            $list = $list->where('status', $status);
        }
        $list = $list->orderBy('id', 'desc')->get()->toArray();

        return $this->success($list);
    }

    //我的返佣记录
    public function myAccountReturn()
    {

        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first();

        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }

        $time = request()->input('time', ''); //邀请返佣时间段
        if ($time) {
            $time = strtotime($time);
        } else {
            $time = 0;
        }


        $list = AccountLog::where('user_id', $user_id)

            ->where('type', AccountLog::INVITATION_TO_RETURN)
            ->where('created_time', '>=', $time)

            ->orderBy('id', 'desc')
            ->get()
            ->toArray();


        return $this->success($list);
    }

    //我的  
    public function info()
    {
        $user_id = Users::getUserId();
        //$user = Users::where("id",$user_id)->first(['id','phone','email','head_portrait','status']);

        try {
            $user = Users::where("id", $user_id)->first();
            if (empty($user)) {
                throw new \Exception(trans('user.hywzd'));
            }

            //用户认证状况
            $res = UserReal::where('user_id', $user_id)->first();
            if (empty($res)) {
                $user['review_status'] = 0;
                $user['name'] = '';
            } else {
                $user['review_status'] = $res['review_status'];
                $user['name'] = $res['name'];
            }
            $user['backgroud_image'] = Setting::getValueByKey('backgroud_image', '');   
            
            
            //查询初级认证情况
            $review_status=0;
            $userRealPO=UserReal::where('user_id',$user_id)->first();
            if (!empty($userRealPO)){
                if(isset($userRealPO->review_status)){
                    $review_status=$userRealPO->review_status;
                    $user["review_status"]=$review_status;
                }
            }
            
            $pay_password=$user->pay_password;
            $hasPayPwd=0;
            if (!empty($pay_password)){
                $hasPayPwd=1;
            }
            $user->hasPayPwd=$hasPayPwd;
            
            // 获取站内信未读数量
            $mail_num = Mail::where('user_id',$user_id)->where('is_read',0)->count();
            $user['mail_num'] = $mail_num;
            
            
            return $this->success($user);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    //身份认证
    public function realName()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $name = request()->input("name", ""); //真实姓名
        $card_id = request()->input("card_id", ""); //身份证号
        $front_pic = request()->input("front_pic", ""); //正面照片
        $reverse_pic = request()->input("reverse_pic", ""); //反面照片
        $hand_pic = request()->input("hand_pic", ""); //手持身份证照片
        $certificate_type = request()->input("certificate_type", "");

        if (empty($name) || empty($card_id) || empty($front_pic) || empty($reverse_pic)) {
            return $this->error(trans('user.qtjwzxx'));
        }
        //        if (empty($name) || empty($card_id) || empty($front_pic) || empty($reverse_pic)) {
//            return $this->error(trans('user.qtjwzxx'));
//        }
        if (empty($front_pic) || empty($reverse_pic)) {//身份证和姓名已经在基础版那里认证了
            //return $this->error(trans('user.qtjwzxx'));
        }

        //校验  身份证号码合法性
        /*
        $idcheck = new IdCardIdentity();
        $res = $idcheck->check_id($card_id);
        if (!$res) {
            return $this->error("请输入合法的身份证号码");
        }
        */
        // $userreal_number = UserReal::where('card_id', $card_id)->count();
        // if ($userreal_number > 0) {
        //     return $this->error(trans('user.gsfzhysy'));
        // }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }
        // 判断是否绑定了邮箱
        if(empty($user->email)) {
            return $this->error(trans('user.qxbdyx'));
        }
        $userreal = UserReal::where('user_id', $user_id)->where('review_status',1)->first();
        if (!empty($userreal)) {
            return $this->error(trans('user.nyjsqgl'));
        }
        try {
            $is_userreal= UserReal::where('user_id', $user_id)->where('review_status',3)->first();
            if($is_userreal){
                $is_userreal->user_id = $user_id;
                $is_userreal->name = $name;
                $is_userreal->card_id = $card_id;
                $is_userreal->create_time = time();
                $is_userreal->front_pic = $front_pic;
                $is_userreal->reverse_pic = $reverse_pic;
                //$is_userreal->hand_pic = $hand_pic;
                $is_userreal->certificate_type = $certificate_type;
                $is_userreal->review_status = 1;
                $is_userreal->save();
            }else{
                $userreal = new UserReal();
                $userreal->user_id = $user_id;
                $userreal->name = $name;
                $userreal->card_id = $card_id;
                $userreal->create_time = time();
                $userreal->front_pic = $front_pic;
                $userreal->reverse_pic = $reverse_pic;
                $userreal->advanced_user = 1;
                //$userreal->hand_pic = $hand_pic;
                $userreal->certificate_type = $certificate_type;
                $userreal->auth_status = 2;
                $userreal->save();
                // 机器人推送消息
                // robotSendMessage($user_id,'高级认证');
                robotSendMessage($user_id,'实名认证');
            }
            Redis::set('rel_tip_count', 1);

            return $this->success(trans('user.tjcgddsh'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }


    //个人中心  身份认证信息  
    public function userCenter()
    {
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'phone', 'email']);
        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }
        $userreal = UserReal::where('user_id', $user_id)->first();

        if (empty($userreal)) {
            $user['review_status'] = 0;
            $user['name'] = '';
            $user['card_id'] = '';
        } else {
            $user['review_status'] = $userreal['review_status'];
            $user['name'] = $userreal['name'];
            $user['card_id'] = $userreal['card_id'];
        }

        if (!empty($user['card_id'])) {
            $user['card_id'] = mb_substr($user['card_id'], 0, 2) . '******' . mb_substr($user['card_id'], -2, 2);
        }
        return $this->success($user);
    }

    //专属海报信息
    public function posterBg()
    {
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'extension_code']);
        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }
        $pics = InviteBg::all(['id', 'pic'])->toArray();

        $data['extension_code'] = $user['extension_code'];
        $data['share_url'] = Setting::getValueByKey('share_url', '');
        $data['pics'] = $pics;

        return $this->success($data);
    }

    //我的邀请分享
    public function share()
    {
        $user_id = Users::getUserId();
        $user = Users::where("id", $user_id)->first(['id', 'extension_code']);
        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }

        $data['share_title'] = Setting::getValueByKey('share_title', '');
        $data['share_content'] = Setting::getValueByKey('share_content', '');
        $data['share_url'] = Setting::getValueByKey('share_url', '');
        $data['extension_code'] = $user['extension_code'];

        return $this->success($data);
    }


    //退出  
    public function logout()
    {

        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $str=trans('user.tcdlcg');

        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }
        //清除用户session
        session()->flush();
        session()->regenerate(); //重新生成一个新的session_id
        $token = Token::getToken();
        //删除当前token 
        Token::deleteToken($user_id, $token);
        
        //清空 redis在线状态
        try {
            Redis::del("" . $user_id);
        }catch (\Exception $e){
            
        }
        
       return $this->success($str);
    }

    public function vip()
    {
        $user_id = Users::getUserId(request()->input("user_id"));
        $password = request()->input('password', '');


        if (empty($password)) return $this->error(trans('wallet.qsrzfmm'));

        $vip = request()->input("vip");
        if (empty($user_id) || empty($vip)) {
            return $this->error(trans('user.cscw'));
        }
        $user = Users::find($user_id);
        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }
        if ($user->vip >= $vip) {
            return $this->error(trans('user.wxsj'));
        }
        if ($vip == "2") {
            if ($user->vip == 1) {
                $money = 9000;
            } else {
                $money = 9999;
            }
        } else {
            $money = 999;
        }

        $wallet = UsersWallet::where("user_id", $user_id)
            ->where("token", Users::TOKEN_DEFAULT)
            ->select("id", "user_id", "password", "address", "balance", "lock_balance", "remain_lock_balance", "create_time", "wallet_name", "password_prompt")
            ->first();
        if (empty($wallet)) {
            return $this->error(trans('wallet.zwqb'));
        }
        if ($password != $wallet->password) {
            return $this->error(trans('wallet.zfmmcw'));
        }
        if ($wallet->balance < $money) {
            return $this->error(trans('wallet.yebz'));
        }

        $walletn = UsersWallet::find($wallet->id);
        $data_wallet = [
            'balance_type' => AccountLog::UPDATE_VIP,
            'wallet_id' => $walletn->id,
            'lock_type' => 0,
            'create_time' => time(),
            'before' => $walletn->balance,
            'change' => -$money,
            'after' => bc_sub($walletn->balance, $money, 5),
        ];
        $user->vip = $vip;
        $walletn->balance = $walletn->balance - $money;
        $user->save();
        $walletn->save();
        AccountLog::insertLog(
            array(
                "user_id" => $user_id,
                "value" => -$money,
                "type" => AccountLog::UPDATE_VIP,
                "info" => "升级会员"
            ),
            $data_wallet
        );
        return $this->success(trans('user.sjcg'));
    }
    //提交虚拟币收货地址
    public function updateCurrencyAddress()
    { }
    public function updateAddress()
    {
        $address = Users::getUserId();

        $eth_address = trim(request()->input('eth_address'));
        if (empty($address) || empty($eth_address)) {
            return $this->error(trans('user.cscw'));
        }
        $user = Users::find($address);
        if (empty($user)) {
            return $this->error(trans('user.cyhbcz'));
        }

        if ($other = Users::where('eth_address', $eth_address)->first()) {
            if ($other->id != $user->id) {
                return $this->error(trans('user.gdzbryjbdgl'));
            }
        }
        try {
            $user->eth_address = $eth_address;
            $user->save();
            return $this->success(trans('user.gxcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getUserByAddress()
    {
        $user_id = Users::getUserId();
        if (empty($user_id))
            return $this->error(trans('user.cscw'));
        $user = Users::where("id", $user_id)->first();
        if (empty($user)) {
            return $this->error(trans('user.hywzd'));
        }
        if (empty($user->extension_code)) {
            $user->extension_code = Users::getExtensionCode();
            $user->save();
        }

        $wallet = UsersWallet::where("user_id", $user_id)
            ->where("token", Users::TOKEN_DEFAULT)
            ->select("id", "user_id", "address", "balance", "lock_balance", "remain_lock_balance", "create_time", "wallet_name", "password_prompt")
            ->first();
        $user->wallet = $wallet;
        return $this->success($user);
    }
    public function chatlist()
    {
        $user_id = Users::getUserId(request()->input('user_id', ''));
        if (empty($user_id)) return $this->error(trans('user.cscw'));

        $user = Users::find($user_id);
        if (empty($user)) return $this->error(trans('user.cyhbcz'));

        $chat_list = UserChat::orderBy('id', 'DESC')->paginate(20);

        $datas = $chat_list->items();

        krsort($datas);
        $return = array();
        foreach ($datas as $d) {
            array_push($return, $d);
        }
        return $this->success(array(
            "user" => $user,
            "chat_list" => [
                'total' => $chat_list->total(),
                'per_page' => $chat_list->perPage(),
                'current_page' => $chat_list->currentPage(),
                'last_page' => $chat_list->lastPage(),
                'next_page_url' => $chat_list->nextPageUrl(),
                'prev_page_url' => $chat_list->previousPageUrl(),
                'from' => $chat_list->firstItem(),
                'to' => $chat_list->lastItem(),
                'data' => $return,
            ]
        ));
    }
    public function sendchat()
    {
        $user_id = Users::getUserId(request()->input('user_id', ''));

        $content = request()->input('content', '');
        if (empty($user_id) || empty($content)) return $this->error(trans('user.cscw'));

        $user = Users::find($user_id);
        if (empty($user)) return $this->error(trans('user.hywzd'));

        $data["user_id"] = $user_id;
        $data["user_name"] = $user->account_number;
        $data["head_portrait"] = $user->head_portrait;
        $data["content"] = $content;
        $data["type"] = "1";


        try {
            $res = UserChat::sendChat($data);
            if ($res == "ok") {
                $user_chat = new UserChat();
                $user_chat->from_user_id = $user_id;
                $user_chat->to_user_id = 0;
                $user_chat->content = $content;
                $user_chat->type = 1;
                $user_chat->add_time = time();
                $user_chat->save();
                return $this->success("ok");
            } else {
                return $this->error(trans('user.qcs'));
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 交易所转入接口
     *
     * @return void
     */
    public function shiftToByExchange(Request $request)
    {
        try {
            $exchange_shift_to = DB::transaction(function () use ($request) {
                $appid = $request->input('appid', '');
                $address = $request->input('address', '');
                $number = $request->input('number', 0);
                $currency_name = $request->input('currency_name', '');
                $voucher_no = $request->input('voucher_no', '');
                $timestamp = $request->input('timestamp', 0);
                $nonce = $request->input('nonce', '');
                $signature = $request->input('signature', '');
                $ip = $request->ip();
                $now = Carbon::now();
                $validator = Validator::make($request->all(), [
                    'appid' => 'required|string|min:1',
                    'address' => 'required|string|min:1',
                    'number' => 'required|numeric|min:0.1',
                    'currency_name' => 'required|string|min:1',
                    'voucher_no' => 'required|string|min:1',
                    'timestamp' => 'required|integer|min:0',
                    'nonce' => 'required|string|min:6',
                    'signature' => 'required|string|min:1',
                ], [], [
                    'appid' => 'appid',
                    'address' => '钱包地址',
                    'number' => '数量',
                    'currency_name' => '币种名称',
                    'voucher_no' => '凭证号',
                    'timestamp' => '时间戳',
                    'nonce' => '随机口令',
                    'signature' => '签名',
                ]);

                throw_if($validator->fails(), new \Exception($validator->errors()->first()));

                throw_if($now->getTimestamp() < $timestamp, new \Exception(trans('user.qqwx')));

                throw_if($now->subSeconds(10)->getTimestamp() > $timestamp, new \Exception(trans('user.qqygq')));

                $currency = Currency::where('name', $currency_name)
                    ->where('allow_game_exchange', 1)
                    ->first();

                throw_unless($currency, new \Exception(trans('wallet.cbzbcz')));

                $user_wallet = UsersWallet::whereHas('currencyCoin', function ($query) use ($currency_name) {
                    $query->where('name', $currency_name);
                })->where('address', $address)
                    ->first();

                throw_unless($user_wallet, new \Exception(trans('wallet.ctbdzbcz')));

                $api = AppApi::where('appid', $appid)
                    ->where('status', 1)
                    ->first();

                throw_unless($api, new \Exception(trans('user.appidwx')));

                if ($api->bind_ip != '') {
                    $ip_list = explode(',', $api->bind_ip);
                    throw_if(!in_array($ip, $ip_list), new \Exception(trans('user.ipwx')));
                }
                throw_unless($this->signatureCheck($request->all()), new \Exception(trans('user.qmwx')));
                //验证凭证是否有效
                throw_unless($this->checkVoucher($voucher_no, $request->all()), new \Exception(trans('user.pzwx')));
                ExchangeShiftTo::unguard();
                $exchange_shift_to = ExchangeShiftTo::create([
                    'user_id' => $user_wallet->user_id,
                    'appid' => $appid,
                    'currency_id' => $currency->id,
                    'voucher_no' => $voucher_no,
                    'number' => $number,
                ]);
                throw_unless(isset($exchange_shift_to->id),  new \Exception(trans('user.cspzsb')));
                /*
                //到账应该48小时才到
                $result = change_wallet_balance($user_wallet, 1, $number, AccountLog::GAME_SHIFT_TO, '游戏转入交易所,凭证号:' . $voucher_no);
                throw_if($result !== true, new \Exception($result));
                */
                return $exchange_shift_to;
            });
            return $this->success(trans('user.tjcgpzh:') . $exchange_shift_to->id);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        } finally {
            ExchangeShiftTo::reguard();
        }
    }

    /**
     * 验证凭证是否有效
     *
     * @param string $voucher_no 凭证号
     * @param array $param 凭证的内容
     * @return boolean
     */
    public function checkVoucher($voucher_no, $param)
    {
        //请里应请求游戏的接口，查询对方凭证的状态与信息是否相符
        $http_client = new Client();
        return true;
    }

    /**
     * 验证签名
     *
     * @param array $param
     * @return bool
     */
    public function signatureCheck($param)
    {
        if (!isset($param['signature']) || !isset($param['appid'])) {
            return false;
        }
        $signature = $param['signature'];
        $content = $this->makeSignature($param);
        return $signature === $content;
    }

    public function makeSignature($param)
    {
        if (!isset($param['appid'])) {
            return false;
        }
        if (isset($param['signature'])) {
            unset($param['signature']);
        }
        $appid = $param['appid'];
        $api = AppApi::where('appid', $appid)
            ->where('status', 1)
            ->first();
        if (!$api) {
            return false;
        }
        ksort($param, SORT_STRING);
        $content = $appid . http_build_query($param) . $api->appsecret;
        return md5($content);
    }


    //用户授权码获取(添加代理商是需要用)
    public function authCode()
    {
        $user_id = Users::getUserId();
        if (Cache::has('authorization_code_' . $user_id)) {

            $code = Cache::get('authorization_code_' . $user_id);
        } else {
            //获取随机授权码
            $code = Users::generate_password(6);
            //缓存
            Cache::put('authorization_code_' . $user_id, $code, 600);
        }

        return $this->success($code);
    }
     //初级认证
    public function realName2()
    {
        $userId=Users::getUserId();
        $lang = request()->input('lang','en');
        $id = request()->input('id',0);
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        
        $user_id = Users::getUserId();
        $name = request()->input("name", ""); //真实姓名
        $card_id = request()->input("card_id", ""); //身份证号
        $certificate_type = request()->input("certificate_type", "");
        if(empty($name) || empty($card_id)) {
            return $this->error(trans('user.qtjwzxx'));
        }
        try {
            // 判断是否绑定了邮箱
            $user = Users::getById($userId);
            if(empty($user->email)) {
                return $this->error(trans('user.qxbdyx'));
            }
            
            $userreal = new UserReal();
            if($id && $id > 0) {
                $find_userreal = UserReal::find($id);
                if($find_userreal) {
                    $userreal = $find_userreal;    
                }
            }
            $userreal->user_id = $user_id;
            $userreal->name = $name;
            $userreal->card_id = $card_id;
            $userreal->create_time = time();
            $userreal->certificate_type = $certificate_type;
            $userreal->review_status=2;
            $userreal->auth_status = 1;

            $userreal->save();
            
            $user->is_realname = 1;
            $user->save();
            
            
            
            // 机器人推送消息
            // robotSendMessage($userId,'实名认证');

           return $this->success(trans('user.tjcgddsh'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    // 站内信列表
     public function mailList(Request $request){
        $user_id = Users::getUserId();
        $limit = $request->input('limit', 10);
        $page = $request->input('page',1);
        $mail = Mail::where('user_id',$user_id)
        ->orderBy('id', 'desc')
        ->paginate($limit, ['*'], 'page', $page);
        return $this->success([
            "list" => $mail->items(),
            'count' => $mail->total(),
            "page" => $page,
            "limit" => $limit
        ]);
     }
     // 站内信详情
     public function mailDetail(Request $request){
        $user_id = Users::getUserId();
        $id = $request->input('id', 0);
        $mail = Mail::where('user_id',$user_id)->where('id',$id)->first();
        if($mail) {
            return $this->success($mail);
        }else {
            return $this->error("Error");
        }
     }
     // 站内信已读
     public function mailRead(Request $request) {
        $user_id = Users::getUserId();
        $id = $request->input('id', 0);
        $result = Mail::where('user_id',$user_id)->where('id',$id)->first();
        $result->is_read = 1;
        $result->save();
        if($result) {
            return $this->success("Success");
        }else {
            return $this->error("Error");
        }
     }
    //  充值地址列表
    public function rechargeAddressList(Request $request) {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        $list = [];
        if($user->wallet_address_id) {
            // 指定分组的
            $list = WalletAddressList::where('wallet_address_id',$user->wallet_address_id)->where('is_show',1)->orderBy('sort','desc')->get();
        }else {
            // 默认的
            $default_wallet_address = WalletAddress::where('is_default',1)->first();
            if($default_wallet_address) {
                $list = WalletAddressList::where('wallet_address_id',$default_wallet_address->id)->where('is_show',1)->orderBy('sort','desc')->get();
            }
        }
        return $this->success($list);
    }
    
    public function resetData() {
        return;
        // 查询钱包记录 1745486298 20949 37527
        $wallet_log = WalletLog::whereBetween('id',[37000,38000])->get();
        $list = [];
        foreach ($wallet_log as $k => $v) {
            $type = '';
            switch ($v->memo) {
                case '充币':
                    $type = 8;
                    break;
                 case '申请提币扣除余额':
                 case '申请提币冻结余额':
                    $type = 99;
                    break;
                 case '提币失败,锁定余额减少':
                 case '提币失败,锁定余额撤回':
                    $type = 101;
                    break;
                 case '扣除订单本金':
                 case '交割合约订单平仓,盈利结算':
                    $type = 502;
                    break;
                 case '资产闪兑扣除资产':
                 case '资产闪兑增加资产':
                 case '锁仓挖矿增加冻结资产':
                 case '锁仓挖矿扣除可用资产':
                    $type = 601;
                    break;
                case '锁仓挖矿收益':
                    $type = 602;
                    break;
                 case '资金划转秒合约':
                     $type = $v->change > 0 ? 15 : 9;
                     break;
                case '资金划转理财':
                    $type = $v->change > 0 ? 888 : 9;
                    break;
                case '资金划转现货':
                    $type = $v->change > 0 ? 11 : 9;
                    break;
                case '资金划转合约':
                    $type = $v->change > 0 ? 13 : 9;
                    break;
                
                 case '理财划转秒合约':
                    $type = $v->change > 0 ? 15 : 999;
                    break;
                case '理财划转资金':
                    $type = $v->change > 0 ? 10 : 999;
                    break;
                 case '理财划转现货':
                    $type = $v->change > 0 ? 11 : 999;
                    break;
                 case '理财划转合约':
                    $type = $v->change > 0 ? 13 : 999;
                    break;
                    
                 case '秒合约划转理财':
                    $type = $v->change > 0 ? 888 : 16;
                    break;
                case '秒合约划转资金':
                    $type = $v->change > 0 ? 10 : 16;
                    break;
                case '秒合约划转现货':
                    $type = $v->change > 0 ? 11 : 16;
                    break;
                case '秒合约划转合约':
                    $type = $v->change > 0 ? 13 : 16;
                    break;
                    
                case '合约划转理财':
                    $type = $v->change > 0 ? 888 : 14;
                    break;
                case '合约划转资金':
                    $type = $v->change > 0 ? 10 : 14;
                    break;
                case '合约划转现货':
                    $type = $v->change > 0 ? 11 : 14;
                    break;
                case '合约划转秒合约':
                    $type = $v->change > 0 ? 15 : 14;
                    break;
                    
                case '现货划转理财':
                    $type = $v->change > 0 ? 888 : 12;
                    break;
                case '现货划转资金':
                    $type = $v->change > 0 ? 10 : 12;
                    break;
                case '现货划转合约':
                    $type = $v->change > 0 ? 13 : 12;
                    break;
                case '现货划转秒合约':
                    $type = $v->change > 0 ? 15 : 12;
                    break;
                    
                    
                case '提币成功':
                    $type = 100;
                    break;
                case '锁仓挖矿扣提前赎回退款':
                case '释放锁仓挖矿减少冻结资产':
                case '锁合挖矿扣提前赎回手续费':
                    $type = 603;
                    break;
                
                default:
                    // code...
                    break;
            }
            $data = [
                'id' => $v->account_log_id,
                'user_id' => $v->user_id,
                'value' => $v->change,
                'created_time' => $v->create_time,
                'info' => $v->memo,
                'type' => $type,
                'currency' => 0,
                'en_info' => $v->en_memo,
                'is_lock' => $v->lock_type,
                'order_no' => $v->order_no,
                'create_date' => date('Y-m-d H:i:s',$v->create_time),
                'status' => 0,
            ];
            // array_push($list,$data);
            // 先查询是否存在
            $account_log = AccountLog::where('id',$v->account_log_id)->first();
            if($account_log) {
                continue;
            }
            $result = AccountLog::insert([$data]);
            var_dump($result);
        }
    }
}
