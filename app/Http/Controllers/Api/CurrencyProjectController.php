<?php


namespace App\Http\Controllers\Api;


use App\Models\AccountLog;
use App\Models\CurrencyProjectOrder;
use App\Models\CurrencyProject;
use App\Models\Users;
use App\Models\UsersWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App;
class CurrencyProjectController extends Controller
{

    public function processOrder(){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $list = CurrencyProjectOrder::where('status',2)
            ->where('type',1)
            ->where('end_at','<',date("Y-m-d H:i:s"))
            ->take(10)->get();
        //认购单派钱
        foreach($list as $v){
            DB::beginTransaction();
            try{
                $op_wallet = UsersWallet::where('user_id',$v->u_id)
                    ->where('currency',$v->currency_id)
                    ->first();
                if(!$op_wallet){
                    throw new \Exception(trans('wallet.cbbcz'));
                }
                $result = change_wallet_balance($op_wallet,
                    2,
                    $v->coin_amount,
                    AccountLog::IEO_OPERATION,
                    'ieo订单',
                    'ieo order');
                CurrencyProjectOrder::where('id',$v->id)->update([
                    'status' => 3
                ]);
                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                continue ;
            }
        }

        $list2 = CurrencyProjectOrder::join('users_wallet','users_wallet.id','=','currency_project_order.pay_wallet_id')
            ->where('currency_project_order.status',1)->where('type',2)->where('users_wallet.change_balance','>','currency_project_order.total_price')
            ->where('end_at','<',date("Y-m-d H:i:s"))
            ->take(10)->select(['currency_project_order.*'])->get();
        // var_dump($list2);exit;
        foreach($list2 as $item){
            DB::beginTransaction();
            try{
                $wallet = UsersWallet::where("user_id", $item->u_id)
                    ->where("currency", $item->pay_currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet){
                    throw new \Exception(trans('wallet.cbbcz'));
                }
                $op_wallet = UsersWallet::where('user_id',$item->u_id)
                    ->where('currency',$item->currency_id)
                    ->first();
                if(!$op_wallet){
                    throw new \Exception(trans('wallet.cbbcz'));
                }
                //扣款
                $result = change_wallet_balance($wallet,
                    2,
                    -$item->total_price,
                    AccountLog::IEO_OPERATION,
                    'ieo订单',
                    'ieo order');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                //发币
                $result = change_wallet_balance($op_wallet,
                    2,
                    $item->coin_amount,
                    AccountLog::IEO_OPERATION,
                    'ieo订单',
                    'ieo order');

                CurrencyProjectOrder::where('id',$item->id)->update([
                    'status' => 3
                ]);
                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                continue ;
            }
        }
    }


    public function projectList(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);
//        $model = new CurrencyProject();

