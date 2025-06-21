<?php

namespace App\Http\Controllers\Api;

use App\Models\InsuranceClaimApply;
use App\Models\InsuranceRule;
use App\Models\RewardGroup;
use App\Models\Setting;
use App\Models\UsersInsurance;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Logic\MicroTradeLogic;
use App\Models\Users;
use App\Models\CurrencyQuotation;
use App\Models\Currency;
use App\Models\MicroSecond;
use App\Models\UsersWallet;
use App\Models\LockMining;
use App\Models\MarketHour;
use App\Models\CurrencyMatch;
use App\Models\InsuranceType;
use App\Models\MicroNumbers;
use App\Models\LockMiningOrder;
use App\Models\AccountLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App;

class LockOrderController extends Controller
{
//手机端首页挖矿推荐2个--2023-04-18
public function getV3LockRecommendList(Request $request)
    {
        // $pagenNum = $request->input('page', 1);
        // $limit = $request->input('limit', 8);
        // $page=$pagenNum-1;
        // if ($page != 0) {
        //   $page = $limit * $page;
        //   $limit=$limit*$pageNum;
        // }
        $list = LockMining::where('status', 1)->offset(0)->limit(2)->get();

        return $this->success($list);
    }
    
     
    public function saveGroupMiningReward()
    {
        //1.找出符合条件的订单
        $sql = "select o.parent_id,count(o.parent_id) as orderCount,MIN(money) as baseMoney,rate,user_id  from lock_mining_order o where money>=10000 and is_reward=0 and o.`day`=30 and o.parent_id is not null  GROUP BY o.parent_id";
        $orderInfos = DB::select($sql);
        if (!empty($orderInfos)&&count($orderInfos)>0) {
            //存在符合条件的订单 在查询相关订单
            $po = $orderInfos[0];
            $orderCount=$po->orderCount;

            if (!empty($orderCount)&&$orderCount>=3) {
                //1.查询相关订单
                $parentId = $po->parent_id;
                DB::update("update  lock_mining_order set is_reward=1 where parent_id=".$parentId." and money>=10000 and day=30");



                $parent = Users::getById($parentId);
                $baseMoney = $po->baseMoney;
                $rate = $po->rate;
                $user_id = $po->user_id;
                $depositUser = Users::getById($user_id);
                print_r($po->parent_id);


                $remark = "1W-30天锁仓挖矿";


                $reward_rate = 0.2 * $rate; //'奖励数量',
                $create_date = date("Y-m-d H:i");
                $status = 0;   //'0:待审核  1:审核通过  -1:拒绝',
                $category = 4;//'分类',
                $base_money = $baseMoney;
                $deposit_username = $depositUser->account_number;
                $reward_username = $parent->account_number;
                $reward = [
                    'rate' => $reward_rate,
                    'reward_username' => $reward_username,
                    'deposit_username' => $deposit_username,
                    'base_money' => $base_money,//上级计息本金
                    'category' => $category,
                    'status' => $status,
                    'create_date' => $create_date,
                    'remark' => $remark,
                    'parent_uid' => $parentId,
                    'days'  =>30,

                ];
                $res = DB::table('mining_reward_group')->insert($reward);
            }
        }
        //2.更新锁仓挖矿订单状态 is_reward=1

        //同时5人订购Mining90天为基础进行购买金额50000$以上，上级用户可以享受额外每天0.5%Mining利息奖励
        $sql = "select o.parent_id,count(o.parent_id) as orderCount,MIN(money) as baseMoney,rate,user_id  from lock_mining_order o where money>=50000 and is_reward=0 and o.`day`=90 and o.parent_id is not null  GROUP BY o.parent_id";
        $orderInfos = DB::select($sql);
        if (!empty($orderInfos)&&count($orderInfos)>0) {
            $po = $orderInfos[0];
            $parentId = $po->parent_id;
            $parent = Users::getById($parentId);
            $baseMoney = $po->baseMoney;
            $rate = $po->rate;
            $user_id = $po->user_id;
            $depositUser = Users::getById($user_id);
            print_r($po->parent_id);


            $reward_rate = 0.5 * $rate; //'奖励数量',
            $create_date = date("Y-m-d H:i");

            $remark = "5W-90天锁仓挖矿";

            $status = 0;   //'0:待审核  1:审核通过  -1:拒绝',

            $category = 5;//'分类',




            $deposit_username = $depositUser->account_number;
            $reward->deposit_username = $deposit_username;

            $reward_username = $parent->account_number;
            $reward->reward_username = $reward_username;


            $reward = [
                'rate'       => $reward_rate,
                'reward_username'       => $reward_username,
                'base_money' => $baseMoney,
                'parent_uid' => $parentId,
                'category'       => $category,
                'status'    => $status,
                'create_date'    => $create_date,
                'remark'     =>$remark,
                'days'  =>90
            ];
            $res = DB::table('reward_group')->insert($reward);

            DB::update("update  lock_mining_order set is_reward=1 where parent_id=".$parentId." and money>=50000 and day=90");
        }

        return $this->success("操作成功");


    }


