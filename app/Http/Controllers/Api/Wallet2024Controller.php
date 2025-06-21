<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Models\{Currency,
    Address,
    AccountLog,
    Setting,
    Users,
    UsersWallet,
    UsersWalletOut,
    LeverTransaction,
    CurrencyQuotation,
    RechargeRecord,
    LoanOrder
};
use App\Events\WithdrawSubmitEvent;
use App;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\Input;
use Carbon\Carbon;

class Wallet2024Controller extends Controller
{
    
    public function getWebSiteConfig()
    {


        $title=Setting::getValueByKey("web_site_title");
        $desc=Setting::getValueByKey("web_site_desc");
        $keyword=Setting::getValueByKey("web_site_keyword");

        $jo["title"]=$title;
        $jo["desc"]=$title;
        $jo["keyword"]=$keyword;


        return $this->success($jo);

    }
    
    
    public function setOnline()
    {
        try {
            $user_id = Users::getUserId();
            $userStr="#".$user_id.'#';
            $onlineUserStr = Redis::get($userStr);
            if(empty($onlineUserStr)) {
               Redis::set($userStr, 'online');
            }
            return $this->success("刷新成功".$userStr);
        }catch (\Exception $ex){

        }
        return $this->success("刷新失败");
    }
    
    public function setOffline()
    {
        try {
            $user_id = Users::getUserId();
            $userStr="#".$user_id.'#';
            Redis::del($userStr);
        }catch (\Exception $ex){

        }
        return $this->success("刷新成功");
    }
    
    //  http://192.168.16.108/walletList2024

    public function getUsdtPriceV3($usdtPriceList,$coin){
            foreach ($usdtPriceList as $tmpCoin){
                $currencyName=$tmpCoin['currencyName'];
                if ($coin==$currencyName){
                    return  $tmpCoin['close'];
                }
            }
    }
    
    public function getUsdtPriceV4($usdtPriceList,$id){
            foreach ($usdtPriceList as $tmpCoin){
                $currency_id = $tmpCoin['currency_id'];
                if ($id == $currency_id){
                    return  $tmpCoin['close'];
                }
            }
    }

