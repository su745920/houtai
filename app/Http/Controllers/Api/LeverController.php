<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\{AccountLog, CurrencyMatch, LeverTransaction, LeverMultiple, Setting, TransactionComplete, TransactionIn, TransactionOut, Users, UsersWallet,Currency};
use App\Events\LeverSubmitOrderEvent;
use App\Jobs\LeverClose;
use App;
class LeverController extends Controller
{
    public function getLeverBalance(){
        $user_id=Users::getUserId();
        $legal = UsersWallet::where("user_id", $user_id)->where("currency", 23)->first();
        $lever_balance=0;
        if ($legal) {
            $lever_balance = $legal->lever_balance;
        }
        $po["lever_balance"]=$lever_balance;
        return $this->success($po);
    }
    
    public function contractFee(){
        $contract_fee= Setting::getValueByKey('contract_fee', 2);
        $po["contract_fee"]=$contract_fee;
        return $this->success($po);

    }
    
    //2023-05-09 我的-》合约订单列表
    public function myTradePage(Request $request)
    {
        $user_id = Users::getUserId();
        $legal_id = 23;
        //$currency_id = request()->input("currency_id", 0);
        $status = $request->input("status", -1);
        $limit = $request->input("limit", 10);
        $param = compact('status', 23);
        $lever_transaction = LeverTransaction::where(function ($query) use ($param) {
            extract($param);
            $status != -1 && $query->where('status', $status);
           $query->where('legal', 23);
            //$currency_id > 0 && $query->where('currency', $currency_id);
        })->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        //以下是跟单需要的 在其他模块不需要
        if (!empty($lever_transaction)){
            foreach ($lever_transaction as $order){
                 $trader_uid=$order->trader_uid;
                 if (!empty($trader_uid)&&$trader_uid>0){
                     $trader=Users::getUserId($trader_uid);
                     $order->traderNickname=$trader->nickname;
                 }

            }
        }

        return $this->success($lever_transaction);
    }
    