    public function shouyiFn()
    {
        $list = db::table('lock_mining_order')->get();
        foreach ($list as $order) {
            $user_id = $order->user_id;
            $money = $order->money;
            $lock_id = $order->lock_id;
            $product = LockMining::where('id', $lock_id)->first();//产品
            $rate_min = $product->rate;
            $rate_max = $product->rate_max;
            $rate = $rate_min;
            $day = $order->day;
            $minShouyi = $rate * 0.01 * $money;//每天收益
            $daylong = intval($day) * 86400;//项目总的持续秒数
            $created_at = $order->created_at;
            $p = intval($created_at) + $daylong;
            $now = intval(time());
            //
            $pDate = date('Y-m-d', $p);
            $nowDate = date('Y-m-d', $now);

            $settlement=$order->settlement;//是否已经结算利息

            if ($pDate > $nowDate) {

                //echo "p==".$p." now==".$now."=ID==".$order->id."====";


                try {
                    $lixisql = 'update lock_mining_order set interest = interest+' . $minShouyi . ' where id = ' . $order->id;
                    print_r($lixisql);
                    DB::update($lixisql);

                    DB::beginTransaction();
                    $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $order->from_name)->first();
                    //change_wallet_balance($wallet, 4, $minShouyi, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 35, "true");

                    change_wallet_lock_balance($wallet, 4, $minShouyi, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 'Lock up mining profits', true);


                    DB::commit();

                } catch (\Throwable $ex) {
                    DB::rollBack();
                    return $this->success($ex->getMessage());
                }
            }else{
                $lockOrder= LockMiningOrder::find($order->id);
                $status=$lockOrder->status;

                $interest=$lockOrder->interest;
                if (empty($interest)){
                    $interest=0;
                }




                if ($settlement==0){

                    $minShouyi = $rate * 0.01 * $money;//每天收益

                    $allLixi=bc_mul($minShouyi,$day);

                    $wallet = UsersWallet::where('user_id', $user_id)->where('currency', 23)->first();

                    change_wallet_balance($wallet, 4, $allLixi, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 'Lock up mining profits', true);



                    //change_wallet_lock_balance($wallet, 4, $interest, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 35, "true");
                    change_wallet_balance($wallet, 4, $money, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿返还本金', 'Lock up mining and return of principal', true);
                    $p = intval($created_at) + $daylong;

                    $dqStr=date('Y-m-d', $p);
                    echo "利息===".$allLixi;




                    $lixisql = "update lock_mining_order set interest=".$allLixi.", settlement=1, status=1,complete_at='".$p."' where id = " . $order->id;
                    DB::update($lixisql);


                }









            }

         
          
//            if ($p > $now) {
//                   echo "p==".$p." now==".$now."=ID==".$order->id."====";
//                   try {
//                    $lixisql = 'update lock_mining_order set interest = interest+' . $minShouyi . ' where id = ' . $order->id;
//                    print_r($lixisql);
//                    DB::update($lixisql);
//
//                    DB::beginTransaction();
//                    $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $order->from_name)->first();
//                    change_wallet_balance($wallet, 4, $minShouyi, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 35, "true");
//                    DB::commit();
//
//                } catch (\Throwable $ex) {
//                    DB::rollBack();
//                    return $this->success($ex->getMessage());
//                }
//            }else{
//                $lixisql = 'update lock_mining_order set  status=1 where id = ' . $order->id;
//                //print_r($lixisql);
//                DB::update($lixisql);
//            }



            //
        }
        return $this->success(trans('lock.syjsfpcg'));
    }