    public function walletList(Request $request)
    {
        $lang = request()->input('lang', 'en');

        $user_id = Users::getUserId();

        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $usdtPriceList=Currency::getAllUsdtPriceV2();


        $sql="select legal_balance,yesterday_legal_balance,change_balance,yesterday_change_balance,earn_balance,yesterday_earn_balance,micro_balance,yesterday_micro_balance,lever_balance,yesterday_lever_balance,currency from users_wallet  where user_id=".$user_id;
        $sql=$sql." and (legal_balance>0 or change_balance>0 or lever_balance>0 or earn_balance>0 or micro_balance>0 )";
        $walletList = DB::select($sql);
        $earn_balance=0;
        $legal_balance=0;
        $change_balance=0;
        $lever_balance=0;
        $micro_balance=0;
        
        $yesterday_earn_balance=0;
        $yesterday_legal_balance=0;
        $yesterday_change_balance=0;
        $yesterday_lever_balance=0;
        $yesterday_micro_balance=0;
        
        foreach ($walletList as $walletPO){
            $currencyId=$walletPO->currency;
            $currencyName=Currency::getNameById($currencyId);

            if ($currencyName=="CNY"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $rmb = Setting::getValueByKey('rmb');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$rmb);
                }

            }elseif ($currencyName=="VND"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $vnd = Setting::getValueByKey('vnd');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$vnd);
                }

            }elseif ($currencyName=="IDR"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $idr = Setting::getValueByKey('idr');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$idr);
                }
            }elseif ($currencyName=="THB"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $thb = Setting::getValueByKey('thb');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$thb);
                }
            }elseif ($currencyName=="USD"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $usd = Setting::getValueByKey('usd');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$usd);
                }
            }elseif ($currencyName=="EUR"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $eur = Setting::getValueByKey('eur');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$eur);
                }
            }elseif ($currencyName=="GBP"){
                $legal_balanceTmp = $walletPO->legal_balance;
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $gbp = Setting::getValueByKey('gbp');
                    $legal_balance=$legal_balance+bc_div($legal_balanceTmp,$gbp);
                }
            }else {
                $usdtVal=null;
                $path = base_path() . '/storage/logs/test/';
                if ($currencyName=="USDT" || $currencyName=="USDC"){
                    $usdtVal=1;
                }else{
                    $usdtVal=$this->getUsdtPriceV3($usdtPriceList,$currencyName);
                    // $path = base_path() . '/storage/logs/test/';
                    // $filename = date('Ymd') . '.log';
                    // file_exists($path) || @mkdir($path);
                    // error_log(date('Y-m-d H:i:s') .'价格='.$usdtVal. PHP_EOL, 3, $path . $filename);
                }

                $legal_balanceTmp = $walletPO->legal_balance;
                $legal_balanceTmp=doubleval($legal_balanceTmp);
                if (!empty($legal_balanceTmp)&&$legal_balanceTmp>0){
                    $legal_balance=$legal_balance+bc_mul($usdtVal,$legal_balanceTmp);
                    //  $legal_balance=$legal_balance+$legal_balanceTmp;
                }
                $yesterday_legal_balanceTmp = $walletPO->yesterday_legal_balance;
                $yesterday_legal_balanceTmp=doubleval($yesterday_legal_balanceTmp);
                if (!empty($yesterday_legal_balanceTmp)&&$yesterday_legal_balanceTmp>0){
                    $yesterday_legal_balance=$yesterday_legal_balance+bc_mul($usdtVal,$yesterday_legal_balanceTmp);
                    //  $legal_balance=$legal_balance+$yesterday_legal_balanceTmp;
                }

                $change_balanceTmp = $walletPO->change_balance;
                if (!empty($change_balanceTmp)&&$change_balanceTmp>0){
                    $change_balance=$change_balance+bc_mul($usdtVal,$change_balanceTmp);
                    //  $change_balance=$change_balance+$change_balanceTmp;
                }
                $yesterday_change_balanceTmp = $walletPO->yesterday_change_balance;
                if (!empty($change_balanceTmp)&&$change_balanceTmp>0){
                    $yesterday_change_balance=$yesterday_change_balance+bc_mul($usdtVal,$yesterday_change_balanceTmp);
                    //  $change_balance=$change_balance+$change_balanceTmp;
                }

                $lever_balanceTmp = $walletPO->lever_balance;
                if (!empty($lever_balanceTmp)&&$lever_balanceTmp>0){
                    $lever_balance=$lever_balance+bc_mul($usdtVal,$lever_balanceTmp);
                    // $lever_balance=$lever_balance+$lever_balanceTmp;
                }
                $yesterday_lever_balanceTmp = $walletPO->yesterday_lever_balance;
                if (!empty($yesterday_lever_balanceTmp)&&$yesterday_lever_balanceTmp>0){
                    $yesterday_lever_balance=$yesterday_lever_balance+bc_mul($usdtVal,$yesterday_lever_balanceTmp);
                    // $lever_balance=$lever_balance+$lever_balanceTmp;
                }

                $earn_balanceTmp = $walletPO->earn_balance;
                if (!empty($earn_balanceTmp)&&$earn_balanceTmp>0){
                    $earn_balance=$earn_balance+bc_mul($usdtVal,$earn_balanceTmp);
                    // $earn_balance=$earn_balance+$earn_balanceTmp;
                }
                $yesterday_earn_balanceTmp = $walletPO->yesterday_earn_balance;
                if (!empty($yesterday_earn_balanceTmp)&&$yesterday_earn_balanceTmp>0){
                    $yesterday_earn_balance=$yesterday_earn_balance+bc_mul($usdtVal,$yesterday_earn_balanceTmp);
                    // $earn_balance=$earn_balance+$earn_balanceTmp;
                }


                $micro_balanceTmp = $walletPO->micro_balance;
                if (!empty($micro_balanceTmp)&&$micro_balanceTmp>0){
                    $micro_balance=$micro_balance+bc_mul($usdtVal,$micro_balanceTmp);
                    // $micro_balance=$micro_balance+$micro_balanceTmp;
                }
                $yesterday_micro_balanceTmp = $walletPO->yesterday_micro_balance;
                if (!empty($yesterday_micro_balanceTmp)&&$yesterday_micro_balanceTmp>0){
                    $yesterday_micro_balance=$yesterday_micro_balance+bc_mul($usdtVal,$yesterday_micro_balanceTmp);
                    // $micro_balance=$micro_balance+$micro_balanceTmp;
                }
            }


        }
        // 当前资产
        $allUsdt = $earn_balance + $legal_balance + $change_balance + $lever_balance + $micro_balance;
        
        // 总资产账户，盈亏 = 当前资产-0点的资产-充值 - 后台充值-贷款+提款
        // 资金账户，盈亏 = 当前资金资产-0点的资产-充值 - 后台充值 -贷款-划转划入+划转划出+提款
        // 现货账户，盈亏 = 当前现货资产-0点的资产 - 后台充值 -划转划入+划转划出
        // 合约账户，盈亏 = 当前合约资产-0点的资产 - 后台充值 -划转划入+划转划出
        // 秒合约跟理财账户，盈亏 =  当前合约资产-0点的资产-划转划入+划转划出
        
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        
        // 后台操作的总的充值
        $ht_account_logs = AccountLog::where('user_id',$user_id)->whereIn('type',[AccountLog::ADMIN_LEGAL_BALANCE,AccountLog::ADMIN_CHANGE_BALANCE,AccountLog::ADMIN_LEVER_BALANCE,AccountLog::ADMIN_SECOND_LEVER_BALANCE])->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        
        $todayHtSum = 0;
        foreach($ht_account_logs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayHtSum = bcadd($todayHtSum,bcmul($usdtVal,$v->value,6),6);
        }
        $todayHtSum = floatval($todayHtSum);
       
        
        // 0点资产
        $yesterday_allUsdt = $yesterday_earn_balance + $yesterday_legal_balance + $yesterday_change_balance + $yesterday_lever_balance + $yesterday_micro_balance;
        // 当日充值成功订单统计
        // $todayRechargeRecordSum = RechargeRecord::where('user_id',$user_id)->where('status',1)->whereBetween('updated_at', [$todayStart, $todayEnd])->sum('money');
        $todayRechargeRecordSum = 0;
        $today_recharge_records = RechargeRecord::where('user_id',$user_id)->where('status',1)->whereBetween('updated_at', [$todayStart, $todayEnd])->get();
        foreach ($today_recharge_records as $k => $v) {
             if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayRechargeRecordSum = bcadd($todayRechargeRecordSum,bcmul($usdtVal,$v->money,6),6);
        }
        $todayRechargeRecordSum = floatval($todayRechargeRecordSum);
        
        // 当日贷款成功订单统计
        $todayLoanOrderSum = LoanOrder::where('user_id',$user_id)->where('status',1)->whereBetween('updated_at', [$todayStart->timestamp, $todayEnd->timestamp])->sum('loan_money');
       
        // 当日提款成功订单统计
        // $todayUsersWalletOutSum = UsersWalletOut::where('user_id',$user_id)->where('status',2)->whereBetween('update_time', [$todayStart->timestamp, $todayEnd->timestamp])->sum('number');
        $todayUsersWalletOutSum = 0;
        $today_users_wallet_outs = UsersWalletOut::where('user_id',$user_id)->where('status',2)->whereBetween('update_time', [$todayStart->timestamp, $todayEnd->timestamp])->get();
        foreach ($today_users_wallet_outs as $k => $v) {
             if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayUsersWalletOutSum = bcadd($todayUsersWalletOutSum,bcmul($usdtVal,$v->number,6),6);
        }
        $todayUsersWalletOutSum = floatval($todayUsersWalletOutSum);
        
        // $yesterday_allUsdt = $yesterday_allUsdt <= 0 ? $allUsdt : $yesterday_allUsdt;
        // 总资产账户盈亏 = 当前资产-0点的资产-充值 - 后台充值的 -贷款+提款
        // $total_profit_loss = $allUsdt - $yesterday_allUsdt - $todayRechargeRecordSum - $todayHtSum - $todayLoanOrderSum + $todayUsersWalletOutSum;
        
        //   if($user_id == 8500326) {
        //     var_dump('当前资产'.$allUsdt);
        //     var_dump('0点的资产'.$yesterday_allUsdt);
        //     var_dump('充值'.$todayRechargeRecordSum);
        //     var_dump('后台充值的'.$todayHtSum);
        //     var_dump('贷款'.$todayLoanOrderSum);
        //     var_dump('提款'.$todayUsersWalletOutSum);
        // }
        
        // 资金账户盈亏 = 当前资金资产-0点的资产-充值-贷款-划转划入+划转划出+提款
        // 当日资金划转划入
        // $todayFundInSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEGAL_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_fund_ins = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEGAL_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayFundInSum = 0;
        foreach($today_fund_ins as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayFundInSum = bcadd($todayFundInSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 当日资金划转划出
        // $todayFundOutSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEGAL_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_fund_outs = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEGAL_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayFundOutSum = 0;
        foreach($today_fund_outs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayFundOutSum = bcadd($todayFundOutSum,bcmul($usdtVal,$v->value,6),6);
        }
        
        // if($user_id == 8500326) {
        //     var_dump('当前资产'.$legal_balance);
        //     var_dump('0点的资产'.$yesterday_legal_balance);
        //     var_dump('充值'.$todayRechargeRecordSum);
        //     var_dump('后台充值的'.$todayHtSum);
        //     var_dump('贷款'.$todayLoanOrderSum);
        //     var_dump('划转划入'.$todayFundInSum);
        //     var_dump('划转划出'.$todayFundOutSum);
        //     var_dump('提款'.$todayUsersWalletOutSum);
        // }
        
        // 后台操作的资金充值
        $ht_fund_account_logs = AccountLog::where('user_id',$user_id)->whereIn('type',[AccountLog::ADMIN_LEGAL_BALANCE])->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        
        $todayHtFundSum = 0;
        foreach($ht_fund_account_logs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayHtFundSum = bcadd($todayHtFundSum,bcmul($usdtVal,$v->value,6),6);
        }
        $todayHtFundSum = floatval($todayHtFundSum);
        
        $fund_profit_loss = $legal_balance - $yesterday_legal_balance - $todayRechargeRecordSum - $todayHtFundSum - $todayLoanOrderSum - $todayFundInSum + abs($todayFundOutSum) + $todayUsersWalletOutSum;
        
        // 现货账户，盈亏 = 当前合约资产-0点的资产-划转划入+划转划出
        // 当日现货划转划入
        // $todayChangeInSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_CHANGE_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_change_ins = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_CHANGE_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayChangeInSum = 0;
        foreach($today_change_ins as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayChangeInSum = bcadd($todayChangeInSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 当日现货划转划出
        // $todayChangeOutSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_CHANGE_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_change_outs = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_CHANGE_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayChangeOutSum = 0;
        foreach($today_change_outs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayChangeOutSum = bcadd($todayChangeOutSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 后台操作的现货充值
        $ht_change_account_logs = AccountLog::where('user_id',$user_id)->whereIn('type',[AccountLog::ADMIN_CHANGE_BALANCE])->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        
        $todayHtChangeSum = 0;
        foreach($ht_change_account_logs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayHtChangeSum = bcadd($todayHtChangeSum,bcmul($usdtVal,$v->value,6),6);
        }
        $todayHtChangeSum = floatval($todayHtChangeSum);
        
        // if($user_id == 8500326) {
        //     var_dump('当前资产'.$change_balance);
        //     var_dump('0点的资产'.$yesterday_change_balance);
        //     var_dump('后台充值'.$todayHtChangeSum);
        //     var_dump('划转划入'.$todayChangeInSum);
        //     var_dump('划转划出'.$todayChangeOutSum);
        // }
        $change_profit_loss = $change_balance - $yesterday_change_balance - $todayHtChangeSum  - $todayChangeInSum + abs($todayChangeOutSum);
        
        // 合约账户，盈亏 = 当前合约资产-0点的资产-划转划入+划转划出
        // 当日合约划转划入
        //  $todayLeverInSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEVER_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_lever_ins = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEVER_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayLeverInSum = 0;
        foreach($today_lever_ins as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayLeverInSum = bcadd($todayLeverInSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 当日合约划转划出
        // $todayLeverOutSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEVER_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_lever_outs = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_LEVER_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayLeverOutSum = 0;
        foreach($today_lever_outs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayLeverOutSum = bcadd($todayLeverOutSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 后台操作的合约充值
        $ht_lever_account_logs = AccountLog::where('user_id',$user_id)->whereIn('type',[AccountLog::ADMIN_LEVER_BALANCE])->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        
        $todayHtLeverSum = 0;
        foreach($ht_lever_account_logs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayHtLeverSum = bcadd($todayHtLeverSum,bcmul($usdtVal,$v->value,6),6);
        }
        $todayHtLeverSum = floatval($todayHtLeverSum);
        $lever_profit_loss = $lever_balance - $yesterday_lever_balance - $todayHtLeverSum  - $todayLeverInSum + abs($todayLeverOutSum);
        
        // 秒合约，盈亏 =  当前合约资产-0点的资产-划转划入+划转划出
        // 当日秒合约划转划入
        //  $todayMicroInSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_ACCOUNT_TRANSFER_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_micro_ins = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_MCIRO_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayMicroInSum = 0;
        foreach($today_micro_ins as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayMicroInSum = bcadd($todayMicroInSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 当日秒合约划转划出
        // $todayMicroOutSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_ACCOUNT_TRANSFER_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_micro_outs = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_MCIRO_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayMicroOutSum = 0;
        foreach($today_micro_outs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayMicroOutSum = bcadd($todayMicroOutSum,bcmul($usdtVal,$v->value,6),6);
        }
         // 后台操作的秒合约充值
        $ht_micro_account_logs = AccountLog::where('user_id',$user_id)->whereIn('type',[AccountLog::ADMIN_SECOND_LEVER_BALANCE])->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        
        $todayHtMicroSum = 0;
        foreach($ht_micro_account_logs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayHtMicroSum = bcadd($todayHtMicroSum,bcmul($usdtVal,$v->value,6),6);
        }
        $todayHtMicroSum = floatval($todayHtMicroSum);
        
        // if($user_id == 8500326) {
        //     var_dump('当前资产'.$micro_balance);
        //     var_dump('0点的资产'.$yesterday_micro_balance);
        //     var_dump('划转划入'.$todayMicroInSum);
        //     var_dump('划转划出'.$todayMicroOutSum);
        // }
        $micro_profit_loss = $micro_balance - $yesterday_micro_balance  - $todayMicroInSum + abs($todayMicroOutSum);
        
        // 理财账户，盈亏 =  当前合约资产-0点的资产-划转划入+划转划出
        // 当日理财划转划入
        //  $todayEarnInSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_EARN_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_earn_ins = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_EARN_IN)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayEarnInSum = 0;
        foreach($today_earn_ins as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayEarnInSum = bcadd($todayEarnInSum,bcmul($usdtVal,$v->value,6),6);
        }
        // 当日理财划转划出
        // $todayEarnOutSum = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_EARN_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->sum('value');
        $today_earn_outs = AccountLog::where('user_id',$user_id)->where('type',AccountLog::WALLET_EARN_OUT)->whereBetween('create_date', [$todayStart, $todayEnd])->get();
        $todayEarnOutSum = 0;
        foreach($today_earn_outs as $k => $v) {
            if ($v->currency == 23 || $v->currency == 57){
                $usdtVal=1;
            }else{
                $usdtVal=$this->getUsdtPriceV4($usdtPriceList,$v->currency);
            }
            $todayEarnOutSum = bcadd($todayEarnOutSum,bcmul($usdtVal,$v->value,6),6);
        }
        $earn_profit_loss = $earn_balance - $yesterday_earn_balance  - $todayEarnInSum + abs($todayEarnOutSum);
        
        $total_profit_loss = $change_profit_loss + $lever_profit_loss + $micro_profit_loss + $earn_profit_loss ;
        $wallet_data = [
            'allUsdt'=>$allUsdt,
            'earn_balance' => $earn_balance,
            'legal_balance' => $legal_balance,
            'change_balance' => $change_balance,
            'lever_balance' => $lever_balance,
            'micro_balance' => $micro_balance,
            'total_profit_loss' => $total_profit_loss,
            'fund_profit_loss' => 0,
            'change_profit_loss' => $change_profit_loss,
            'lever_profit_loss' => $lever_profit_loss,
            'micro_profit_loss' => $micro_profit_loss,
            'earn_profit_loss' => $earn_profit_loss
            
            
            // 'yesterday_allUsdt' => $yesterday_allUsdt,
            // 'todayRechargeRecordSum' => $todayRechargeRecordSum,
            // 'todayLoanOrderSum' => $todayLoanOrderSum,
            // 'todayUsersWalletOutSum' => $todayUsersWalletOutSum,
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }

}