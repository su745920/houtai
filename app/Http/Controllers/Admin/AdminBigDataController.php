<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog,
    Currency,
    Hq15min,
    Hq30,
    Hq5,
    Hq1min,
    LockMiningOrder,
    Setting,
    Users,
    UsersWallet,
    RechargeRecord};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AdminBigDataController extends Controller
{
    public function microyingkuiTj(){
        //亏
        $kuiSql="select  sum(fact_profits) kui from  micro_orders  where profit_result = -1";
        $kuiList=DB::select($kuiSql);
        $kuiPO=$kuiList[0];
        $kui=$kuiPO->kui;
        if (empty($kui)){
            $kui=0;
        }
        $result["kui"]=$kui;
        //
        $kuiSql="select  sum(fact_profits) ying from  micro_orders  where profit_result = 1";
        $kuiList=DB::select($kuiSql);
        $kuiPO=$kuiList[0];
        $kui=$kuiPO->ying;
        if (empty($kui)){
            $kui=0;
        }
        $result["ying"]=$kui;
        //
//        $kuiSql="select  sum(fact_profits) ping from  micro_orders  where profit_result = 0";
//        $kuiList=DB::select($kuiSql);
//        $kuiPO=$kuiList[0];
//        $kui=$kuiPO->ping;
//        if (empty($kui)){
//            $kui=0;
//        }
//        $result["ping"]=$kui;

        return $this->success($result);



    }
    public function getAssetsDistribution(){
        //1.usdt
        $usdtSql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  usdt  from  users_wallet w where  w.currency=23";
        $usdtList=DB::select($usdtSql);
        $usdtPO=$usdtList[0];
        $usdt=$usdtPO->usdt;
        if (empty($usdt)){
            $usdt=0;
        }
        $result["usdt"]=$usdt;

        //2.btc
        $btcSql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  btc  from  users_wallet w where  w.currency=32";
        $btcList=DB::select($btcSql);
        $btcPO=$btcList[0];
        $btc=$btcPO->btc;
        if (empty($btc)){
            $btc=0;
        }
        $btc=doubleval($btc);
        //

        $btcClose=Currency::getUsdtPriceV2("btc");
        $btcClose=doubleval($btcClose);
        $btc=bc_mul($btc,$btcClose);
        $result["btc"]=$btc;

        //ETH
        $ethSql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  eth  from  users_wallet w where  w.currency=35";
        $ethList=DB::select($ethSql);
        $ethPO=$ethList[0];
        $eth=$ethPO->eth;
        if (empty($eth)){
            $eth=0;
        }
        //

        $ethClose=Currency::getUsdtPriceV2("eth");
        $ethClose=doubleval($ethClose);
        $eth=bc_mul($eth,$ethClose);
        $result["eth"]=$eth;
        //
        $ethSql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  vnd  from  users_wallet w where  w.currency=81";
        $ethList=DB::select($ethSql);
        $ethPO=$ethList[0];
        $vnd=$ethPO->vnd;
        if (empty($vnd)){
            $vnd=0;
        }
        $vnd=doubleval($vnd);

        $vndHl=Setting::getValueByKey('vnd');
        $vndHl=doubleval($vndHl);
        $vnd=bc_div($vnd,$vndHl);
        $result["vnd"]=$vnd;
        //
        $cnySql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  cny  from  users_wallet w where  w.currency=63";
        $cnyList=DB::select($cnySql);
        $cnyPO=$cnyList[0];
        $cny=$cnyPO->cny;
        if (empty($cny)){
            $cny=0;
        }
        $cny=doubleval($cny);

        $rmbHl=Setting::getValueByKey('rmb');
        $rmbHl=doubleval($rmbHl);
        $cny=bc_div($cny,$rmbHl);
        $result["cny"]=$cny;
        //
        $idrSql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  idr  from  users_wallet w where  w.currency=84";
        $idrList=DB::select($idrSql);
        $idrPO=$idrList[0];
        $idr=$idrPO->idr;
        if (empty($idr)){
            $idr=0;
        }
        $idr=doubleval($idr);

        $idrHl=Setting::getValueByKey('idr');
        $idrHl=doubleval($idrHl);
        $idr=bc_div($idr,$idrHl);
        $result["idr"]=$idr;
        ////
        $idrSql="select  sum(w.legal_balance+change_balance+legal_balance+micro_balance+earn_balance)  thb  from  users_wallet w where  w.currency=82";
        $idrList=DB::select($idrSql);
        $idrPO=$idrList[0];
        $idr=$idrPO->thb;
        if (empty($idr)){
            $idr=0;
        }
        $idr=doubleval($idr);

        $idrHl=Setting::getValueByKey('thb');
        $idrHl=doubleval($idrHl);
        $idr=bc_div($idr,$idrHl);
        $result["thb"]=$idr;


        return $this->success($result);




    }
    public function getOnlineCount(){
        $onlineUserCnt=0;
        $onlineUserStr="";
        try{
            $onlineUserStr = Redis::get("onlineUserStr");


            $arraylist=explode("#", $onlineUserStr);

            for($i=0;$i<count($arraylist);$i++) //把它们全部输出来
            {
                $uid=$arraylist[$i];
                if (!empty($uid)&&intval($uid)>0){
                    $onlineUserCnt = $onlineUserCnt + 1;
                }

            }

        }catch (\Exception $ex){

        }
        $result["onlineUserCnt"] = $onlineUserCnt;
        return $this->success($result);
    }
    public function czTipFn(){
        $onlineUserCnt=0;
        $onlineUserStr="";
        $list = Users::get();
         foreach ($list as $k => $v) {
            $userStr="#".$v->id.'#';
            $t=Redis::get($userStr);
            if ($t&&(strlen($t)>5||$t=="online")){
               $onlineUserCnt += 1;
            }
         }


        $allUSDTSql="select  count(1) as czTipCount from recharge_record where  `status`=0 ";
        $allUSDTOrderList=DB::select($allUSDTSql);

        $allUSDTOrderPO=$allUSDTOrderList[0];
        $allUSDT=$allUSDTOrderPO->czTipCount;
        if (empty($allUSDT)){
            $allUSDT=0;
        }
        $allUSDT=intval($allUSDT);
        $result["czTipCount"]=$allUSDT;
        //
        $withdrawAmountSql="select  count(1) as withdrawTipCount from users_wallet_out  where  `status`=1 ";
        $withdrawAmountList=DB::select($withdrawAmountSql);

        $withdrawAmountPO=$withdrawAmountList[0];
        $withdrawAmount=$withdrawAmountPO->withdrawTipCount;
        if (empty($withdrawAmount)){
            $withdrawAmount=0;
        }
        $withdrawAmount=intval($withdrawAmount);
        $result["withdrawTipCount"]=$withdrawAmount;

        $result["onlineUserCnt"] = $onlineUserCnt;
        $result["onlineUserStr"]=$onlineUserStr;

        return $this->success($result);
    }

    //  http://192.168.41.26/admin/homeBigData
    public function home(){

        //总用户数量
        $sql = "select count(1) as allUserCount from users ";
        $allUserList = DB::select($sql);
        $allUserPO=$allUserList[0];
        $allUserCount=$allUserPO->allUserCount;
        $result["allUserCount"]=$allUserCount;

        //今日注册用户
        $todayRegSql="select count(1) as  todayRegCount from users where  DATE_FORMAT(FROM_UNIXTIME(time), '%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')";
        $todayRegList = DB::select($todayRegSql);
        $todayRegPO=$todayRegList[0];
        $todayRegCount=$todayRegPO->todayRegCount;
        if (empty($todayRegCount)){
            $todayRegCount=0;
        }
        $result["todayRegCount"]=$todayRegCount;

        //合约订单数量
        $swapOrderSql="select  count(1) as  swapOrderCnt from lever_transaction";
        $swapOrderList=DB::select($swapOrderSql);
        $swapOrderCntPO=$swapOrderList[0];
        $swapOrderCount=$swapOrderCntPO->swapOrderCnt;
        if (empty($swapOrderCount)){
            $swapOrderCount=0;
        }
        $result["swapOrderCount"]=$swapOrderCount;

        //今日合约订单数量
        $todaySwapOrderSql="select  count(1) as  todaySwapOrderCnt from lever_transaction where  DATE_FORMAT(FROM_UNIXTIME(create_time), '%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')";
        $todaySwapOrderList=DB::select($todaySwapOrderSql);
        $todaySwapOrderPO=$todaySwapOrderList[0];
        $todaySwapOrderCount=$todaySwapOrderPO->todaySwapOrderCnt;
        if (empty($todaySwapOrderCount)){
            $todaySwapOrderCount=0;
        }
        $result["todaySwapOrderCount"]=$todaySwapOrderCount;


        //今日秒合约订单数量
        $todayMicroOrderSql="select  count(1) as  todayMicroOrderCnt from micro_orders  where DATE_FORMAT(created_at, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d')";
        $todayMicroOrderList=DB::select($todayMicroOrderSql);
        $todayMicroOrderPO=$todayMicroOrderList[0];
        $todayMicroOrderCnt=$todayMicroOrderPO->todayMicroOrderCnt;
        if (empty($todayMicroOrderCnt)){
            $todayMicroOrderCnt=0;
        }
        $result["todayMicroOrderCnt"]=$todayMicroOrderCnt;

        //秒合约订单总订单
        $microOrderSql="select  count(1) as  microOrderCnt from micro_orders";
        $microOrderList=DB::select($microOrderSql);
        $microOrderPO=$microOrderList[0];
        $microOrderCnt=$microOrderPO->microOrderCnt;
        if (empty($microOrderCnt)){
            $microOrderCnt=0;
        }
        $result["allMicroOrderCnt"]=$microOrderCnt;






        //锁仓订单数量
        $lockMiningOrderSql="select  count(1) as lockMiningOrderCnt from lock_mining_order";
        $lockMiningOrderList=DB::select($lockMiningOrderSql);
        $lockMiningOrderPO=$lockMiningOrderList[0];
        $lockMiningOrderCnt=$lockMiningOrderPO->lockMiningOrderCnt;
        if (empty($lockMiningOrderCnt)){
            $lockMiningOrderCnt=0;
        }
        $result["lockMiningOrderCnt"]=$lockMiningOrderCnt;

        //锁仓订单数量
        $todayLockMiningOrderSql="select  count(1) as todayLockMiningOrderCnt from lock_mining_order  where  DATE_FORMAT(create_date, '%Y-%m-%d')=DATE_FORMAT(now(),'%Y-%m-%d')";
        $todayLockMiningOrderList=DB::select($todayLockMiningOrderSql);
        $todayLockMiningOrderPO=$todayLockMiningOrderList[0];
        $todayLockMiningOrderCnt=$todayLockMiningOrderPO->todayLockMiningOrderCnt;
        if (empty($todayLockMiningOrderCnt)){
            $todayLockMiningOrderCnt=0;
        }
        $result["todayLockMiningOrderCnt"]=$todayLockMiningOrderCnt;
        //

        $allUSDTSql="select  sum(money) as allUSDT from recharge_record where  `status`=1 and currency=23";
        $allUSDTOrderList=DB::select($allUSDTSql);

        $allUSDTOrderPO=$allUSDTOrderList[0];
        $allUSDT=$allUSDTOrderPO->allUSDT;
        if (empty($allUSDT)){
            $allUSDT=0;
        }
        $allUSDT=intval($allUSDT);
        $result["allUSDT"]=$allUSDT;
        //
        $todayUSDTSql="select   money,currency  from recharge_record where  `status`=1 and  DATE_FORMAT(created_at, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d') ";
        $todayUSDTOrderList=DB::select($todayUSDTSql);

        $todayUSDT=0;
        if (!empty($todayUSDTOrderList)){
            foreach ($todayUSDTOrderList as $czPO){
                $currency=$czPO->currency;
                $money=$czPO->money;

                if ($currency==32){
                    $hl=Currency::getUsdtPriceV2("btc");
                    $val=bc_mul($money,$hl);
                    $todayUSDT=$todayUSDT+$val;
                }
                if ($currency==35){
                    $hl=Currency::getUsdtPriceV2("eth");
                    $val=bc_mul($money,$hl);
                    $todayUSDT=$todayUSDT+$val;
                }

                if ($currency==63){
                    $rmb = Setting::getValueByKey('rmb');
                    $val=bc_div($money,$rmb);
                    $todayUSDT=$todayUSDT+$val;
                }
                if ($currency==81){
                    $vnd = Setting::getValueByKey('vnd');
                    $val=bc_div($money,$vnd);
                    $todayUSDT=$todayUSDT+$val;
                }
                if ($currency==82){
                    $thb = Setting::getValueByKey('thb');
                    $val=bc_div($money,$thb);
                    $todayUSDT=$todayUSDT+$val;
                }
                if ($currency==84){
                    $idr = Setting::getValueByKey('idr');
                    $val=bc_div($money,$idr);
                    $todayUSDT=$todayUSDT+$val;
                }




            }
        }
        $result["todayUSDT"]=$todayUSDT;


        //
        $withdrawAmountSql="select  sum(number) as withdrawAmount from users_wallet_out  where  DATE_FORMAT(FROM_UNIXTIME(`create_time`), '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d')";
        $withdrawAmountList=DB::select($withdrawAmountSql);

        $withdrawAmountPO=$withdrawAmountList[0];
        $withdrawAmount=$withdrawAmountPO->withdrawAmount;
        if (empty($withdrawAmount)){
            $withdrawAmount=0;
        }
        $withdrawAmount=intval($withdrawAmount);
        $result["todayWithdrawUSDTAmount"]=$withdrawAmount;



        $cjSql="select  count(1) cj_count  from  dzp_user where  DATE_FORMAT(create_time, '%Y-%m-%d') = DATE_FORMAT(now(), '%Y-%m-%d')";
        $cjList=DB::select($cjSql);

        $cjPO=$cjList[0];
        $cj_count=$cjPO->cj_count;
        if (empty($cj_count)){
            $cj_count=0;
        }
        $cj_count=intval($cj_count);
        $result["cj_count"]=$cj_count;
        //
        $c2cSql="select count(1)   c2c_audit_cnt from  seller where  audit_status=0 ";
        $c2cList=DB::select($c2cSql);

        $c2cPO=$c2cList[0];
        $c2c_audit_cnt=$c2cPO->c2c_audit_cnt;
        if (empty($c2c_audit_cnt)){
            $c2c_audit_cnt=0;
        }
        $c2c_audit_cnt=intval($c2c_audit_cnt);
        $result["c2c_audit_cnt"]=$c2c_audit_cnt;

        $loginSql="select   count(1) as  todayLoginCnt  from users u where  DATE_FORMAT(FROM_UNIXTIME(u.lastlogin_time), '%Y-%m-%d') =  DATE_FORMAT(now(), '%Y-%m-%d')";
        $loginList=DB::select($loginSql);

        $loginPO=$loginList[0];
        $todayLoginCnt=$loginPO->todayLoginCnt;
        if (empty($todayLoginCnt)){
            $todayLoginCnt=0;
        }
        $todayLoginCnt=intval($todayLoginCnt);
        $result["todayLoginCnt"]=$todayLoginCnt;


        //
        $ieoSql="select   count(1) as  todayIEOCnt  from currency_project_order u where  DATE_FORMAT(u.created_at, '%Y-%m-%d') =  DATE_FORMAT(now(), '%Y-%m-%d')";
        $ieoList=DB::select($ieoSql);

        $ieoPO=$ieoList[0];
        $todayIEOCnt=$ieoPO->todayIEOCnt;
        if (empty($todayIEOCnt)){
            $todayIEOCnt=0;
        }
        $todayIEOCnt=intval($todayIEOCnt);
        $result["todayIEOCnt"]=$todayIEOCnt;





        return $this->success($result);


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