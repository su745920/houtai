<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\{AccountLog, CurrencyMatch, LeverTransaction, LeverMultiple, Setting, TransactionComplete, TransactionIn, TransactionOut, Users, UsersWallet,CurrencyQuotation};
use App\Events\LeverSubmitOrderEvent;
use App\Jobs\LeverClose;
use App;
class LeverV2Controller extends Controller
{
    public function getMatchInfo(){
        $currency_id = request()->input("currency_id");
        $currency_match = CurrencyMatch::where('legal_id', 23)
            ->where('currency_id', $currency_id)
            ->first();
        $data["matchInfo"]=$currency_match;
        return $this->success($data);
    }
    /**
     * 提交杆杠交易 使用合约余额百分比
     *
     * @return void
     */
    public function submit()
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
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        $share = request()->input("share");
        $multiple = request()->input("multiple");
        $u= request()->input("u");

        $type = request()->input("type", "1");
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $status = request()->input('status', LeverTransaction::TRANSACTION); //默认是市价交易,为0则是挂单交易
        $target_price = request()->input('target_price', 0); //目标价格
        $password = request()->input('password', ''); //支付密码
        $warehouse_type = request()->input('warehouse_type',0);

        $target_profit_price = request()->input('target_profit_price', 0); //止盈
        $stop_loss_price = request()->input('stop_loss_price', 0); //止损
        $currency_name = request()->input('currency_name','');

        $now = time();
        $user_lever = 0;
        if (empty($legal_id) || empty($currency_id) || empty($share) || empty($multiple)) {
            return $this->error(trans('lever.qscshczcw'));
        }
        // 判断是否开启了实名认证校验
        if(Setting::getValueByKey("is_open_transaction",0) === 1) {
            // 判断是否高级实名认证
            $advanced_review_status = 0;
            $real_data = DB::table('user_real')->where('user_id',$user_id)->orderBy("id","desc")
                ->first();
            if (!empty($real_data)){
                if ($real_data->advanced_user == 2){
                    $advanced_review_status = 2;
                }
            }
            if($advanced_review_status !== 2) {
                return $this->error(trans('login.qsmrz'));
            }
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
            //return $this->error(trans('lever.ssbngy'). $currency_match->lever_max_share);
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
        
         // 判断是否休市
        $is_lock = CurrencyQuotation::getCurrencyQuotationIsLock($currency_id,$legal_id);
        if($is_lock) {
            return $this->error(trans('common.stop'));
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

        $tx_amount=$u;



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
            //$caution_money = bc_div($u, $multiple); //保证金
            $caution_money = $u;
            //$trade_fee = bc_mul($all_money, $lever_trade_fee_rate);
            $trade_fee = bc_mul($caution_money, $lever_trade_fee_rate);//应该是保证金*手续费/100 保证金是原始金额/杠杆倍数
            //
            //增加上级返佣
            if ($trade_fee>0){
                $user=Users::getById($user_id);
                $userName=$user->account_number;
                $parent_id=$user->parent_id;
                if (!empty($parent_id)&&$parent_id>0){
                    $parentPO=Users::getById($parent_id);
                    $reward_username=$parentPO->account_number;
                    $invite_ratio= Setting::getValueByKey("invite_ratio");
                    $invite_ratio=0.01*$invite_ratio;

                    $fanyong=$trade_fee*$invite_ratio;

                    $parentWallet = UsersWallet::where('currency',23)
                        ->where('user_id', $parent_id)
                        ->first();
                    change_wallet_balance2024($parentWallet, 0, $fanyong, AccountLog::INVITATION_TO_RETURN,
                        '邀请手续费返佣' . $fanyong.'USDT', 'Invitation fee rebate ' . $fanyong.'USDT',false, $userName, $extra_sign = 0, ''.$reward_username,false,false,$tx_amount);

                }
            }


            //

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
            // $lever_transaction->share = $share; 
             $lever_transaction->share = bc_div($u,$last_price,8); // 手数的计算方式应该是保证金÷开仓价=手数

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
            $lever_transaction->warehouse_type = $warehouse_type;

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
                'Submit ' . $currency_match->symbol . ' Leveraged trading, price ' . $fact_price . ',Deducting the deposit',
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
                'Submit ' . $currency_match->symbol . ' Leveraged trading, deducting transaction fees',
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
            // 机器人推送消息
           robotSendMessage($user_id,'永续合约'.$currency_name.' 金额：'.$u);
            //推荐奖:手续费结算
            event(new LeverSubmitOrderEvent($lever_transaction));
            return $this->success(trans('lever.tjcg'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

}