    /**
     * 取交易信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deal()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        if (empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('lever.cscw'));
        }


        $lever_share_limit = [
            'min' => 1,
            'max' => 0,
        ];
        $curreny_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if ($curreny_match) {
            $lever_share_limit = array_merge($lever_share_limit, [
                'min' => $curreny_match->lever_min_share,
                'max' => $curreny_match->lever_max_share,
            ]);
        }
        $my_transaction = LeverTransaction::with('user')
            ->orderBy('id', 'desc')
            ->where("user_id", $user_id)
            ->where("status", LeverTransaction::TRANSACTION)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->orderBy("id", "desc")
            ->take(10)
            ->get();
            
        $last_price = LeverTransaction::getLastPrice($legal_id, $currency_id);
        $user_lever = 0;
        $all_levers = 0;
        if (!empty($user_id)) {
            $legal = UsersWallet::where("user_id", $user_id)->where("currency", $legal_id)->first();
            if ($legal) {
                $user_lever = $legal->lever_balance;
            }
            $all_levers = LeverTransaction::where("legal", $legal_id)
                ->where("currency", $currency_id)
                ->where("user_id", $user_id)
                ->where("status", LeverTransaction::TRANSACTION)
                ->selectRaw('sum(`number` * `price`) as `all_levers`')
                ->value('all_levers');
            $all_levers || $all_levers = 0;
        }
        //$match_transaction = $this->getLastMathTransaction($legal_id, $currency_id);
        $lever_transaction = $this->getLastLeverTransaction($legal_id, $currency_id);
        $ustd_price = 0;
        $last = TransactionComplete::orderBy('id', 'desc')
            ->where("currency", $legal_id)
            ->where("legal", 3)
            ->first();
        if (!empty($last)) {
            $ustd_price = $last->price;
        }
        if ($legal_id == 3) {
            $ustd_price = 1;
        }
        return $this->success([
            "lever_transaction" => $lever_transaction,
            "my_transaction" => $my_transaction,
            "lever_share_limit" => $lever_share_limit,
            "multiple" => LeverTransaction::leverMultiple(),
            "last_price" => $last_price,
            "user_lever" => $user_lever,
            "all_levers" => $all_levers,
            "ustd_price" => $ustd_price,
            "ExRate" => Setting::getValueByKey('USDTRate', 6.88),
            "contract_fee"=>Setting::getValueByKey('contract_fee', 2)
        ]);
    }

    /**
     * 交易列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dealAll()
    {
        $user_id = Users::getUserId();
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $limit = request()->input("limit", 10);
        $page = request()->input("page", 1);
        if (empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('lever.cscw'));
        }
        $lever_transaction = LeverTransaction::with('user')
            ->orderBy('id', 'desc')
            ->where("user_id", $user_id)
            ->where("status", LeverTransaction::TRANSACTION)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->paginate($limit);
        $user_wallet = UsersWallet::where('currency', $legal_id)->where('user_id', $user_id)->first();
        $balance = $user_wallet ? $user_wallet->lever_balance : 0;
        //取盈亏总额
        list(
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all
        ) = LeverTransaction::getUserProfit($user_id, $legal_id);
        //取该交易对盈亏总额
        list(
            'caution_money_total' => $caution_money,
            'origin_caution_money_total' => $origin_caution_money,
            'profits_total' => $profits
        ) = LeverTransaction::getUserProfit($user_id, $legal_id, $currency_id);
        $total_all_money = bc_add($caution_money_all, $balance);
        $hazard_rate = LeverTransaction::getWalletHazardRate($user_wallet);
        $data = [
            'balance' => $balance,
            'hazard_rate' => $hazard_rate,
            'caution_money_total' => $caution_money_all,
            'origin_caution_money_total' => $origin_caution_money_all,
            'profits_total' => $profits_all,
            'caution_money' => $caution_money,
            'origin_caution_money' => $origin_caution_money,
            'profits' => $profits,
            'order' => $lever_transaction,
        ];
        return $this->success($data);
    }

    /**
     * 我的交易
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTrade()
    {
        $user_id = Users::getUserId();
        $legal_id = request()->input("legal_id", 0);
        $currency_id = request()->input("currency_id", 0);
        $status = request()->input("status", -1);
        $limit = request()->input("limit", 10);
        $param = compact('status', 'legal_id', 'currency_id');
        $lever_transaction = LeverTransaction::where(function ($query) use ($param) {
            extract($param);
            $status != -1 && $query->where('status', $status);
            $legal_id > 0 && $query->where('legal', $legal_id);
            $currency_id > 0 && $query->where('currency', $currency_id);
        })->where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->success($lever_transaction);
    }

    /**
     * 提交杆杠交易
     *
     * @return void
     */
    public function submit()
    {
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        $share = request()->input("share");
        $multiple = request()->input("multiple");
        $u= request()->input("u");
        $payU=$u/$multiple;

        $type = request()->input("type", "1");
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $status = request()->input('status', LeverTransaction::TRANSACTION); //默认是市价交易,为0则是挂单交易
        $target_price = request()->input('target_price', 0); //目标价格
        $password = request()->input('password', ''); //支付密码
        
        $target_profit_price = request()->input('target_profit_price', 0); //止盈
        $stop_loss_price = request()->input('stop_loss_price', 0); //止损
        
        $now = time();
        $user_lever = 0;

        if (empty($legal_id) || empty($currency_id) || empty($share) || empty($multiple)) {
            return $this->error(trans('lever.qscshczcw'));
        }
        $currency_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$currency_match) {
            return $this->error(trans('lever.zdjydbcz'));
        }
        if ($currency_match->open_lever != 1) {
            return $this->error(trans('lever.nwktbjyddjygn'));
        }
        //手数判断:大于0的整数,且在区间范围内
        //if ($share != intval($share) || !is_numeric($share) || $share <= 0) {
        if (!is_numeric($share) || $share <= 0) {
            return $this->error(trans('lever.ssbxdydzs'));
        }
        //if (bc_comp($currency_match->lever_min_share, $share) > 0) {
        //    return $this->error(trans('lever.ssbndy') . $currency_match->lever_min_share);
        //}
        if (bc_comp($currency_match->lever_max_share, $share) < 0 && bc_comp($currency_match->lever_max_share, '0') > 0) {
            return $this->error(trans('lever.ssbngy'). $currency_match->lever_max_share);
        }        
        //倍数判断  1-125倍里面任意一个 不是某几个 而是1-125倍任意一个 2023-04-24
        // $multiples = LeverMultiple::where("type", 1)->pluck('value')->all();
        // if (!in_array($multiple, $multiples)) {
        //     return $this->error(trans('lever.xzbsbzxtfw'));
        // }
        //$lever_min_share->lever_max_share
        $exist_close_trade = LeverTransaction::where('user_id', $user_id)->where('status', LeverTransaction::CLOSING)->count();
        if ($exist_close_trade > 0) {
            return $this->error(trans('lever.nyzapczdjy'));
        }
        if (!in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::TRANSACTION])) {
            return $this->error(trans('lever.jylxcw'));
        }
        if ($status == LeverTransaction::ENTRUST) {
            $open_lever_entrust = Setting::getValueByKey('open_lever_entrust', 0);
            if ($open_lever_entrust <= 0) {
                return $this->error(trans('lever.ggnzwkf'));
            }
        }
        //判断是否委托交易 (限价交易)
        if ($status == LeverTransaction::ENTRUST && $target_price <= 0) {
            return $this->error(trans('lever.xjjyjgbxd'));
        }
        $overnight = $currency_match->overnight ?? 0;
        //优先从行情取最新价格
        $last_price = LeverTransaction::getLastPrice($legal_id, $currency_id);
        if (bc_comp_zero($last_price) <= 0) {
            return $this->error(trans('lever.dqmyhq'));
        }
        //挂单委托(限价交易)价格取用户设置的
        if ($status == LeverTransaction::ENTRUST) {
            if ($type == LeverTransaction::SELL && $target_price <= $last_price) {
                return $this->error(trans('lever.xjjymcbn'));
            } elseif ($type == LeverTransaction::BUY && $target_price >= $last_price) {
                return $this->error(trans('lever.xjjymcbngy'));
            }
            $origin_price = $target_price;
        } else {
            $origin_price = $last_price;
        }
        //交易手数转换
        $lever_share_num = $currency_match->lever_share_num ?? 1;
        $num = bc_mul($share, $lever_share_num);
        //点差率
        $spread = $currency_match->spread;
        $spread_price = bc_div(bc_mul($origin_price, $spread), 100);
        $type == LeverTransaction::SELL && $spread_price = bc_mul(-1, $spread_price); //买入应加上点差,卖出就减去点差
        $fact_price = bc_add($origin_price, $spread_price); //收取点差之后的实际价格
        $all_money = bc_mul($fact_price, $num, 5);
        //计算手续费
        $contract_fee=Setting::getValueByKey("contract_fee");
        //$lever_trade_fee_rate = bc_div($currency_match->lever_trade_fee ?? 0, 100);
        $lever_trade_fee_rate = bc_div($contract_fee ?? 0, 100);



        DB::beginTransaction();
        try {
            $legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", $legal_id)
                ->lockForUpdate()
                ->first();
            if (!$legal) {
                throw new \Exception(trans('lever.qbwzd'));
            }
            $user_lever = $legal->lever_balance;

            //$caution_money = bc_div($all_money, $multiple); //保证金
            $caution_money = bc_div($u, $multiple); //保证金

            //$trade_fee = bc_mul($all_money, $lever_trade_fee_rate);
            $trade_fee = bc_mul($caution_money, $lever_trade_fee_rate);//应该是保证金*手续费/100 保证金是原始金额/杠杆倍数

            $shoud_deduct = bc_add($caution_money, $trade_fee); //保证金+手续费
            if (bc_comp($user_lever, $shoud_deduct) < 0) {
                throw new \Exception($currency_match->legal_name . trans('lever.yebz') . $shoud_deduct . '('. trans('lever.sxf').':' . $trade_fee . ')');
            }
            $lever_transaction = new LeverTransaction();
            $lever_transaction->user_id = $user_id;
            $lever_transaction->type = $type;
            $lever_transaction->overnight = $overnight;
            $lever_transaction->origin_price = $origin_price;
            $lever_transaction->price = $fact_price;
            $lever_transaction->update_price = $last_price;
            $lever_transaction->share = $share;
            //$lever_transaction->target_profit_price = $target_profit_price;
            //$lever_transaction->stop_loss_price = $stop_loss_price;
            
            //止盈止损
            if (!empty($target_profit_price)&&$target_profit_price>0){
                $lever_transaction->target_profit_price = $target_profit_price;
            }
            if (!empty($stop_loss_price)&&$stop_loss_price>0) {
                $lever_transaction->stop_loss_price = $stop_loss_price;
            }
            
            
            $lever_transaction->number = $num;
            $lever_transaction->origin_caution_money = $caution_money;
            $lever_transaction->caution_money = $caution_money;
            $lever_transaction->currency = $currency_id;
            $lever_transaction->legal = $legal_id;
            $lever_transaction->multiple = $multiple;
            $lever_transaction->trade_fee = $trade_fee;
            $lever_transaction->transaction_time = $now;
            $lever_transaction->create_time = $now;
            $lever_transaction->status = $status;

            //追加用户的代理商关系
            $user = Users::find($user_id);
            $lever_transaction->agent_path = $user->agent_path;

            $result = $lever_transaction->save();
            if (!$result) {
                throw new \Exception(trans('lever.tjsb'));
            }
            //扣除保证金
            $result = change_wallet_balance(
                $legal,
                2, //合约账户
                -$caution_money,
                AccountLog::LEVER_TRANSACTION,
                '提交' . $currency_match->symbol . '杠杆交易,价格' . $fact_price . ',扣除保证金',
                'Submit ' . $currency_match->symbol . ' leveraged trading, price ' . $fact_price . ',Deducting the deposit',
                false,
                0,
                0,
                serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'multiple' => $multiple,
                ])
            );
            if ($result !== true) {
                throw new \Exception(trans('lever.kcbzjsb').':' . $result);
            }
            //扣除手续费
            $result = change_wallet_balance(
                $legal,
                2, //合约账户
                -$trade_fee,
                AccountLog::LEVER_TRANSACTION_FEE,
                '提交' . $currency_match->symbol . '杠杆交易,扣除手续费',
                'Submit ' . $currency_match->symbol . ' leveraged trading, deducting transaction fees',
                false,
                0,
                0,
                serialize([
                    'trade_id' => $lever_transaction->id,
                    'all_money' => $all_money,
                    'lever_trade_fee_rate' => $lever_trade_fee_rate,
                ])
            );
            if ($result !== true) {
                throw new \Exception(trans('lever.kcsxfsb').':' . $result);
            }
            DB::commit();
            //推荐奖:手续费结算
            event(new LeverSubmitOrderEvent($lever_transaction));
            return $this->success(trans('lever.tjcg'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 设置止盈止亏
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStopPrice()
    {
        $user_set_stopprice = Setting::getValueByKey('user_set_stopprice', 0);
        if (!$user_set_stopprice) {
            return $this->error(trans('lever.cgnzwkf'));
        }
        $id = request()->input('id', 0);
        $user_id = Users::getUserId();
        $target_profit_price = request()->input('target_profit_price', 0);
        $stop_loss_price = request()->input('stop_loss_price', 0);
        if ($target_profit_price <= 0 || $stop_loss_price <= 0) {
            return $this->error(trans('lever.zyzxjg'));
        }
        $lever_transaction = LeverTransaction::where('user_id', $user_id)
            ->where('status', LeverTransaction::TRANSACTION)
            ->find($id);
        if (!$lever_transaction) {
            return $this->error(trans('lever.zbdgbjy'));
        }
        if ($lever_transaction->type == 1) {
            //买入
            if ($target_profit_price <= $lever_transaction->price || $target_profit_price <= $lever_transaction->update_price) {
                return $this->error(trans('lever.mrzy'));
            }
            if ($stop_loss_price >= $lever_transaction->price || $stop_loss_price >= $lever_transaction->update_price) {
                return $this->error(trans('lever.mrzk'));
            }
        } else {
            //卖出
            if ($target_profit_price >= $lever_transaction->price || $target_profit_price >= $lever_transaction->update_price) {
                return $this->error(trans('lever.mczy'));
            }
            if ($stop_loss_price <= $lever_transaction->price || $stop_loss_price <= $lever_transaction->update_price) {
                return $this->error(trans('lever.mczk'));
            }
        }
        $target_profit_price > 0 && $lever_transaction->target_profit_price = $target_profit_price;
        $stop_loss_price > 0 && $lever_transaction->stop_loss_price = $stop_loss_price;
        $result = $lever_transaction->save();
        return $result ? $this->success(trans('lever.szcg')) : $this->error(trans('lever.szsb'));
    }

    /**
     * 平仓
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function close()
    {
        $user_id = Users::getUserId();
        $id = request()->input("id");
        if (empty($id)) {
            return $this->error(trans('lever.cscw'));
        }
        DB::beginTransaction();
        try {
            $lever_transaction = LeverTransaction::lockForupdate()->find($id);
            if (empty($lever_transaction)) {
                throw new \Exception(trans('lever.sjwzd'));
            }
            if ($lever_transaction->user_id != $user_id) {
                throw new \Exception(trans('lever.wqcz'));
            }
            if ($lever_transaction->status != LeverTransaction::TRANSACTION) {
                throw new \Exception(trans('lever.jyztyc'));
            }
            $return = LeverTransaction::leverClose($lever_transaction, 1);
            if (!$return) {
                throw new \Exception(trans('lever.pcsb'));
            }
            DB::commit();
            return $this->success(trans('lever.czcg'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 批量平仓(按买卖方向)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchCloseByType(Request $request)
    {
        $user_id = Users::getUserId();
        $legal_id = $request->input('legal_id', 0);
        $currency_id = $request->input('currency_id', 0);
        $type = $request->input('type', 0); //0.所有,1.买入(做多),2.卖出(做空)
        if (!in_array($type, [0, 1, 2])) {
            return $this->error(trans('lever.mrfxcccw'));
        }
        $lever = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where('user_id', $user_id)
            ->where(function ($query) use ($type, $legal_id, $currency_id) {
                !empty($legal_id) && $query->where('legal', $legal_id);
                !empty($currency_id) && $query->where('currency', $currency_id);
                !empty($type) && $query->where('type', $type);
            })->get();
        $task_list = $lever->pluck('id')->all();
        $result = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->whereIn('id', $task_list)
            ->update([
                'closed_type' => 1,
                'status' => LeverTransaction::CLOSING,
                'handle_time' => microtime(true),
            ]);
        if ($result > 0) {
            LeverClose::dispatch($task_list, true)->onQueue('lever:close');
        }
        return $result > 0 ? $this->success(trans('lever.tjcgddsh')) : $this->error(trans('lever.wzdpcdjy'));
    }

    /**
     * 批量平仓(按盈亏)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchCloseByProfit(Request $request)
    {
        $user_id = Users::getUserId();
        $type = $request->input('type'); //0.所有,1.盈,2.亏
        $lever = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->where('user_id', $user_id)
            ->get();
        switch ($type) {
            case 1:
                $lever = $lever->where('profits', '>', 0);
                break;
            case 2:
                $lever = $lever->where('profits', '<', 0);
                break;
            default:
        }
        $task_list = $lever->pluck('id')->all();
        $result = LeverTransaction::where('status', LeverTransaction::TRANSACTION)
            ->whereIn('id', $task_list)
            ->update([
                'closed_type' => 1,
                'status' => LeverTransaction::CLOSING,
                'handle_time' => microtime(true),
            ]);
        if ($result > 0) {
            LeverClose::dispatch($task_list, true)->onQueue('lever:close');
        }
        return $result > 0 ? $this->success('提交成功,请等待系统处理') : $this->error('未找到需要平仓的交易');
    }

    /**
     * 取最近几条撮合交易
     *
     * @param integer $legal_id 法币id
     * @param integer $currency_id 交易币id
     * @param integer $limit 限制条数,默认5
     * @return array
     */
    public function getLastMathTransaction($legal_id, $currency_id, $limit = 5)
    {
        $in = TransactionIn::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'desc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit($limit)
            ->get();
        $out = TransactionOut::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'asc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit($limit)
            ->get()
            ->sortByDesc('price')
            ->values();
        return [
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * 取最近几条杠杆交易
     *
     * @param integer $legal_id 法币id
     * @param integer $currency_id 交易币id
     * @param integer $limit 限制条数,默认5
     * @return array
     */
    public function getLastLeverTransaction($legal_id, $currency_id, $limit = 5)
    {
        $in = LeverTransaction::with('user')
            ->where('legal', $legal_id)
            ->where('currency', $currency_id)
            ->where('type', LeverTransaction::BUY)
            ->where('status', LeverTransaction::TRANSACTION)
            ->orderBy('price', 'desc')
            ->limit($limit)
            ->get();
        $out = LeverTransaction::with('user')
            ->where('legal', $legal_id)
            ->where('currency', $currency_id)
            ->where('type', LeverTransaction::SELL)
            ->where('status', LeverTransaction::TRANSACTION)
            ->orderBy('price', 'asc')
            ->limit($limit)
            ->get()
            ->sortByDesc('price')
            ->values();
        return [
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * 取消挂单(撤单)
     *
     * @return boolean
     */
    public function cancelTrade(Request $request)
    {
        $user_id = Users::getUserId();
        $id = $request->input('id');
        try {
            //退手续费和保证金
            DB::transaction(function () use ($user_id, $id) {
                $lever_trade = LeverTransaction::where('user_id', $user_id)
                    ->where('status', LeverTransaction::ENTRUST)
                    ->lockForUpdate()
                    ->find($id);
                if (!$lever_trade) {
                    throw new \Exception(trans('lever.jybczhycdqsxhcs'));
                }
                $legal_id = $lever_trade->legal;
                $refund_trade_fee = $lever_trade->trade_fee;
                $refund_caution_money = $lever_trade->caution_money;
                $legal_wallet = UsersWallet::where('user_id', $user_id)
                    ->where('currency', $legal_id)
                    ->first();
                if (!$legal_wallet) {
                    throw new \Exception(trans('lever.cdsbyhqbbcz'));
                }
                $result = change_wallet_balance(
                    $legal_wallet,
                    2, //3, maxCore 
                    $refund_trade_fee,
                    AccountLog::LEVER_TRANSACTION_FEE_CANCEL,
                    '杠杆' . $lever_trade->type_name . '委托撤单,退回手续费',
                    'Lever' . $lever_trade->type_name . ' commission cancellation and refund of handling fees',
                    false,
                    0,
                    0,
                    '',
                    true
                );
                if ($result !== true) {
                    throw new \Exception(trans('lever.cdsb'). $result);
                }
                $result = change_wallet_balance(
                    $legal_wallet,
                    2, //3, maxCore 
                    $refund_caution_money,
                    AccountLog::LEVER_TRANSACTIO_CANCEL,
                    '杠杆' . $lever_trade->type_name . '委托撤单,退回保证金',
                    'Lever ' . $lever_trade->type_name . ' entrusting cancellation and refunding the deposit',
                    false,
                    0,
                    0,
                    '',
                    true
                );
                if ($result !== true) {
                    throw new \Exception(trans('lever.cdsb') . $result);
                }
                $lever_trade->status = LeverTransaction::CANCEL;
                $lever_trade->complete_time = time();
                $result = $lever_trade->save();
                if (!$result) {
                    throw new \Exception(trans('lever.cdsbbgztsb'));
                }
                // $lever_trades = collect([$lever_trade]);
                // LeverTransaction::pushDeletedTrade($lever_trades);
            });
            return $this->success(trans('lever.cdcg'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
    // 测试
    public function getList() {
    //   $result = robotSendMessage(15875,'注册');
    //      return $this->success($result);
    }
}
