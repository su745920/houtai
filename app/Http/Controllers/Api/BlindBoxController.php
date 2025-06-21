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
use App\Models\BlindBox;
use App\Models\MarketHour;
use App\Models\CurrencyMatch;
use App\Models\InsuranceType;
use App\Models\MicroNumbers;
use App\Models\BlindBoxOrder;
use App\Models\AccountLog;
use App\Utils\Probability;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App;

class BlindBoxController extends Controller
{
    
    
     /**
     * 获得锁仓挖矿列表
     */
    public function getOrder(Request $request){
        // Redis::set('key','value');
        // var_dump(Redis::get('key'));
        // exit;
        
        
      
        
        // $pagenNum = $request->input('page', 1);
        // $limit = $request->input('limit', 8);
        // $page=$pagenNum-1;
        // if ($page != 0) {
        //   $page = $limit * $page;
        //   $limit=$limit*$pageNum;
        // }
        // $list = BlindBox::where('status',1)
        //     ->offset($page)->limit($limit)->orderBy(\DB::raw('RAND()'))->get();
         return $this->success($res);
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
      
        $key = 'lan_type_'.$user_id;
        $lang = Cache::get($key);
        
        $currency_id =23;
        
        if(!$lang){
            $lang = 'zh_cn';
        }
        $wallet = UsersWallet::where('user_id',$user_id)->where('currency', '23')->first();
            //购买盲盒需要消耗usdt
        $xiaohao = Setting::getValueByKey('Consumptionoffunds', '');
        if($xiaohao > $wallet['change_balance']){
            return $this->error(trans('blind.zjbz'));
        }
        
        $Probability = new Probability();
        $val = Redis::get('key');
        $blind_box = json_decode($val, true);
        $result = $Probability->lotteryRaffle($blind_box);
        $blind_box_order = db::table('blind_box')->where('status',1)->where('name',$result['name'])->where('status',1)->first();
        $blind_box_order = json_decode(json_encode($blind_box_order), true);
        if(empty($blind_box_order)){
            return $this->error(trans('blind.mhzybcz'));
          
        }
        try {
            DB::beginTransaction();
            $order_data = [
                'user_id' => $user_id,
                'blind_id' => $blind_box_order['id'],
                'currency_id'=> $blind_box_order['currency_id'],
                'name' => $blind_box_order['name'],
                'num' => $blind_box_order['num'],
                'consume' =>$xiaohao
            ];
            $blind_box = BlindBoxOrder::create($order_data);
            $from_result = change_wallet_balance($wallet, 2, -$xiaohao, AccountLog::XIAOHAO_BLIND_BOX, '盲盒消耗','Blind box consumption');
            $wallet = UsersWallet::where('user_id',$user_id)->where('currency', $blind_box_order['currency_id'])->first();
            $to_result = change_wallet_balance($wallet, 2, $blind_box_order['num'], AccountLog::LOCK_MINING, '盲盒获取','Blind box acquisition');
            $blind = BlindBox::where('status',1)->where('id',$blind_box_order['id'])->select('name','currency_id')->first();
            DB::commit();
            return $this->success(trans('blind.gxnc').trans('blind.hd'). $blind_box_order['num']. $blind['currency_id']);
        } catch (\Throwable $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
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
            $lists = BlindBoxOrder::where('user_id', $user_id)
                // ->when($status <> -1, function ($query) use ($status) {
                //     $query->where('status', $status);
                // })
                // ->when($match_id <> -1, function ($query) use ($match_id) {
                //     $query->where('match_id', $match_id);
                // })
                // ->when($currency_id <> -1, function ($query) use ($currency_id) {
                //     $query->where('currency_id', $currency_id);
                // })
                ->orderBy('id', 'desc')
               ->offset($page)->limit($limit)->get();
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
         $wallet['change_balance'] = UsersWallet::where('user_id',$user_id)->where('currency','23')->value('change_balance');
         $wallet['xiaohao'] = Setting::getValueByKey('Consumptionoffunds', '');
         return $this->success($wallet);
    }


}
