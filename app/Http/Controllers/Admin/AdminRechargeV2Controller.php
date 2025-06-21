<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog,
    Currency,
    CurrencyProjectOrder,
    DzpConfigCount,
    RewardConf,
    Setting,
    UserLevelModel,
    Users,
    UsersWallet,
    RechargeRecord,
    WalletLog
};
use Illuminate\Support\Facades\DB;

class AdminRechargeV2Controller extends Controller
{
    public function rechargeFailPage(Request $request)
    {

        $limit = $request->input('limit', 10);
        $lists = RechargeRecord::where(function ($query) {


            $query->where('user_id', '>', 0);
        })->whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number', '');
            $account_number != '' && $query->where('account_number', $account_number);
            $user_id = $request->input('user_id', 0);
            $user_id != '' && $query->where('user_id',  $user_id );
        })->where(function ($query) use ($request) {

            $currency = $request->input('currency', -1);
            $start_time = $request->input('start_time', null);
            $end_time = $request->input('end_time', null);
            $currency != -1 && $query->where('currency', $currency);
            $start_time && $query->where('created_at', '>=', $start_time);
            $end_time && $query->where('created_at', '<=', $end_time);

            $status = $request->input('status', '');

            $query->where('status', '=','2');


            $id = $request->input('id', '');
            if (!empty($id)&&$id>0){
                $query->where('user_id', '=', $id);
            }


        })->orderBy('status', 'asc')->orderBy('id', 'desc')
            ->paginate($limit);
        //print_r($lists);

        foreach ($lists as $po) {
            $uid = $po->user_id;
            $u = DB::table('users')->where('id', $uid)->first();
            $account_number = $u->account_number;
            $po->account_number = $account_number;
            $po->email = $u->email;
            $po->remark = $u->remark;
            //
            if (!empty($po->currency)) {
                if ($po->currency==58){
                    $po->currency_name = "银行卡支付";
                }else {
                    $currency_name = Currency::getNameById($po->currency);
                    $po->currency_name = $currency_name;
                }


            }

            $dianhui = $po->dianhui;
            if ($dianhui == 1) {
                $po->channel = "电汇";
            } else {
                if (23 == $po->currency) {
                    $po->channel = $po->usdt_type;
                }
            }
            if (empty($po->voucher)) {
                $po->voucher = "";
            }

            $status = $po->status;
            if ($status == 1) {
                $po->statusZH = "<p style='color:green'>审核通过</p>";
            } else if ($status == 2) {
                $po->statusZH = "<p style='color:red'>拒绝</p>";
            } else {
                $po->statusZH = "<p style='color:black'>等待审核</p>";
            }


        }
        //


        $sum = $lists->sum('money');
        return $this->layuiData($lists, $sum);
    }
    public function rechargeSuccessPage(Request $request)
    {

        $limit = $request->input('limit', 10);
        $lists = RechargeRecord::where(function ($query) {


            $query->where('user_id', '>', 0);
        })->whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number', '');
            $account_number != '' && $query->where('account_number', $account_number);
            $user_id = $request->input('user_id', 0);
            $user_id != '' && $query->where('user_id',  $user_id );
        })->where(function ($query) use ($request) {

            $currency = $request->input('currency', -1);
            $start_time = $request->input('start_time', null);
            $end_time = $request->input('end_time', null);
            $currency != -1 && $query->where('currency', $currency);
            $start_time && $query->where('created_at', '>=', $start_time);
            $end_time && $query->where('created_at', '<=', $end_time);

            $status = $request->input('status', '');

            $query->where('status', '=','1');


            $id = $request->input('id', '');
            if (!empty($id)&&$id>0){
                $query->where('user_id', '=', $id);
            }


        })->orderBy('status', 'asc')->orderBy('id', 'desc')
            ->paginate($limit);
        //print_r($lists);

        foreach ($lists as $po) {
            $uid = $po->user_id;
            $u = DB::table('users')->where('id', $uid)->first();
            $account_number = $u->account_number;
            $po->account_number = $account_number;
            $po->email = $u->email;
            $po->remark = $u->remark;
            //
            if (!empty($po->currency)) {
                if ($po->currency==58){
                    $po->currency_name = "银行卡支付";
                }else {
                    $currency_name = Currency::getNameById($po->currency);
                    $po->currency_name = $currency_name;
                }


            }

            $dianhui = $po->dianhui;
            if ($dianhui == 1) {
                $po->channel = "电汇";
            } else {
                if (23 == $po->currency) {
                    $po->channel = $po->usdt_type;
                }
            }
            if (empty($po->voucher)) {
                $po->voucher = "";
            }

            $status = $po->status;
            if ($status == 1) {
                $po->statusZH = "<p style='color:green'>审核通过</p>";
            } else if ($status == 2) {
                $po->statusZH = "<p style='color:red'>拒绝</p>";
            } else {
                $po->statusZH = "<p style='color:black'>等待审核</p>";
            }


        }
        //


        $sum = $lists->sum('money');
        return $this->layuiData($lists, $sum);
    }



    //2023-01-14新增充值组团奖励功能
    public function saveRewardGroup($deposit_uid, $deposit_amount,$deposit_date)
    {

        $depositUser = Users::find($deposit_uid);
        $parent_id = $depositUser->parent_id;

        $list = RewardConf::all();
        $p0=$list[0];
        $p1=$list[1];
        $p2=$list[2];
        $p3=$list[3];

        if (!empty($parent_id) && $parent_id > 0) {
            $parent = Users::find($parent_id);

            $reward_username = $parent->account_number;
            //$deposit_amount = $deposit_amount;
            $deposit_username = $depositUser->account_number;
            $create_date = date("Y-m-d H:i");
            $remark = "";
            $status = 0;  //'0:待审核  1:审核通过  -1:拒绝',
            $category = 0;
            $reward_amount = 0;

            $min_amount0=$p0->min_amount;
            $max_amount0=$p0->max_amount;
            $ratio0=$p0->ratio;

            if ($deposit_amount >= $min_amount0 && $deposit_amount < $max_amount0) {
                $category = 1;
                $reward_amount = $ratio0 * $deposit_amount;
            }

            $min_amount1=$p1->min_amount;
            $max_amount1=$p1->max_amount;
            $ratio1=$p1->ratio;
            if ($deposit_amount >= $min_amount1 && $deposit_amount < $max_amount1) {
                $category = 2;
                $reward_amount = $ratio1 * $deposit_amount;
            }

            $min_amount2=$p2->min_amount;
            $max_amount2=$p2->max_amount;
            $ratio2=$p2->ratio;

            if ($deposit_amount >= $min_amount2 && $deposit_amount < $max_amount2) {
                $category = 3;
                $reward_amount = $ratio2 * $deposit_amount;
            }

            $min_amount3=$p3->min_amount;
            $max_amount3=$p3->max_amount;
            $ratio3=$p3->ratio;

            if ($deposit_amount >= $min_amount3) {
                $category = 4;
                $reward_amount = $ratio3 * $deposit_amount;
            }

            if ($reward_amount > 0) {

                $reward = [
                    'reward_amount' => $reward_amount,
                    'reward_username' => $reward_username,
                    'deposit_username' => $deposit_username,
                    'deposit_amount' => $deposit_amount,
                    'category' => $category,
                    'status' => $status,
                    'create_date' => $create_date,
                    'remark' => $remark,
                    'parent_uid' => $parent_id,
                    'deposit_date' =>$deposit_date
                ];
                $res = DB::table('cz_reward_group')->insert($reward);

            }
        }


    }


    public function getVIPLevel($uid)
    {
        //
        $userSql="select cz_amount from users  where id=".$uid;
        $userList=DB::select($userSql);

        $czPO=$userList[0];
        $usdt=$czPO->cz_amount;
        if (empty($usdt)){
            $usdt=0;
        }

        $userLevel=UserLevelModel::find(1);
        $lv10=$userLevel->lv10;
        $lv9=$userLevel->lv9;
        $lv8=$userLevel->lv8;
        $lv7=$userLevel->lv7;

        $lv6=$userLevel->lv6;
        $lv5=$userLevel->lv5;
        $lv4=$userLevel->lv4;
        $lv3=$userLevel->lv3;

        $lv2=$userLevel->lv2;
        $lv1=$userLevel->lv1;

        if (!empty($lv10)&&$usdt>=$lv10){
            DB::update("update  users  set vip=10 where id=".$uid);
        }
        else if (!empty($lv9)&&$usdt>=$lv9){
            DB::update("update  users  set vip=9 where id=".$uid);
        }
        else if (!empty($lv8)&&$usdt>=$lv8){
            DB::update("update  users  set vip=8 where id=".$uid);
        }
        else if (!empty($lv7)&&$usdt>=$lv7){
            DB::update("update  users  set vip=7 where id=".$uid);
        }
        else if (!empty($lv6)&&$usdt>=$lv6){
            DB::update("update  users  set vip=6 where id=".$uid);
        }
        else if (!empty($lv5)&&$usdt>=$lv5){
            DB::update("update  users  set vip=5 where id=".$uid);
        }else if (!empty($lv4)&&$usdt>=$lv4){
            DB::update("update  users  set vip=4 where id=".$uid);
        } else if (!empty($lv3)&&$usdt>=$lv3){
            DB::update("update  users  set vip=3 where id=".$uid);
        }
        else if (!empty($lv2)&&$usdt>=$lv2){
            DB::update("update  users  set vip=2 where id=".$uid);
        }
        else if (!empty($lv1)&&$usdt>=$lv1){
            DB::update("update  users  set vip=1 where id=".$uid);
        }

    }


    public function v2_tj(Request $request)
    {
        $id = $request->input('id');
        $po = RechargeRecord::find($id);
        $user_id = $po->user_id;
        $po->update(['status' => 1]);
        $notes = $request->input('notes');
        if($notes) {
          $po->update(['notes' => $notes]);  
        }

        DB::update('update users set cz_count = cz_count+1 where id = ' . $user_id);
        $sql = "";
        $amount = $po->money;
        $amount=doubleval($amount);
        $dianhui = $po->dianhui;

        $configCountPO=DzpConfigCount::find(1);
        $czAmountConfig=$configCountPO->cz_amount;
        $czAmountConfig=doubleval($czAmountConfig);

        if ($dianhui == 1) {
            $currency = 23;
            $wallet = UsersWallet::where('currency', $currency)->where('user_id', $user_id)->first();
            DB::beginTransaction();
            try {
                $currency_id = 63;
                $settingVO=7.19;
                $lang = $po->lang;



                if ($lang=="vi"){
                    $currency_id=81;
                    $settingVO=Setting::getValueByKey('vnd');

                    $usdt=bc_div($amount,$settingVO,2);
                    //计算抽奖次数
                    $cj_count=0;
                    if (!empty($czAmountConfig)&&$czAmountConfig>0){
                         if ($usdt>$czAmountConfig){
                             $cj_count=bc_div($usdt,$czAmountConfig);
                         }
                    }
                    $cj_count=floor($cj_count);



                    $czAmountSql = "update users set cz_amount = cz_amount+" . $usdt ." , cj_count= cj_count+".$cj_count.     " where id = " . $user_id;



                    DB::update($czAmountSql);


                } else if ($lang=="th"){
                    $currency_id=82;
                    $settingVO=Setting::getValueByKey('thb');

                    $usdt=bc_div($amount,$settingVO,2);
                    //计算抽奖次数
                    $cj_count=0;
                    if (!empty($czAmountConfig)&&$czAmountConfig>0){
                        if ($usdt>$czAmountConfig){
                            $cj_count=bc_div($usdt,$czAmountConfig);
                        }
                    }
                    $cj_count=floor($cj_count);

                    $vip=$this->getVIPLevel($usdt);
                    $czAmountSql = "update users set  cz_amount = cz_amount+" . $usdt ." , cj_count= cj_count+".$cj_count.      " where id = " . $user_id;
                    DB::update($czAmountSql);

                }else if($lang=="id"){
                    $currency_id=83;
                    $settingVO=Setting::getValueByKey('idr');

                    $usdt=bc_div($amount,$settingVO,2);
                    //计算抽奖次数
                    $cj_count=0;
                    if (!empty($czAmountConfig)&&$czAmountConfig>0){
                        if ($usdt>$czAmountConfig){
                            $cj_count=bc_div($usdt,$czAmountConfig);
                        }
                    }
                    $cj_count=floor($cj_count);

                    $czAmountSql = "update users set cz_amount = cz_amount+" . $usdt ." , cj_count= cj_count+".$cj_count.      " where id = " . $user_id;
                    DB::update($czAmountSql);

                }else{
                    $settingVO=Setting::getValueByKey('rmb');

                    $usdt=bc_div($amount,$settingVO,2);
                    //计算抽奖次数
                    $cj_count=0;
                    if (!empty($czAmountConfig)&&$czAmountConfig>0){
                        if ($usdt>$czAmountConfig){
                            $cj_count=bc_div($usdt,$czAmountConfig);
                        }
                    }
                    $cj_count=floor($cj_count);
                    $czAmountSql = "update users set cz_amount = cz_amount+" . $usdt ." , cj_count= cj_count+".$cj_count.      " where id = " . $user_id;
                    DB::update($czAmountSql);

                }

                if ($lang=="en"){
                    $currency_id = $po->currency;
                }


                $sql = 'update users_wallet set legal_balance = legal_balance+' . $amount . ' where user_id = ' . $user_id . ' and currency='.$currency_id;

                DB::update($sql);


                $deposit_date=$po->created_at;
                $this->saveRewardGroup($user_id, $amount,$deposit_date);

                DB::commit();

                // $pushURL="https://api0912.myshop0816.shop/market/binance/stomp/push";
                // $this->curl($pushURL);
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
        } else {
            $currency = $po->currency;

            $sql = 'update users_wallet set cz_amount = cz_amount+' . $amount . ' where user_id = ' . $user_id . ' and currency=' . $currency;
            DB::update($sql);
            //
            if ($currency==23){

                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($amount>$czAmountConfig){
                        $cj_count=bc_div($amount,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                $czAmountSql = "update users set cz_amount = cz_amount+" . $amount ." , cj_count= cj_count+".$cj_count.   " where id = " . $user_id;
                DB::update($czAmountSql);
            }
            if ($currency==32){
                $btcprice=Currency::getUsdtPriceV2("btc");
                $usdt=bc_mul($btcprice,$amount);
                //
                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($usdt>$czAmountConfig){
                        $cj_count=bc_div($usdt,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                //
                $czAmountSql = "update users set cz_amount = cz_amount+" . $usdt ." , cj_count= cj_count+".$cj_count.  " where id = " . $user_id;
                DB::update($czAmountSql);
            }
            if ($currency==35){
                $ethPrice=Currency::getUsdtPriceV2("eth");
                $usdt=bc_mul($ethPrice,$amount);
                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($usdt>$czAmountConfig){
                        $cj_count=bc_div($usdt,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                //
                $czAmountSql = "update users set cz_amount = cz_amount+" . $usdt ." , cj_count= cj_count+".$cj_count." where id = " . $user_id;
                DB::update($czAmountSql);
            }
            
            if ($currency==57){

                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($amount>$czAmountConfig){
                        $cj_count=bc_div($amount,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                $czAmountSql = "update users set cz_amount = cz_amount+" . $amount ." , cj_count= cj_count+".$cj_count.   " where id = " . $user_id;
                DB::update($czAmountSql);
            }




            $wallet = UsersWallet::where('currency', $currency)->where('user_id', $user_id)->first();
            DB::beginTransaction();
            try {
                $result = change_wallet_balance($wallet,
                    0,//充值充到 资金账户2023-08-26
                    +$amount,
                    AccountLog::WALLET_CURRENCY_IN,
                    '充币',
                    'Deposit coins',
                     false, 
                     0, 
                     0, 
                     '', 
                     false, 
                     false, 
                     false,
                     $id);
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $deposit_date=$po->created_at;
                $this->saveRewardGroup($user_id, $amount,$deposit_date);

                DB::commit();

                // $pushURL="https://api0912.myshop0816.shop/market/binance/stomp/push";
                // $this->curl($pushURL);

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
        }

        $this->getVIPLevel($user_id);


        return $this->success("审核成功");
    }

    public function v2_jj(Request $request)
    {
        $id = $request->input('id');
        RechargeRecord::find($id)->update(['status' => 2]);
        $notes = $request->input('notes');
        if($notes) {
         RechargeRecord::find($id)->update(['notes' => $notes]);  
        }

        // $pushURL="https://api0912.myshop0816.shop/market/binance/stomp/push";
        // $this->curl($pushURL);

        return $this->success("操作成功");
    }
    
    public function v2_reset(Request $request)
    {
        $id = $request->input('id');
        $po = RechargeRecord::find($id);
        
        // 限制只有未操作过的提币才能进行操作
        $recharge_record = RechargeRecord::where('status', 1)
            ->lockForUpdate()
            ->findOrFail($id);
        if(!$recharge_record) {
            return $this->error("订单不存在");
        }
        
        $account_log =  AccountLog::where('recharge_out_record_id',$id)->first();
        if(empty($account_log)) {
            return $this->error("记录不存在");
        }
        
        $user_id = $po->user_id;
        $po->update(['status' => 0]);
        $po->update(['notes' => '']);  

        DB::update('update users set cz_count = cz_count-1 where id = ' . $user_id);
        $sql = "";
        $amount = $po->money;
        $amount=doubleval($amount);
        $dianhui = $po->dianhui;

        $configCountPO=DzpConfigCount::find(1);
        $czAmountConfig=$configCountPO->cz_amount;
        $czAmountConfig=doubleval($czAmountConfig);

        {
            $currency = $po->currency;

            $sql = 'update users_wallet set cz_amount = cz_amount-' . $amount . ' where user_id = ' . $user_id . ' and currency=' . $currency;
            DB::update($sql);
            //
            if ($currency==23){

                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($amount>$czAmountConfig){
                        $cj_count=bc_div($amount,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                $czAmountSql = "update users set cz_amount = cz_amount-" . $amount ." , cj_count= cj_count-".$cj_count.   " where id = " . $user_id;
                DB::update($czAmountSql);
            }
            if ($currency==32){
                $btcprice=Currency::getUsdtPriceV2("btc");
                $usdt=bc_mul($btcprice,$amount);
                //
                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($usdt>$czAmountConfig){
                        $cj_count=bc_div($usdt,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                //
                $czAmountSql = "update users set cz_amount = cz_amount-" . $usdt ." , cj_count= cj_count-".$cj_count.  " where id = " . $user_id;
                DB::update($czAmountSql);
            }
            if ($currency==35){
                $ethPrice=Currency::getUsdtPriceV2("eth");
                $usdt=bc_mul($ethPrice,$amount);
                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($usdt>$czAmountConfig){
                        $cj_count=bc_div($usdt,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                //
                $czAmountSql = "update users set cz_amount = cz_amount-" . $usdt ." , cj_count= cj_count-".$cj_count." where id = " . $user_id;
                DB::update($czAmountSql);
            }
            
            if ($currency==57){

                //计算抽奖次数
                $cj_count=0;
                if (!empty($czAmountConfig)&&$czAmountConfig>0){
                    if ($amount>$czAmountConfig){
                        $cj_count=bc_div($amount,$czAmountConfig);
                    }
                }
                $cj_count=floor($cj_count);

                $czAmountSql = "update users set cz_amount = cz_amount-" . $amount ." , cj_count= cj_count-".$cj_count.   " where id = " . $user_id;
                DB::update($czAmountSql);
            }

            $wallet = UsersWallet::where('currency', $currency)->where('user_id', $user_id)->first();
            DB::beginTransaction();
            try {
                $result = reset_change_wallet_balance(
                    $wallet,
                    0,//充值充到 资金账户2023-08-26
                    -$amount,
                    $id);
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $deposit_date=$po->created_at;
                // $this->saveRewardGroup($user_id, $amount,$deposit_date);

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
        }

        $this->getVIPLevel($user_id);


        return $this->success("重置成功");
    }

    public function rechargePage(Request $request)
    {

        $limit = $request->input('limit', 10);
        $lists = RechargeRecord::where(function ($query) {


            $query->where('user_id', '>', 0);
        })->whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number', '');
            $account_number != '' && $query->where('account_number', $account_number);
            $user_id = $request->input('user_id', 0);
            $user_id != '' && $query->where('user_id',  $user_id );
        })->where(function ($query) use ($request) {

            $currency = $request->input('currency', -1);
            $start_time = $request->input('start_time', null);
            $end_time = $request->input('end_time', null);
            $currency != -1 && $query->where('currency', $currency);
            $start_time && $query->where('created_at', '>=', $start_time);
            $end_time && $query->where('created_at', '<=', $end_time);


                $query->where('status', '=', '0');


            $id = $request->input('id', '');
            if (!empty($id)&&$id>0){
                $query->where('user_id', '=', $id);
            }


        })->orderBy('status', 'asc')->orderBy('id', 'desc')
            ->paginate($limit);
        //print_r($lists);

        foreach ($lists as $po) {
            $uid = $po->user_id;
            $u = DB::table('users')->where('id', $uid)->first();
            $account_number = $u->account_number;
            $po->account_number = $account_number;
             $po->email = $u->email;
            $po->remark = $u->remark;
            //
            if (!empty($po->currency)) {
                if ($po->currency==58){
                    $po->currency_name = "银行卡支付";
                }else {
                    $currency_name = Currency::getNameById($po->currency);
                    $po->currency_name = $currency_name;
                }


            }

            $dianhui = $po->dianhui;
            if ($dianhui == 1) {
                $po->channel = "电汇";
            } else {
                if (23 == $po->currency) {
                    $po->channel = $po->usdt_type;
                }
            }
            if (empty($po->voucher)) {
                $po->voucher = "";
            }

            $status = $po->status;
            if ($status == 1) {
                $po->statusZH = "<p style='color:green'>审核通过</p>";
            } else if ($status == 2) {
                $po->statusZH = "<p style='color:red'>拒绝</p>";
            } else {
                $po->statusZH = "<p style='color:black'>等待审核</p>";
            }


        }
        //


        $sum = $lists->sum('money');
        return $this->layuiData($lists, $sum);
    }

    function curl($url,$postdata=[]) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);

        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $output;
    }
}