    //锁仓挖矿数据统计
    public function bigData(Request $request)
    {
        $user_id = Users::getUserId();
        $orderCnt = LockMiningOrder::where('user_id', $user_id)->where('status', 0)->count();
        $data["orderCnt"] = $orderCnt;
        //
        $userPO = Users::where('id', $user_id)->first();
        $lock_shouyi = $userPO->lock_shouyi;
        if (empty($lock_shouyi)) {
            $lock_shouyi = 0;
        }
        $data["lock_shouyi"] = number_format($lock_shouyi, 8);
        //20221122 加status = 0 条件
        $lockAllMoney = LockMiningOrder::where('user_id', $user_id)->where('status', 0)->sum('money');
        if (empty($lockAllMoney)) {
            $lockAllMoney = 0;
        }
        //
        $todyShouyi = 0;
        $list = db::table('lock_mining_order')->where('user_id', $user_id)->get();
        foreach ($list as $order) {
            $user_id = $order->user_id;
            $money = $order->money;
            $lock_id = $order->lock_id;
            $product = LockMining::where('id', $lock_id)->first();//产品
            $rate_min = $product->rate;
            $rate_max = $product->rate_max;
            $day = $product->day;
            $maxShouyi = $rate_max * 0.01 * $money;//每天收益
            $daylong = intval($day) * 86400;//项目总的持续秒数
            $created_at = $order->created_at;
            //20221122 加status = 0 条件
            if ($created_at + $daylong >= time() && $order->status == 0) {
                $todyShouyi = $todyShouyi + $maxShouyi;
            }
        }
         // 昨日收益
        // 获取昨天的开始和结束时间
        $yesterdayStart = Carbon::yesterday()->startOfDay()->timestamp;
        $yesterdayEnd = Carbon::yesterday()->endOfDay()->timestamp;
        $yesterdayShouyi = AccountLog::where("user_id", $user_id)
        ->where('type', AccountLog::LOCK_MINING_PROFIT)
        ->whereBetween('created_time', [$yesterdayStart, $yesterdayEnd])
        ->sum('value');
        //2.累计收益
        $allShouyi = AccountLog::where("user_id", $user_id)->where('type', AccountLog::LOCK_MINING_PROFIT)->sum('value');
        // $logList = AccountLog::where("user_id", $user_id)->where('type', 255)->get();
        // foreach ($logList as $log) {
        //     $allShouyi = $allShouyi + $log->value;
        // }
        $data["allShouyi"] = number_format($allShouyi, 8,'.','');
        $data["todyShouyi"] = number_format($todyShouyi, 8,'.','');
        $data["lockAllMoney"] = number_format($lockAllMoney, 8,'.','');
        $data["yesterdayShouyi"] = number_format($yesterdayShouyi,8,'.','');
        return $this->success($data);
    }

    public function getDetail(Request $request)
    {
        // $pagenNum = $request->input('page', 1);
        // $limit = $request->input('limit', 8);
        // $page=$pagenNum-1;
        // if ($page != 0) {
        //   $page = $limit * $page;
        //   $limit=$limit*$pageNum;
        // }
        $id = $request->input('id', "");
        $po = LockMining::where('id', $id)->first();
        //
        $user_id = Users::getUserId();

        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();

        $usdt = isset($wal->earn_balance) ? $wal->earn_balance : '0.00';//资产账户余额，这里只使用资产账户余额  币币的在这里无法使用
        $po["usdt"] = intval($usdt);
        //
        $rate1 = $po->rate;
        $rate_max = $po->rate_max;
        $min_money = $po->min_money;
        $max_money = $po->max_money;
        //20221122 去除*$day
        //$day=$po->day;
        $minShouyi = $rate1 * 0.01 * $min_money;//*$day;
        $maxShouyi = $rate_max * 0.01 * $max_money;//*$day;
        $po->mayShouyi = $minShouyi . "~" . $maxShouyi;


        //
        return $this->success($po);
    }