        $list = CurrencyProject::where('status',1)
            ->where('isdel',0)
            ->skip($limit*($page-1))->take($limit)
            ->select(['title','summary','amount','total_sell','start_at','end_at','logo','id','currency_id','pay_currency_id'])
            ->orderBy('start_at','desc')
            ->get();
//        bc_sub($project->amount,$project->total_sell)
        foreach ($list as &$item) {
            $total_sell = $item->total_sell;
            if (empty($total_sell)){
                $total_sell = 0;
            }
            if ($total_sell<=0){
                $percentage = 100;
            }else{
                $percentage = (bc_sub($item->amount,$total_sell) / $item->amount) * 100;
                if (number_format($percentage,2) == 100){ // 卖出去就是99.99
                    $percentage = '99.99';
                }
            }
            $item->percentage = number_format($percentage,0);
            $item->percentage2 = number_format(100-$percentage,0);

            $day = strtotime($item->end_at) - strtotime($item->start_at);
            $day = intval($day / 24 / 3600);
            $item->day = $day > 0 ? $day : 1;
            
            $surplus=bc_sub($item->amount,$total_sell);
             $item->surplus=number_format($surplus,0);


            $projectID=$item->id;
            //$alredySoldCnt = CurrencyProjectOrder::where('project_id',$projectID)->count();
            
            $alredySoldCnt = CurrencyProjectOrder::where('project_id',$projectID)->sum('coin_amount');
            if (empty($alredySoldCnt)){
                $alredySoldCnt=0;
            }
             $item->alredySoldCnt=$alredySoldCnt;
             
           $item->surplus=$item->amount-$alredySoldCnt;
           
            $item->surplus2=bc_mul($item->surplus,100);
            $item->surplus2=bc_div($item->surplus2,$item->amount);
            
            $progress=bc_mul($alredySoldCnt,100);
            $progress= bc_div($progress,$item->amount);

            $item->progress = number_format($progress,2);

        }
        unset($item);

//        foreach($list as &$v){
//            $v['time_status']
//        }
        return $this->success(['list' => $list]);
    }



    public function projectDetail(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $userId = Users::getUserId();
        $id = $request->get('project_id');
        $project = CurrencyProject::find($id);
        if(!$project){
            throw new \Exception(trans('currency.xbsgxmbcz'));
        }
        if($project->status != 1){
            return $this->error(trans('currency.xbsgxmztyc'));
        }
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $project->pay_currency_id)
            ->first();
        $hasMoney = $wallet->change_balance > 0 ? 1 : 0;
        if($project->min){
            $hasMoney = $wallet->change_balance > $project->min ? 1 : 0;
        }
        $project->user_has_money = 1;//$hasMoney;
        $order = CurrencyProjectOrder::where('project_id',$id)->where('type','<',3)->where('u_id',$userId)->first();
        if($order)
            $order->order_no = 1000+$order->id;
        //
        
          //$projectId=$p->id;
            $amount=$project->amount;
            //project_id
            //CurrencyProjectOrder::where('project_id',$projectId)->get('coin_amount')

            $alreadySoldCnt=DB::table('currency_project_order')->where('project_id',$id)->sum('coin_amount');
            $remainCnt=$amount-$alreadySoldCnt;
        $project->remainCnt=$remainCnt;
            

        $sell = CurrencyProjectOrder::where('project_id',$id)->where('type','=',3)->where('u_id',$userId)->first();
        return $this->success([
            'info' => $project,
            'order_info' => $order,
            'sell_order' => $sell
        ]);
    }
    public function joinLottery(Request $request){
        return $this->error('error');
        $id = $request->get('project_id');
        $amount = $request->get('amount');
        $userId = Users::getUserId();
        $project = CurrencyProject::find($id);
        if(!$project){
            return $this->error(trans('currency.xbsgxmbcz'));
        }
        if($project->status != 1){
            return $this->error(trans('currency.xbsgxmztyc'));
        }
        if($project->time_status != 2){
            return $this->error(trans('currency.xbsgxmyjs'));
        }
        if(bc_sub($project->amount,bc_add($amount,$project->total_sell,8))< 0){
            return $this->error(trans('currency.xbsgxmysq'));
        }
        // $check = CurrencyProjectOrder::where('u_id',$userId)->where('project_id',$project->id)->first();
        // if($check){
        //     return $this->error('already apply');
        // }
        //new wallet
        $payWallet = UsersWallet::where('currency',$project->pay_currency_id)->where('user_id',$userId)->first();
        if(!$payWallet){
            $payWalletId =  UsersWallet::insertGetId([
                'currency' => $project->pay_currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $payWalletId = $payWallet->id;
        }
        $wallet = UsersWallet::where('currency',$project->currency_id)->where('user_id',$userId)->first();
        if(!$wallet){
            $walletId =  UsersWallet::insertGetId([
                'currency' => $project->currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $walletId = $wallet->id;
        }
        $price = bc_mul($project->price,$amount,8);
        $model = new CurrencyProjectOrder();
        $model->u_id = $userId;
        $model->project_id = $project->id;
        $model->currency_id = $project->currency_id;
        $model->pay_currency_id = $project->pay_currency_id;
        $model->coin_amount = $amount;
        $model->price = $project->price;
        $model->total_price = $price;
        $model->created_at = date('Y-m-d H:i:s');
        $model->status = 1;
        $model->type = 2;//抽奖
        $model->end_at = $project->end_at;
        $model->wallet_id = $walletId;
        $model->pay_wallet_id = $payWalletId;
        $model->save();
        return $this->success('success');
    }

    public function buyOrder(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->get('project_id');
        $amount = $request->get('total_price');
        $userId = Users::getUserId();
        $project = CurrencyProject::find($id);

        if(!$project){
            return $this->error(trans('currency.xbsgxmbcz'));
        }
        if(!$amount){
            return $this->error(trans('common.cscw'));
        }
        if($project->status != 1){
            return $this->error(trans('currency.xbsgxmztyc'));
        }
        if($project->time_status != 3){
            return $this->error(trans('currency.xbsgxmwjs'));
        }
        if(!$project->sell_begin || strtotime($project->sell_begin) > time()){
            return $this->error(trans('currency.pswks'));
        }
        $check = CurrencyProjectOrder::where('u_id',$userId)->where('project_id',$project->id)->first();
        if($check){
            return $this->error(trans('currency.ieoxmzkcjyc'));
        }
        //new wallet
        $payWallet = UsersWallet::where('currency',$project->pay_currency_id)->where('user_id',$userId)->first();
        if(!$payWallet){
            $payWalletId =  UsersWallet::insertGetId([
                'currency' => $project->pay_currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $payWalletId = $payWallet->id;
        }
        $wallet = UsersWallet::where('currency',$project->currency_id)->where('user_id',$userId)->first();
        if(!$wallet){
            $walletId =  UsersWallet::insertGetId([
                'currency' => $project->currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $walletId = $wallet->id;
        }
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $project->pay_currency_id)
            ->lockForUpdate()
            ->first();
        if(!$wallet){
            return $this->error('wallet not found');
        }
        if($amount>$wallet->change_balance){
            // var_dump([$project->pay_currency_id,$userId]);
            return $this->error(trans('wallet.qbyebz'));
        }
        DB::beginTransaction();
        try{
            $result = change_wallet_balance($wallet,
                2,
                -$amount,
                AccountLog::IEO_OPERATION,
                'ieo订单',
                'ieo order');
            if ($result !== true) {
                throw new \Exception($result);
            }

            // $result = change_wallet_balance($op_wallet,
            //     2,
            //     $amount,
            //     AccountLog::IEO_OPERATION,
            //     'ieo order');

            $model = new CurrencyProjectOrder();
            $model->project_id = $project->id;
            $model->u_id = $userId;
            $model->currency_id = $project->currency_id;
            $model->pay_currency_id = $project->pay_currency_id;
            $model->coin_amount = null;
            $model->price = null;
            $model->total_price = $amount;
            $model->created_at = date('Y-m-d H:i:s');
            $model->status = 2;
            $model->type = 3;
            $model->end_at = $project->end_at;
            $model->wallet_id = $walletId;
            $model->pay_wallet_id = $payWalletId;
            $model->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success(trans('common.success'));

    }

    public function postOrder(Request $request){
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->get('project_id');
        $amount = $request->get('amount');
        $userId = Users::getUserId();
        $project = CurrencyProject::find($id);
        if(!$project){
            return $this->error(trans('currency.xbsgxmbcz'));
        }
        if($project->status != 1){
            return $this->error(trans('currency.xbsgxmztyc'));
        }

        if($amount<=0){
            return $this->error(trans('common.cscw'));
        }
        if($project->time_status != 2){
            return $this->error(trans('currency.xbsgxmujs'));
        }


        //221120 最小值校验
        $sub = bc_sub((string)$project->min,(string)$amount,8);
        if($sub > 0){
            return $this->error(trans('currency.zdrgjew').$project->min);
        }
        ///
        $price=$project->price;
        $amount=bc_div($amount,$price,2);
        
         $alreadySoldCnt=DB::table('currency_project_order')->where('project_id',$id)->sum('coin_amount');
        if (empty($alreadySoldCnt)){
            $alreadySoldCnt=0;
        }
        
        
        if(bc_sub($project->amount,bc_add($amount,$alreadySoldCnt,8))< 0){
            return $this->error(trans('currency.xbsgxmysq'));
        }
        //
       
        
        //new wallet
        $payWallet = UsersWallet::where('currency',$project->pay_currency_id)->where('user_id',$userId)->first();
        if(!$payWallet){
            $payWalletId =  UsersWallet::insertGetId([
                'currency' => $project->pay_currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $payWalletId = $payWallet->id;
        }
        $wallet = UsersWallet::where('currency',$project->currency_id)->where('user_id',$userId)->first();
        if(!$wallet){
            $walletId =  UsersWallet::insertGetId([
                'currency' => $project->currency_id,
                'user_id' => $userId,
                'address' => null,
                'create_time' => time()
            ]);
        }else{
            $walletId = $wallet->id;
        }

        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $project->pay_currency_id)
            ->lockForUpdate()
            ->first();
        if(!$wallet){
            return $this->error(trans('wallet.cbbcz'));
        }
        // $check = CurrencyProjectOrder::where('u_id',$userId)->where('project_id',$project->id)->first();
        // if($check){
        //     return $this->error('already apply');
        // }
        $op_wallet = UsersWallet::where('user_id',$userId)
            ->where('currency',$project->currency_id)
            ->first();
        if(!$op_wallet){
            return $this->error(trans('wallet.cbbcz'));
        }
        $price = bc_mul($project->price,$amount,8);
        if($price>$wallet->earn_balance){
            // var_dump([$project->pay_currency_id,$userId]);
            return $this->error(trans('wallet.yebz'));
        }

        DB::beginTransaction();
        try{
            $result = change_wallet_balance($wallet,
                4,
                -$price,
                AccountLog::IEO_OPERATION,
                '新币申购扣除资金',
                'Deduction of funds for new currency subscription');
            if ($result !== true) {
                throw new \Exception($result);
            }

            /*
            $result = change_wallet_lock_balance($op_wallet,
                2,
                $amount,
                AccountLog::IEO_OPERATION,
                'ieo order');*/

            $model = new CurrencyProjectOrder();
            $model->project_id = $project->id;
            $model->u_id = $userId;
            $model->currency_id = $project->currency_id;
            $model->pay_currency_id = $project->pay_currency_id;
            $model->coin_amount = $amount;
            $model->price = $project->price;
            $model->total_price = $price;
            $model->created_at = date('Y-m-d H:i:s');
            $model->status = 2;
            $model->type = 1;
            $model->end_at = $project->end_at;
            $model->wallet_id = $walletId;
            $model->pay_wallet_id = $payWalletId;
            $model->save();
            // 更新一出手
            $project->total_sell += $amount;
            $project->save();
            DB::commit();
            // 机器人推送消息
            robotSendMessage($userId,'申购'.$amount);
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success(trans('common.cg'));


    }

    public function userOrder(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $page =  $request->get('page',1);
        $limit = $request->get('limit',10);
        $lists = DB::table('currency_project_order')
            ->join('currency_project', 'currency_project.id', '=', 'currency_project_order.project_id')
            ->join('currency', 'currency.id', '=', 'currency_project.currency_id')
            ->where('currency_project_order.u_id',$user_id)
            ->orderBy('currency_project_order.id', 'desc')
            ->select('currency_project_order.created_at',
                'currency.name',
                'currency_project_order.coin_amount',
                'currency_project.sell_begin',
                'currency_project_order.passed_amount',
                'currency_project_order.status'
            )
            ->paginate($limit);

        foreach ($lists->items() as &$item) {
            $item->give_amount = 0;
            if ($item->status == 3){
                $item->give_amount = $item->coin_amount;
            }
            $item->coin_amount=number_format($item->coin_amount,2);
        }
        unset($item);

        $result = array('data' => $lists->items(), 'page' => $page, 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }

}
