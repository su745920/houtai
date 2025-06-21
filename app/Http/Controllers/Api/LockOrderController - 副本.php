<?php

namespace App\Http\Controllers\Api;

use App\Models\InsuranceClaimApply;
use App\Models\InsuranceRule;
use App\Models\Setting;
use App\Models\UsersInsurance;
use Carbon\Carbon;
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
class LockOrderController extends Controller
{
    
    public function shouyiFn(){
        $list = db::table('lock_mining_order')->get();
        foreach ($list as $order){
            $user_id=$order->user_id;
            $money=$order->money;
            $lock_id=$order->lock_id;
            $product = LockMining::where('id',$lock_id)->first();//产品
            $rate_min=$product->rate;
            $rate_max=$product->rate_max;
            $rate=$rate_min;
            $day=$product->day;
            $minShouyi=$rate*0.01*$money;//每天收益
            $daylong = intval($day) * 86400;//项目总的持续秒数
            $created_at=$order->created_at;
            if($created_at+$daylong >= time()) {
                try {
                    DB::beginTransaction();
                    $wallet = UsersWallet::where('user_id', $user_id)->where('currency', $order->from_name)->first();
                    change_wallet_balance($wallet, 2, $minShouyi, AccountLog::LOCK_MINING_PROFIT, '锁仓挖矿收益', 35, "true");
                    DB::commit();
                    return $this->success("success");
                } catch (\Throwable $ex) {
                    DB::rollBack();
                    return $this->success($ex->getMessage());
                }
            }
        }
    }
    
    //锁仓挖矿数据统计
    public function bigData(Request $request){
        $user_id = Users::getUserId();
        $orderCnt=LockMiningOrder::where('user_id',$user_id)->count();
        $data["orderCnt"]=$orderCnt;
        
        $userPO=Users::where('id',$user_id)->first();
        $lock_shouyi=$userPO->lock_shouyi;
        if (empty($lock_shouyi)){
            $lock_shouyi=0;
        }
        $data["lock_shouyi"]=$lock_shouyi;
        
        $lockAllMoney =LockMiningOrder::where('user_id',$user_id)->sum('money');
        if (empty($lockAllMoney)){
            $lockAllMoney=0;
        }
        $data["lockAllMoney"]=$lockAllMoney;
        
        return $this->success($data);
    }
    
    public function getDetail(Request $request){
        // $pagenNum = $request->input('page', 1);
        // $limit = $request->input('limit', 8);
        // $page=$pagenNum-1;
        // if ($page != 0) {
        //   $page = $limit * $page;
        //   $limit=$limit*$pageNum;
        // }
        $id=$request->input('id', "");
        $po = LockMining::where('id',$id)->first();
        //
        $user_id = Users::getUserId();

        $us = DB::table('currency')->where('name', 'USDT')->first();

        $wal = UsersWallet::where('currency', $us->id)->where('user_id', $user_id)->first();

        $usdt= isset($wal->lever_balance) ? $wal->lever_balance : '0.00';
        $po["usdt"]=intval($usdt);
        //
        $rate1=$po->rate;
        $rate_max=$po->rate_max;
        $min_money=$po->min_money;
        $max_money=$po->max_money;
        $day=$po->day;
        $minShouyi=$rate1*0.01*$min_money*$day;
        $maxShouyi=$rate_max*0.01*$max_money*$day;
        $po->mayShouyi=$minShouyi."~".$maxShouyi;
        
        return $this->success($po);
    }
    
