<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{AccountLog, Agent, Users, UsersWalletOut, Currency, LeverTransaction, UsersWallet, AgentMoneylog,CurrencyProjectOrder,
    DzpConfigCount,RewardConf,Setting,UserLevelModel,RechargeRecord
};

class CapitalController extends Controller
{

    //充币
    public function rechargeIndex()
    {
        //法币
        $legal_currencies = Currency::where('is_legal', 1)->get();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.recharge", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    //提币
    public function withdrawIndex()
    {
        //法币
        $legal_currencies = Currency::where('is_legal', 1)->get();
        //下级代理
        $son_agents = Agent::getAllChildAgent(Agent::getAgentId());
        return view("agent.capital.withdraw", [
            'legal_currencies' => $legal_currencies,
            'son_agents' => $son_agents,
        ]);
    }

    public function rechargeList(Request $request)
    {
        
        $limit = $request->input('limit', 10);
        
        $agent = Agent::getAgent();
        $child_agents = Agent::getAllChildAgent($agent->id);
        $agents = $child_agents->pluck('id')->all();
        $child_users = Users::whereIn('agent_note_id', $agents)->get();
        $agent_id = Agent::getAgentId();
        
        $lists = RechargeRecord::whereIn('user_id', $child_users->pluck('id')->all())
        ->whereHas('user', function ($query) use ($request) {
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


            // $query->where('status', '=', '0');


            $id = $request->input('id', '');
            if (!empty($id)&&$id>0){
                $query->where('user_id', '=', $id);
            }
        })->orderBy('status', 'asc')->orderBy('id', 'desc')
            ->paginate($limit);
        
        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            return $item;
        });
        $lists->setCollection($items);
            
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
        $sum = $lists->sum('money');
        return $this->layuiData($lists, $sum);
        
        // $limit = $request->input('limit', 20);
        // $agent = Agent::getAgent();
        // $child_agents = Agent::getAllChildAgent($agent->id);
        // $agents = $child_agents->pluck('id')->all();
        // $child_users = Users::whereIn('agent_note_id', $agents)->get();
        // $agent_id = Agent::getAgentId();
        // // $node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        // // var_dump($node_users);
        // $lists = AccountLog::where('type', AccountLog::WALLET_CURRENCY_IN)
        //     ->whereIn('user_id', $child_users->pluck('id')->all())
        //     // ->whereIn('user_id', $node_users)
        //     ->where(function ($query) use ($request) {

        //         $account_number = $request->input('account_number', '');
        //         $belong_agent = $request->input('belong_agent', '');
        //         $currency_id = $request->input('currency_id', -1);

        //         $query->when($account_number != '', function ($query) use ($account_number) {
        //             $query->whereHas('user', function ($query) use ($account_number) {
        //                 $query->where('account_number', $account_number);
        //             });
        //         })->when($belong_agent != '', function ($query) use ($belong_agent) {
        //             $query->whereHas('user', function ($query) use ($belong_agent) {
        //                 $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
        //                     $query->where('username', $belong_agent);
        //                 });
        //             });
        //         })->when($currency_id > 0, function ($query) use ($currency_id) {
        //             $query->where('currency', $currency_id);
        //         });
        //     })
        //     ->orderBy('id', 'desc')
        //     ->paginate($limit);