    /**
     * 获得锁仓挖矿列表
     */
    public function getOrder(Request $request)
    {
        // $pagenNum = $request->input('page', 1);
        // $limit = $request->input('limit', 8);
        // $page=$pagenNum-1;
        // if ($page != 0) {
        //   $page = $limit * $page;
        //   $limit=$limit*$pageNum;
        // }
        $list = LockMining::where('status', 1)->get();

        return $this->success($list);
    }

    /*
    购买资源
    */
    /**
     * 下单
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request)
    {
        $user_id = Users::getUserId();
        $id = $request->input('id', 0);
        $number = $request->input('number', 0);
        $key = 'lan_type_' . $user_id;


        $currency_id = 23;

        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',

            'number' => 'required|numeric|min:0.0000001',
        ], [], [
            'id' => '锁仓ID',

            'number' => '存币数量',
        ]);
        //进行基本验证
        throw_if($validator->fails(), new \Exception($validator->errors()->first()));
        $lockmining = db::table('lock_mining')->where('id', $id)->where('status', 1)->first();
        $lockmining = json_decode(json_encode($lockmining), true);
        if (empty($lockmining)) {
            return $this->success(trans('lock.sczybcz'));

        }
        if ($number < $lockmining['min_money']) {
            return $this->success(trans('lock.cbsl') . $lockmining['min_money']);

        }
        $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $lockmining['from_name'])->first();
        if ($lockmining['money'] > $wallet['earn_balance']) {
            return $this->success(trans('lock.zjbz'));

        }
        // var_dump($user_id);
        // exit;
        $user = Users::getById($user_id);
        $parent_id = $user->parent_id;//上级用户
        if (empty($parent_id)) {
            $parent_id = 0;
        }

        try {
            DB::beginTransaction();
            $order_data = [
                'user_id' => $user_id,
                'lock_id' => $id,
               'interest' => 0,
                'from_name' => $lockmining['from_name'],
                'to_name' => $lockmining['to_name'],
                'day' => $lockmining['day'],
                'money' => $number,
                'rate' => $lockmining['rate'],
                'rate_max' => $lockmining['rate_max'],
                'level' => $lockmining['title'],
                'is_reward' => 0,
                'parent_id' => $parent_id
            ];
            $lock_order = LockMiningOrder::create($order_data);

            $from_result = change_wallet_balance($wallet, 4, -$number, AccountLog::LOCK_MINING, '锁仓挖矿扣除可用资产', 'Lock up mining deducts available assets');
            $to_result = change_wallet_lock_balance($wallet, 4, $number, AccountLog::LOCK_MINING, '锁仓挖矿增加冻结资产', 'Lock up mining increases frozen assets', true);
            DB::commit();
            // 机器人推送消息
            robotSendMessage($user_id,'质押生息'.$number);
            return $this->success(trans('lock.cbcg'));
        } catch (\Throwable $ex) {
            DB::rollBack();
            return $this->success($ex->getMessage());
        }

    }

    /**
     * 订单
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lists(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        try {
            $user_id = Users::getUserId();
            $pageNum = $request->input('page', 1);
            $limit = $request->input('limit', 8);
            $page = $pageNum - 1;

            if ($page != 0) {
                $page = $limit * $page;
                $limit = $limit * $pageNum;
            }

            //$status = $request->input('status', -1);
            // $match_id = $request->input('match_id', -1);
            // $currency_id = $request->input('currency_id', -1);
            // $lists = db::table('lock_mining_order')->where('user_id',$user_id)->orderBy('id', 'desc')
            //     ->offset($page)->limit($limit)->get();
            $lists = db::table('lock_mining_order')->where('user_id', $user_id)->orderBy('status', 'asc')->orderBy('id', 'desc')
                ->offset($page)->limit($limit)->get();

            $lists = json_decode(json_encode($lists), true);
            foreach ($lists as $key => $val) {
                $lists[$key]['from_name'] = Currency::where('id', $val['from_name'])->value('name');
                $lists[$key]['to_name'] = Currency::where('id', $val['to_name'])->value('name');
                $lists[$key]['created_at'] = date('Y-m-d', $val['created_at']);
                if (empty($val['complete_at'])) {
                    $lists[$key]['complete_at'] = date('Y-m-d', $val['created_at'] + ($val['day'] * 86400));
                } else {
                    $lists[$key]['complete_at'] = date('Y-m-d', $val['complete_at']);
                }
                if ($val['status'] == '7') {
                    $lists[$key]['status'] = '提前赎回';
                } else if ($val['status'] == '1') {
                    $lists[$key]['status'] = '已结束';
                } else {
                    $lists[$key]['status'] = '进行中';
                }

                //
                $lock_id = $val['lock_id'];
                $lock = db::table('lock_mining')->where('id', $lock_id)->first();
                $lists[$key]['rate_max'] = $lock->rate_max;
                $adance_redeem_falsify = $lock->adance_redeem_falsify;
                $lists[$key]['adance_redeem_falsify'] = $adance_redeem_falsify;
                ///

                //2023-03-04增加当日收益 累计收益
                $money = $val['money'];
              
                $product = LockMining::where('id', $lock_id)->first();//产品
                $rate_min = $product->rate;
                $rate_max = $product->rate_max;
                $day = $product->day;
                $maxShouyi = $rate_max * 0.01 * $money;//每天收益
                $daylong = intval($day) * 86400;//项目总的持续秒数
                $created_at = $val['created_at'];
                $ljShouyi=0;
                if ($created_at + $daylong >= time() && $val['status'] == 0) {
                    $cha=time()-$created_at;
                    $reallyDays=ceil(intval($cha)/86400);
                    $ljShouyi=$maxShouyi*$reallyDays;

                    $lists[$key]['todayShouyi'] = $maxShouyi;

                }else{
                    $ljShouyi=$maxShouyi*intval($day);

                    $lists[$key]['todayShouyi'] = "0";
                }
                $lists[$key]['ljShouyi'] = $ljShouyi;



                ///
            }
            // $lists = LockMiningOrder::where('user_id', $user_id)
            //     // ->when($status <> -1, function ($query) use ($status) {
            //     //     $query->where('status', $status);
            //     // })
            //     // ->when($match_id <> -1, function ($query) use ($match_id) {
            //     //     $query->where('match_id', $match_id);
            //     // })
            //     // ->when($currency_id <> -1, function ($query) use ($currency_id) {
            //     //     $query->where('currency_id', $currency_id);
            //     // })
            //     ->orderBy('id', 'desc')
            //     ->offset($page)->limit($limit)->get();


            // foreach($lists as $key => $val){
            //     $lists[$key]['profit_res'] = $val['end_price'] - $val['open_price'] - $val['fee'];
            // }
            // $lists->each(function ($item, $key) {
            //     return $item->append('remain_milli_seconds');
            // });
            /*
            $results = $lists->getCollection();
            $results->transform(function ($item, $key) {
                return $item->append('remain_milli_seconds');
            });
            $lists->setCollection($results);
            */
            return $this->success($lists);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }


    public function wallet(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $user_id = Users::getUserId();
        $id = $request->input('id', 0);
        //$from_name = $request->input('from_name', 0);

        $lockmining = db::table('lock_mining')->where('id', $id)->where('status', 1)->first();
        if (empty($lockmining)) {

            return $this->error(trans('lock.syjsfpcg'));
        }
        $lockmining = json_decode(json_encode($lockmining), true);

        $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $lockmining['from_name'])->first();
        return $this->success($wallet);
    }

}