     /**
     * 获得锁仓挖矿列表
     */
    public function getOrder(Request $request){
        // $pagenNum = $request->input('page', 1);
        // $limit = $request->input('limit', 8);
        // $page=$pagenNum-1;
        // if ($page != 0) {
        //   $page = $limit * $page;
        //   $limit=$limit*$pageNum;
        // }
        $list = LockMining::where('status',1)->get();
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
        $key = 'lan_type_'.$user_id;
        $lang = Cache::get($key);
        
        $currency_id =23;
        
        if(!$lang){
            $lang = 'zh_cn';
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
            $lockmining = db::table('lock_mining')->where('id',$id)->where('status',1)->first();
            $lockmining = json_decode(json_encode($lockmining), true);
            if(empty($lockmining)){
                return $this->success(trans('lock.sczybcz'));
               
            }
            if($number < $lockmining['min_money']){
                return $this->success(trans('lock.cbsl').$lockmining['min_money']);
                
            }
            $wallet = UsersWallet::where('user_id',$user_id)->where('currency', $lockmining['from_name'])->first();
            if($lockmining['money'] > $wallet['lever_balance']){
                return $this->success(trans('lock.zjbz'));
                 
            }
            // var_dump($user_id);
            // exit;
            
           try {
                DB::beginTransaction();
                $order_data = [
                    'user_id' => $user_id,
                    'lock_id' => $id,
                    'rate'=> $lockmining['rate'],
                    'from_name' => $lockmining['from_name'],
                    'to_name' => $lockmining['to_name'],
                    'day' =>  $lockmining['day'],
                    'money' => $number,
                    'rate' =>$lockmining['rate'],
                    'level' =>$lockmining['title']
                ];
                $lock_order = LockMiningOrder::create($order_data);
                $from_result = change_wallet_balance($wallet, 2, -$number, AccountLog::LOCK_MINING, '锁仓挖矿扣除可用资产',36);
                $to_result = change_wallet_balance($wallet, 2, $number, AccountLog::LOCK_MINING, '锁仓挖矿增加冻结资产',35,"true");
                DB::commit();
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
        try {
            $user_id = Users::getUserId();
            $pageNum = $request->input('page', 1);
            $limit = $request->input('limit', 8);
            $page=$pageNum-1;
           
            if ($page != 0) {
              $page = $limit * $page;
              $limit=$limit*$pageNum;
            }
           
            //$status = $request->input('status', -1);
            // $match_id = $request->input('match_id', -1);
            // $currency_id = $request->input('currency_id', -1);
            $lists = db::table('lock_mining_order')->where('user_id',$user_id)->orderBy('id', 'desc')
                ->offset($page)->limit($limit)->get();
            $lists = json_decode(json_encode($lists), true);    
            foreach($lists as $key=>$val){
                $lists[$key]['from_name'] = Currency::where('id',$val['from_name'])->value('name');
                $lists[$key]['to_name'] = Currency::where('id',$val['to_name'])->value('name');
                $lists[$key]['created_at'] = date('Y-m-d',$val['created_at']);
                if(empty($val['complete_at'])){
                    $lists[$key]['complete_at'] = date('Y-m-d',$val['created_at'] + ($val['day'] * 86400));
                }else{
                    $lists[$key]['complete_at'] = date('Y-m-d',$val['complete_at']);
                }
                if($val['status'] == '1'){
                   $lists[$key]['status'] = '已结束';
                }else{
                   $lists[$key]['status'] = '进行中';
                }   
                
                $lock_id=$val['lock_id'];
                $lock = db::table('lock_mining')->where('id',$lock_id)->first();
                $adance_redeem_falsify=$lock->adance_redeem_falsify;
                $lists[$key]['adance_redeem_falsify']=$adance_redeem_falsify;
                
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

   
    public function wallet(Request $request){
       
         $user_id = Users::getUserId();
         $id = $request->input('id', 0);
         //$from_name = $request->input('from_name', 0);
        
         $lockmining = db::table('lock_mining')->where('id',$id)->where('status',1)->first();
          if(empty($lockmining)){
           
            return $this->error('锁仓资源不存在');
         }  
         $lockmining = json_decode(json_encode($lockmining), true);
        
         $wallet = UsersWallet::where('user_id',$user_id)->where('currency',$lockmining['from_name'])->first();
         return $this->success($wallet);
    }

}