        // $items = $lists->getCollection();
        // $items->transform(function ($item, $key) {
        //     // 设置上级代理商信息
        //     $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
        //     return $item;
        // });
        // $lists->setCollection($items);
        // return $this->layuiData($lists);
    }

    //提币
    public function withdrawList(Request $request)
    {
        $limit = $request->input('limit', 20);
        $agent = Agent::getAgent();
        $child_agents = Agent::getAllChildAgent($agent->id);
        $agents = $child_agents->pluck('id')->all();
        $child_users = Users::whereIn('agent_note_id', $agents)->get();
        $agent_id = Agent::getAgentId();
        // $node_users = Users::whereRaw("FIND_IN_SET($agent_id,`agent_path`)")->pluck('id')->all();
        $lists = UsersWalletOut::whereIn('user_id', $child_users->pluck('id')->all())
            // ->whereIn('user_id', $node_users)
            ->where(function ($query) use ($request) {

                $account_number = $request->input('account_number', '');
                $belong_agent = $request->input('belong_agent', '');
                $currency_id = $request->input('currency_id', -1);

                $query->when($account_number != '', function ($query) use ($account_number) {
                    $query->whereHas('user', function ($query) use ($account_number) {
                        $query->where('account_number', $account_number);
                    });
                })->when($belong_agent != '', function ($query) use ($belong_agent) {
                    $query->whereHas('user', function ($query) use ($belong_agent) {
                        $query->whereHas('belongAgent', function ($query) use ($belong_agent) {
                            $query->where('username', $belong_agent);
                        });
                    });
                })->when($currency_id > 0, function ($query) use ($currency_id) {
                    $query->where('currency', $currency_id);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);

        $items = $lists->getCollection();
        $items->transform(function ($item, $key) {
            // 设置上级代理商信息
            if ($item->notes == '') {
                $item->notes = '用户提币';
            }
            $item->setAttribute('belong_agent_name', $item->user->belongAgent->username ?? '');
            return $item;
        });
        $lists->setCollection($items);
        return $this->layuiData($lists);
    }

    //用户资金
    public function wallet(Request $request)
    {
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error('参数错误');
        }

        return view("agent.capital.wallet", ['user_id' => $id]);
    }

    public function wallettotalList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $user_id = $request->input('user_id', null);
        if (empty($user_id)) {
            return $this->error('参数错误');
        }

        $list = Currency::where('is_legal', 1)->orderBy('id', 'desc')->select(['id', 'name'])->paginate($limit);

        foreach ($list->items() as &$value) {
            $value->_ru = AccountLog::where('type', AccountLog::CHAIN_RECHARGE)
                ->where('user_id', $user_id)
                ->where('currency', $value->id)
                ->sum('value');

            $value->_chu = UsersWalletOut::where('status', 2)
                ->where('user_id', $user_id)
                ->where('currency', $value->id)
                ->sum('real_number');

            $value->_caution_money = LeverTransaction::where('user_id', $user_id)->whereIn('status', [0, 1, 2])->where('legal', $value->id)->sum('caution_money');
        }

        return $this->layuiData($list);
    }

    //结算 提现到账
    public function walletOut(Request $request)
    {
        $id = $request->input('id', '');

        if (!$id) {
            return $this->error('参数错误');
        }

        try {
            DB::beginTransaction();
            $agent_log = AgentMoneylog::lockForUpdate()->find($id);
            if (empty($agent_log)) {
                throw new \Exception('操作失败:信息有误');
            }
            if ($agent_log->status != 0) {
                throw new \Exception('操作失败:该账单已提现,请勿重复操作或刷新后重试');
            }
            $agent = Agent::find($agent_log->agent_id);
            if ($agent->is_admin != 1) {
                $wallet = UsersWallet::where('user_id', $agent->user_id)->where('currency', $agent_log->legal_id)->first();
                if (empty($wallet)) {
                    throw new \Exception('用户钱包不存在');
                }
                if ($agent_log->type == 1) {

                    $account_type = AccountLog::AGENT_JIE_TC_MONEY;
                    $account_info = '代理商结算头寸收益 划转到账';
                    $en_account_info = 'Agent settlement position income Transfer to account';
                } else {
                    $account_type = AccountLog::AGENT_JIE_SX_MONEY;
                    $account_info = '代理商结算手续费收益 划转到账';
                    $en_account_info = 'Transfer of agent settlement fee income to account';
                }
                $change_result = change_wallet_balance($wallet, 1, $agent_log->change, $account_type, $account_info,$en_account_info);
                if ($change_result !== true) {
                    throw new \Exception($change_result);
                }
            } else {
                throw new \Exception('超级代理商无法提现');
            }


            $agent_log->status = 1; //
            $agent_log->updated_time = time(); //

            $agent_log->save();

            DB::commit();
            return $this->success('操作成功:)');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
     public function v2_tj(Request $request)
    {
        $id = $request->input('id');
        $po = RechargeRecord::find($id);
        $user_id = $po->user_id;
        
        DB::beginTransaction();
        try {
            $po->update(['status' => 1]);
            $notes = $request->input('notes');
            if($notes) {
              $po->update(['notes' => $notes]);  
            }
            DB::update('update users set cz_count = cz_count+1 where id = ' . $user_id);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        
        
        
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
            } catch (\Exception $e) {
                DB::rollBack();
                return $this->error($e->getMessage());
            }
        } else {
            $currency = $po->currency;
            DB::beginTransaction();
            try {
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
                $result = change_wallet_balance($wallet,
                    0,//充值充到 资金账户2023-08-26
                    +$amount,
                    AccountLog::WALLET_CURRENCY_IN,
                    '充币',
                    'Deposit coins');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                $deposit_date=$po->created_at;
                $this->saveRewardGroup($user_id, $amount,$deposit_date);

                DB::commit();

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

        return $this->success("操作成功");
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
    
     public function show(Request $request)
    {
        $id = $request->input('id', '');
        if (!$id) {
            return $this->error('参数小错误');
        }
        $walletout = UsersWalletOut::find($id);

        $out = UsersWalletOut::where('currency', $walletout->currency)
            ->where('user_id', $walletout->user_id)
            ->where('status', 2)
            ->sum('real_number');
        //
        $vo=UsersWallet::where('currency', $walletout->currency)
            ->where('user_id', $walletout->user_id)->first();
        $in=$vo->cz_amount;


        $use_chain_api = Setting::getValueByKey('use_chain_api', 0);

        $authorityList=session()->get("authorityList");

        return view('agent.cashb.edit', [
            'wallet_out' => $walletout,
            'out' => $out,
            'in' => $in,

            'use_chain_api' => $use_chain_api,
            'authorityList' => $authorityList
        ]);
    }

    public function done(Request $request)
    {
        set_time_limit(0);
        $id = $request->input('id', 0);
        $method = $request->input('method', '');
        $txid =  $request->input('txid', '');
        $notes = $request->input('notes', '');
        $verificationcode = $request->input('verificationcode', '') ?? '';
        try {

            DB::beginTransaction();
            throw_if(empty($id), new \Exception('参数错误'));
            $balance_from = Setting::getValueByKey('withdraw_from_balance', 1); // 从哪个账户提币(1.法币,2.币币,3.杠杆)

            // 限制只有未操作过的提币才能进行操作
            $wallet_out = UsersWalletOut::where('status', '<=', 1)
                ->lockForUpdate()
                ->findOrFail($id);


            $number = $wallet_out->number;
            $real_number = bc_mul($wallet_out->number, bc_sub(1, bc_div($wallet_out->rate, 100)));
            // $real_number = bc_sub($number, $wallet_out->rate); // 手续费为固定
            $user_id = $wallet_out->user_id;
            $currency_model = $wallet_out->currencyCoin;
            $currency_id = $currency_model->id;

            // 查找提币的钱包(中心化的)
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $currency_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($method == 'done') {
                //确认提币
                change_wallet_lock_balance($user_wallet, 0, -$number, AccountLog::WALLETOUTDONE, '提币成功','Withdrawal successful', true);
                $use_chain_api = Setting::getValueByKey('use_chain_api', 0);

                $wallet_out->use_chain_api = $use_chain_api;
                $wallet_out->status = 2; //提币成功状态
            } else {
                change_wallet_lock_balance($user_wallet, 0, -$number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额减少','Withdrawal failed, locked balance decreased', true);
                change_wallet_balance($user_wallet, 0, $number, AccountLog::WALLETOUTBACK, '提币失败,锁定余额撤回','Withdrawal failed, locked balance withdrawn');
                $wallet_out->status = 3; //提币失败状态
            }
            $wallet_out->notes = $notes; //反馈的信息
            //$wallet_out->verificationcode = $verificationcode;
            $wallet_out->update_time = time();
            $wallet_out->save();
            //event(new WithdrawAuditEvent($wallet_out, $currency_model));
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('操作失败:' . 'File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }
    
    public function editAddress(Request $request) {
        $id = $request->input('id', 0);
        $address = $request->input('address', '');
        if(!$address) {
            return $this->error('地址不能为空');
        }
        $wallet_out = UsersWalletOut::lockForUpdate()
                ->findOrFail($id);
        $wallet_out->address = $address;
        $data = $wallet_out->save();
        if($data) {
            return $this->success('操作成功');
        }else {
            return $this->error('操作失败');
        }
    }
}
