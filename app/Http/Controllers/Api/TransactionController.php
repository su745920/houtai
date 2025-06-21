<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\{CurrencyMatch, CurrencyQuotation, AccountLog, Currency, Token, Transaction, TransactionComplete, TransactionIn, TransactionInDel, TransactionOut, TransactionOutDel, Users, UsersWallet,Setting};
use Illuminate\Support\Facades\Cache;
use App;

class TransactionController extends Controller
{
    
 
    public function out_2024()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $price = request()->input("price");
        $num = request()->input("num");
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $type = request()->input("mode");
        if (empty($type)){
            $type = request()->input("type");//1 市价. 2 限价
        }

        if (empty($user_id) || empty($price) || empty($num) || empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $currency_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$currency_match) {
            return $this->error(trans('transaction.zdjydbcz'));
        }
        if ($currency_match->open_transaction != 1) {
            return $this->error(trans('transaction.nmyktgjyddjygn'));
        }
        $exchange_rate = $currency_match->exchange_rate;
        $quantity = bc_div(bc_mul($num, $exchange_rate), 100); //交易利率
        $real_quantity = $num + $quantity;
        $has_num = 0;

        $user = Users::find($user_id);
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        $legal = Currency::where("is_display", 1)
            ->where("id", $legal_id)
            ->where("is_legal", 1)
            ->first();
        $currency = Currency::where("is_display", 1)
            ->where("id", $currency_id)
            ->first();
        if (empty($user) || empty($legal) || empty($currency)) {
            return $this->error(trans('transaction.sjwzd'));
        }


        try {
            DB::beginTransaction();
            $user_currency = UsersWallet::where("user_id", $user_id)
                ->where("currency", $currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($user_currency)) {
                throw new \Exception(trans('transaction.qxtjqb'));
            }
            if (bc_comp($price, '0') <= 0 || bc_comp($num, '0') <= 0) {
                throw new \Exception(trans('transaction.jeslbxdy'));
            }
            // if (bc_comp($user_currency->change_balance, $real_quantity) < 0) {
            //     $ndyebz = trans('transaction.ndyebzqb');
            //     throw new \Exception("{$ndyebz}{$exchange_rate}%({$quantity})");
            // }
            if (bc_comp($user_currency->lock_change_balance, '0') < 0) {
                throw new \Exception(trans('transaction.nddjzjycjzgm'));
            }

            //挂卖先扣手续费
            // $result = change_wallet_balance($user_currency, 2, -$quantity, AccountLog::MATCH_TRANSACTION_SELL_FEE, '挂卖扣除手续费,挂卖数量:' . $num . ',费率:' . $exchange_rate . '%',10);
            // if ($result !== true) {
            //     throw new \Exception($result);
            // }
            //查找价格高于等于当前卖出价格的所有买入委托
            // $in = TransactionIn::where("price", ">=", $price)
            //     ->where("currency", $currency_id)
            //     ->where("legal", $legal_id)
            //     ->where("number", ">", "0")
            //     ->orderBy('price', 'desc')
            //     ->orderBy('id', 'asc')
            //     ->lockForUpdate()
            //     ->get();
            // //dd($in);
            // if (count($in) > 0) {
            //     foreach ($in as $i) {
            //         if (bc_comp($has_num, $num) < 0) {
            //             $shengyu_num = bc_sub($num, $has_num);
            //             $this_num = 0;
            //             if (bc_comp($i->number, $shengyu_num) > 0) {
            //                 $this_num = $shengyu_num;
            //             } else {
            //                 $this_num = $i->number;
            //             }
            //             $has_num = bc_add($has_num, $this_num);
            //             if (bc_comp($this_num, '0') > 0) {
            //                 TransactionOut::transaction($i, $this_num, $user, $user_currency, $legal_id, $currency_id);
            //             }
            //         } else {
            //             break;
            //         }
            //     }
            // }

            $num = bcadd($num, 0, 5);

            //if($type == 1){//市价
            if(true){//市价
                $has_num = $num;


                $market_limit=request()->input("market_limit");
                $symbol=\request()->input("symbol");

                //TransactionIn::transactions($type,$price, $num, $user, $legal_id, $currency_id);//这里是真正的购买
                //TransactionIn::transactionsBinaceFn($symbol,$market_limit,$price, $num, $user, $legal_id, $currency_id);//这里是真正的购买
                TransactionOut::transactionBinaceOutFn($symbol,$market_limit,$price, $num, $user, $user_currency, $legal_id, $currency_id);
            }

            $num = bc_sub($num, $has_num,5);
            if (bc_comp($num, '0') > 0) {
                $out = new TransactionOut();
                $out->type = $type;
                $out->user_id = $user_id;
                $out->price = $price;
                $out->number = $num;
                $out->currency = $currency_id;
                $out->legal = $legal_id;
                $out->is_active = 1;
                $out->create_time = time();
                $out->rate = $exchange_rate;
                $out->save();


                //提交卖出记录扣除交易币
                $result = change_wallet_balance($user_currency, 1, -$num, AccountLog::TRANSACTIONOUT_SUBMIT_REDUCE, '提交挂卖' . $currency_match->symbol . '扣除','Submit for sale ' . $currency_match->symbol . ' deduction');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                //提交卖出记录(增加冻结)
                $result = change_wallet_balance($user_currency, 1, $num, AccountLog::TRANSACTIONOUT_SUBMIT_REDUCE, '提交挂卖' . $currency_match->symbol . '冻结','Submit for sale ' . $currency_match->symbol . ' frozen',true);
                if ($result !== true) {
                    throw new \Exception($result);
                }
            }
            if ($currency_match->market_from != 2) {
                Transaction::pushNews($currency_id, $legal_id);
            }
            DB::commit();
            return $this->success(trans('transaction.czcg'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    
    public function in_v2024()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $price = request()->input("price");
        $num = request()->input("num");
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $type = request()->input("mode");
        if (empty($type)){
            $type = request()->input("market_limit");//0 市价. 1 限价
        }

        if (empty($user_id) || empty($price) || empty($num) || empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('transaction.cscw'));
        }



        $currency_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$currency_match) {
            return $this->error(trans('transaction.zdjybcz'));
        }


        if ($currency_match->open_transaction != 1) {
            return $this->error(trans('transaction.nhmyktgjyddjygn'));
        }

        $has_num = 0;
        $legal = Currency::where("is_display", 1)
            ->where("id", $legal_id)
            ->where("is_legal", 1)
            ->first();
        $currency = Currency::where("is_display", 1)
            ->where("id", $currency_id)
            ->first();
        $user = Users::find($user_id);

        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }

        if (empty($user) || empty($legal) || empty($currency)) {
            return $this->error(trans('transaction.sjwzd'));
        }

        if (bc_comp($price, '0') <= 0 || bc_comp($num, '0') <= 0) {
            return $this->error(trans('transaction.jghslbxdy'));
        }

        //使用JAVA进行撮合交易
        /*$use_java_match_trade = config('app.use_java_match_trade', 0);
        if ($use_java_match_trade) {
            $java_match_url = config('app.java_match_url', '');
            $request_client = new Client();
            var_dump($java_match_url);die;
            $response = $request_client->post($java_match_url . '/api/transaction/in', [
                'headers' => [
                    'Authorization' => Token::getToken(),
                ],
                'form_params' => [
                    'legal_id' => $legal_id,
                    'currency_id' => $currency_id,
                    'price' => $price,
                    'num' => $num,
                    'type' => 1,
                ],
            ]);
            $result = $response->getBody()->getContents();
            $result = json_decode($result);
            if (!isset($result->type) || $result->type != 'ok') {
                return $this->error($result->message);
            }
            DB::commit();
            return $this->success("操作成功");
        }*/

        try {
            DB::beginTransaction();
            //买方法币钱包
            $user_legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", $legal_id)
                ->lockForUpdate()
                ->first();
            $all_balance = bc_mul($price, $num);

            if (bc_comp($user_legal->change_balance, $all_balance) < 0) {
                throw new \Exception(trans('transaction.yebz'));
            }

            //return $this->error("AAAAA");

            //查找所有价格小于等于当前价格的卖出委托
            // $out = TransactionOut::where("price", "<=", $price)
            //     ->where("number", ">", "0")
            //     ->where("currency", $currency_id)
            //     ->where("legal", $legal_id)
            //     ->lockForUpdate()
            //     ->orderBy('price', 'asc')
            //     ->orderBy('id', 'asc')
            //     ->get();

            // if (count($out) > 0) {
            //     foreach ($out as $o) {
            //         if (bc_comp($has_num, $num) < 0) {
            //             $shengyu_num = bc_sub($num, $has_num);
            //             $this_num = 0;
            //             if (bc_comp($o->number, $shengyu_num) > 0) {
            //                 $this_num = $shengyu_num;
            //             } else {
            //                 $this_num = $o->number;
            //             }
            //             $has_num = bc_add($has_num, $this_num);
            //             if (bc_comp($this_num, '0') > 0) {
            //                 TransactionIn::transaction($o, $this_num, $user, $legal_id, $currency_id);
            //             }
            //         } else {
            //             break;
            //         }
            //     }
            // }

            //if($type == 1){//市价
            if(true){//市价

                $has_num = $num;
                $market_limit=request()->input("market_limit");
                $symbol=\request()->input("symbol");

                //TransactionIn::transactions($type,$price, $num, $user, $legal_id, $currency_id);//这里是真正的购买
                TransactionIn::transactionsBinaceFn($symbol,$market_limit,$price, $num, $user, $legal_id, $currency_id);//这里是真正的购买
            }



            $remain_num = bcsub($num, $has_num); //匹配后的剩余数量
            if (bc_comp($remain_num, '0') > 0) {
                $in = new TransactionIn();
                $in->user_id = $user_id;
                $in->price = $price;
                $in->type = $type;
                $in->number = $remain_num;
                $in->currency = $currency_id;
                $in->legal = $legal_id;
                $in->is_active = 1;
                $in->create_time = time();
                $in->save();
                $all_balance = bc_mul($price, $remain_num);
                //提交买入记录扣除
                $result = change_wallet_balance($user_legal, 1, -$all_balance, AccountLog::TRANSACTIONIN_SUBMIT_REDUCE, '提交挂买' . $currency_match->symbol . '扣除',AccountLog::TRANSACTIONIN_SUBMIT_REDUCE, 'Submit for purchase ' . $currency_match->symbol . ' deduction');
                if ($result !== true) {
                    throw new \Exception($result);
                }
                //提交买入记录扣除冻结
                $result = change_wallet_balance($user_legal, 1, $all_balance, AccountLog::TRANSACTIONIN_SUBMIT_REDUCE, '提交挂买' . $currency_match->symbol . '冻结','Submit for purchase ' . $currency_match->symbol . 'frozen',true);
                if ($result !== true) {
                    throw new \Exception($result);
                }
            }
            if ($currency_match->market_from != 2) {
                Transaction::pushNews($currency_id, $legal_id);
            }

            DB::commit();
            return $this->success(trans('transaction.czcg'));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->error($ex->getMessage());
        }
    }
    
  


    public function AllTransactionInList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();

        if (empty($user_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $limit = request()->input('limit', 10);
        $page = request()->input('page', 1);
        $transactionIn = TransactionIn::where('user_id', $user_id)->where('is_active', 1)->where('status', 0)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($transactionIn)) {
            return $this->error(trans('transaction.nhmyjyjl'));
        }
        return $this->success(array(
            "list" => $transactionIn->items(), 'count' => $transactionIn->total(),
            "page" => $page, "limit" => $limit
        ));
    }
    public function AllTransactionOutList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();

        if (empty($user_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $limit = request()->input('limit', 10);
        $page = request()->input('page', 1);
        $transactionOut = TransactionOut::where('user_id', $user_id)->where('is_active', 1)->where('status', 0)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($transactionOut)) {
            return $this->error(trans('transaction.nhmyjyjl'));
        }
        return $this->success(array(
            "list" => $transactionOut->items(), 'count' => $transactionOut->total(),
            "page" => $page, "limit" => $limit
        ));
    }
    
    
    
    //正在买入记录
    public function TransactionInList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $legal_id = $request->input('legal_id', 0);
        $currency_id = $request->input('currency_id', 0);
        if (empty($user_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $limit = request()->input('limit', 10);
        $page = request()->input('page', 1);
        $transactionIn = TransactionIn::where('user_id', $user_id)->where('is_active', 1)->where('status', 0)
            ->when($legal_id > 0, function ($query) use ($legal_id) {
                $query->where('legal', $legal_id);
            })
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($transactionIn)) {
            return $this->error(trans('transaction.nhmyjyjl'));
        }
        return $this->success(array(
            "list" => $transactionIn->items(), 'count' => $transactionIn->total(),
            "page" => $page, "limit" => $limit
        ));
    }

    //正在卖出记录
    public function TransactionOutList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $legal_id = $request->input('legal_id', 0);
        $currency_id = $request->input('currency_id', 0);
        if (empty($user_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $limit = request()->input('limit', 10);
        $page = request()->input('page', 1);
        $transactionOut = TransactionOut::where('user_id', $user_id)->where('is_active', 1)->where('status', 0)
            ->when($legal_id > 0, function ($query) use ($legal_id) {
                $query->where('legal', $legal_id);
            })
            ->when($currency_id > 0, function ($query) use ($currency_id) {
                $query->where('currency', $currency_id);
            })
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($transactionOut)) {
            return $this->error(trans('transaction.nhmyjyjl'));
        }
        return $this->success(array(
            "list" => $transactionOut->items(), 'count' => $transactionOut->total(),
            "page" => $page, "limit" => $limit
        ));
    }

    //交易完成记录
    public function TransactionCompleteList()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $limit = request()->input('limit', 10);
        $page = request()->input('page', 1);
        if (empty($user_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $TransactionComplete = TransactionComplete::where('user_id', $user_id)
            ->orwhere('from_user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        if (empty($TransactionComplete)) {
            return $this->error(trans('transaction.nhmyjyjl'));
        }
        foreach ($TransactionComplete->items() as $key => &$value) {
            if ($value['user_id'] == $user_id) {
                $value['type'] = 'in';
            } else {
                $value['type'] = 'out';
            }
        }
        return $this->success([
            "list" => $TransactionComplete->items(),
            'count' => $TransactionComplete->total(),
            "page" => $page, "limit" => $limit
        ]);
    }

    //取消交易
    public function TransactionDel(Request $request)
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
        $type = $request->input('type', ''); //in 买入交易 out卖出交易
        try {
            $user = Users::findOrFail($user_id);
            $validator = Validator::make($request->only(['id', 'type']), [
                'id' => 'required|integer|gt:0',
                'type' => 'required|in:in,out',
            ], [], [
                'id' => '交易id',
                'type' => '交易类型',
            ]);
            $transaction_class = $type == 'in' ? TransactionIn::class : TransactionOut::class;
            $transaction_del_class = $type == 'in' ? TransactionInDel::class : TransactionOutDel::class;
            $validator->after(function ($validator) use ($id, $transaction_class, $user) {
                try {
                    $trade = $transaction_class::lockForupdate()->findOrFail($id);
                    throw_if($trade->user_id != $user->id, new \Exception(trans('transaction.bnchfzifbdjy')));
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
                    return $validator->errors()->add('notfound', trans('transaction.jywzdknychhchqsxhcs'));
                } catch (\Throwable $th) {
                    return $validator->errors()->add('exception', $th->getMessage());
                }
            });
            throw_if($validator->fails(), new \Exception($validator->errors()->first()));
            DB::transaction(function () use ($user, $id, $type, $transaction_class, $transaction_del_class) {
                $trade = $transaction_class::lockForupdate()->findOrFail($id);
                $currency_match = CurrencyMatch::where('currency_id', $trade->currency)
                    ->where('legal_id', $trade->legal)
                    ->firstOrFail();
                // 退回原冻结数量
                if ($type == 'in') {
                    $shoud_refund_number = bc_mul($trade->price, $trade->number, 8);
                    $currency_id = $trade->legal; // 买入退回法币
                    $type_name = "挂买";
                    $type_name_en = "hang buy";
                    $currency_name = $currency_match->legal_name;
                } else {
                    $shoud_refund_number = $trade->number;
                    $currency_id = $trade->currency; // 卖出退回交易币
                    $type_name = "挂卖";
                    $type_name_en = "hanging sale";
                    $currency_name = $currency_match->currency_name;
                }
                $user_wallet = UsersWallet::where('user_id', $user->id)
                    ->where('currency', $currency_id)
                    ->firstOrFail();
                if (bc_comp($user_wallet->lock_change_balance, $shoud_refund_number,6) < 0) {
                    //20221126 返回改成抛出异常 提示改成冻结余额不足
                    throw new \Exception(trans('transaction.sbdjyebz'));
                    //return $this->error(trans('transaction.ch'));
                }
                change_wallet_balance(
                    $user_wallet,
                    1,
                    -$shoud_refund_number,
                    AccountLog::TRANSACTIONIN_IN_DEL,
                    "币币交易:用户取消{$type_name}{$currency_match->symbol},解除锁定{$currency_name},交易号:{$trade->id}",
                    "Coin trading: User cancels {$type_name}{$currency_match->symbol},Unlock {$currency_name},Transaction Number:{$trade->id}",
                    true
                );
                change_wallet_balance(
                    $user_wallet,
                    1,
                    $shoud_refund_number,
                    AccountLog::TRANSACTIONIN_IN_DEL,
                    "币币交易:用户取消{$type_name}{$currency_match->symbol},退回{$currency_name},交易号:{$trade->id}",
                   "Coin trading: User cancels {$type_name}{$currency_match->symbol},return{$currency_name},Transaction Number:{$trade->id}"
                );
                // 插入挂单备份
                $trade_del = $transaction_del_class::unguarded(function () use ($trade, $transaction_del_class) {
                    return $transaction_del_class::create([
                        'transaction_id' => $trade->id,
                        'type' => 2,
                        'user_id'  => $trade->user_id,
                        'price'  => $trade->price,
                        'total'  => $trade->total,
                        'number' => $trade->number,
                        'currency'  => $trade->currency,
                        'legal'  => $trade->legal,
                        'rate' => $trade->rate,
                        'is_auto' => $trade->is_auto,
                        'auto_id' => $trade->auto_id,
                        'is_active' => $trade->is_active,
                        'create_time' => $trade->getOriginal('create_time'),
                    ]);
                });
                throw_if(!isset($trade_del->id), new \Exception(trans('transaction.chsbjljyxxsb')));
                // 删除该挂单
                throw_unless($trade->delete(), new \Exception(trans('transaction.chsbxxsb')));
            });
            return $this->success(trans('transaction.chcg'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return $validator->errors()->add('notfound', $ex->getModel());
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 挂卖
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function out()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $price = request()->input("price");
        $num = request()->input("num");
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $type = request()->input("mode");
        
        if (empty($type)){
            $type = request()->input("type");//1 市价. 2 限价
        }
        if (empty($user_id) || empty($price) || empty($num) || empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('transaction.cscw'));
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
            return $this->error(trans('transaction.zdjydbcz'));
        }
        if ($currency_match->open_transaction != 1) {
            return $this->error(trans('transaction.nmyktgjyddjygn'));
        }
        $exchange_rate = $currency_match->exchange_rate;
        $quantity = bc_div(bc_mul($num, $exchange_rate), 100); //交易利率
        $real_quantity = $num + $quantity;
        $has_num = 0;
        
        $user = Users::find($user_id);
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        $legal = Currency::where("is_display", 1)
            ->where("id", $legal_id)
            ->where("is_legal", 1)
            ->first();
        $currency = Currency::where("is_display", 1)
            ->where("id", $currency_id)
            ->first();
        if (empty($user) || empty($legal) || empty($currency)) {
            return $this->error(trans('transaction.sjwzd'));
        }
        
        // 判断是否休市
        $is_lock = CurrencyQuotation::getCurrencyQuotationIsLock($currency_id,$legal_id);
        if($is_lock) {
            return $this->error(trans('common.stop'));
        }

        //使用JAVA进行撮合交易
        /*$use_java_match_trade = config('app.use_java_match_trade', 0);
        if ($use_java_match_trade) {
            $java_match_url = config('app.java_match_url', '');
            $request_client = new Client();
            $response = $request_client->post($java_match_url . '/api/transaction/out', [
                'headers' => [
                    'Authorization' => Token::getToken(),
                ],
                'form_params' => [
                    'legal_id' => $legal_id,
                    'currency_id' => $currency_id,
                    'price' => $price,
                    'num' => $num,
                    'type' => 1,
                ],
            ]);

            $result = $response->getBody()->getContents();
            $result = json_decode($result);
            if (!isset($result->type) || $result->type != 'ok') {
                return $this->error($result->message);
            }
            DB::commit();
            return $this->success("操作成功");
        }
*/
        
        try {
            DB::beginTransaction();
            $user_currency = UsersWallet::where("user_id", $user_id)
                ->where("currency", $currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($user_currency)) {
                throw new \Exception(trans('transaction.qxtjqb'));
            }
            if (bc_comp($price, '0') <= 0 || bc_comp($num, '0') <= 0) {
                throw new \Exception(trans('transaction.jeslbxdy'));
            }
            // if (bc_comp($user_currency->change_balance, $real_quantity) < 0) {
            //     $ndyebz = trans('transaction.ndyebzqb');
            //     throw new \Exception("{$ndyebz}{$exchange_rate}%({$quantity})");
            // }
            if (bc_comp($user_currency->lock_change_balance, '0') < 0) {
                throw new \Exception(trans('transaction.nddjzjycjzgm'));
            }
            
            
            
            $num = bcadd($num, 0, 5);
            if($type == 1){//市价
                // if(true){//市价
                $has_num = $num;
                TransactionOut::transactions($type,$price, $num, $user, $user_currency, $legal_id, $currency_id);
            }else {
                // 限价
                //挂卖先扣手续费
                // $result = change_wallet_balance($user_currency, 2, -$quantity, AccountLog::MATCH_TRANSACTION_SELL_FEE, '挂卖扣除手续费,挂卖数量:' . $num . ',费率:' . $exchange_rate . '%',10);
                // if ($result !== true) {
                //     throw new \Exception($result);
                // }
                // // 查找价格高于等于当前卖出价格的所有买入委托
                // $in = TransactionIn::where("price", ">=", $price)
                //     ->where("currency", $currency_id)
                //     ->where("legal", $legal_id)
                //     ->where("number", ">", "0")
                //     ->orderBy('price', 'desc')
                //     ->orderBy('id', 'asc')
                //     ->lockForUpdate()
                //     ->get();
                // //dd($in);
                // if (count($in) > 0) {
                //     foreach ($in as $i) {
                //         if (bc_comp($has_num, $num) < 0) {
                //             $shengyu_num = bc_sub($num, $has_num);
                //             $this_num = 0;
                //             if (bc_comp($i->number, $shengyu_num) > 0) {
                //                 $this_num = $shengyu_num;
                //             } else {
                //                 $this_num = $i->number; 
                //             }
                //             $has_num = bc_add($has_num, $this_num);
                //             if (bc_comp($this_num, '0') > 0) {
                //                 TransactionOut::transaction($i, $this_num, $user, $user_currency, $legal_id, $currency_id);
                //             }
                //         } else {
                //             break;
                //         }
                //     }
                // }
                
               $num = bc_sub($num, $has_num,5);
                if (bc_comp($num, '0') > 0) {
                    $out = new TransactionOut();
                    $out->type = $type;
                    $out->user_id = $user_id;
                    $out->price = $price;
                    $out->number = $num;
                    $out->currency = $currency_id;
                    $out->legal = $legal_id;
                    $out->is_active = 1;
                    $out->create_time = time();
                    $out->rate = $exchange_rate;
                    $out->save();
                    
                    
                    //提交卖出记录扣除交易币
                    $result = change_wallet_balance($user_currency, 1, -$num, AccountLog::TRANSACTIONOUT_SUBMIT_REDUCE, '提交挂卖' . $currency_match->symbol . '扣除','Submit for sale ' . $currency_match->symbol . ' deduction');
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                    //提交卖出记录(增加冻结)
                    $result = change_wallet_balance($user_currency, 1, $num, AccountLog::TRANSACTIONOUT_SUBMIT_REDUCE, '提交挂卖' . $currency_match->symbol . '冻结','Submit for sale ' . $currency_match->symbol . ' frozen',true);
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                }
            }
            
            if ($currency_match->market_from != 2) {
                Transaction::pushNews($currency_id, $legal_id);
            }
            DB::commit();
            // 机器人推送消息
            robotSendMessage($user_id,'币币卖出'.$currency->name.' 数量'.$num);
            return $this->success(trans('transaction.czcg'));
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 挂买
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function in()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $price = request()->input("price");
        $num = request()->input("num");
        $legal_id = request()->input("legal_id");
        $currency_id = request()->input("currency_id");
        $type = request()->input("mode");
        if (empty($type)){
            $type = request()->input("type");//1 市价. 2 限价
        }
        
        if (empty($user_id) || empty($price) || empty($num) || empty($legal_id) || empty($currency_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        
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
            return $this->error(trans('transaction.zdjybcz'));
        }
      
        
        if ($currency_match->open_transaction != 1) {
            return $this->error(trans('transaction.nhmyktgjyddjygn'));
        }
        
        $has_num = 0;
        $legal = Currency::where("is_display", 1)
            ->where("id", $legal_id)
            ->where("is_legal", 1)
            ->first();
        $currency = Currency::where("is_display", 1)
            ->where("id", $currency_id)
            ->first();
        $user = Users::find($user_id);
        
        if($user['status'] == 0){
            return $this->error(trans('login.gzhydjqlxkf'));
        }
        
        if (empty($user) || empty($legal) || empty($currency)) {
            return $this->error(trans('transaction.sjwzd'));
        }

        if (bc_comp($price, '0') <= 0 || bc_comp($num, '0') <= 0) {
            return $this->error(trans('transaction.jghslbxdy'));
        }
        
        // 判断是否休市
        $is_lock = CurrencyQuotation::getCurrencyQuotationIsLock($currency_id,$legal_id);
        if($is_lock) {
            return $this->error(trans('common.stop'));
        }
        
        //使用JAVA进行撮合交易
        /*$use_java_match_trade = config('app.use_java_match_trade', 0);
        if ($use_java_match_trade) {
            $java_match_url = config('app.java_match_url', '');
            $request_client = new Client();
            var_dump($java_match_url);die;
            $response = $request_client->post($java_match_url . '/api/transaction/in', [
                'headers' => [
                    'Authorization' => Token::getToken(),
                ],
                'form_params' => [
                    'legal_id' => $legal_id,
                    'currency_id' => $currency_id,
                    'price' => $price,
                    'num' => $num,
                    'type' => 1,
                ],
            ]);
            $result = $response->getBody()->getContents();
            $result = json_decode($result);
            if (!isset($result->type) || $result->type != 'ok') {
                return $this->error($result->message);
            }
            DB::commit();
            return $this->success("操作成功");
        }*/
        
        try {
            DB::beginTransaction();
            //买方法币钱包
            $user_legal = UsersWallet::where("user_id", $user_id)
                ->where("currency", $legal_id)
                ->lockForUpdate()
                ->first();
            $all_balance = bc_mul($price, $num);
            
            if (bc_comp($user_legal->change_balance, $all_balance) < 0) {
                throw new \Exception(trans('transaction.yebz'));
            }
            
           if($type == 1){//市价
            //   if(true){//市价
                $has_num = $num;
                TransactionIn::transactions($type,$price, $num, $user, $legal_id, $currency_id);//这里是真正的购买
            }else {
                //查找所有价格小于等于当前价格的卖出委托
                // $out = TransactionOut::where("price", "<=", $price)
                //     ->where("number", ">", "0")
                //     ->where("currency", $currency_id)
                //     ->where("legal", $legal_id)
                //     ->lockForUpdate()
                //     ->orderBy('price', 'asc')
                //     ->orderBy('id', 'asc')
                //     ->get();
    
                // if (count($out) > 0) {
                //     foreach ($out as $o) {
                //         if (bc_comp($has_num, $num) < 0) {
                //             $shengyu_num = bc_sub($num, $has_num);
                //             $this_num = 0;
                //             if (bc_comp($o->number, $shengyu_num) > 0) {
                //                 $this_num = $shengyu_num;
                //             } else {
                //                 $this_num = $o->number;
                //             }
                //             $has_num = bc_add($has_num, $this_num);
                //             if (bc_comp($this_num, '0') > 0) {
                //                 TransactionIn::transaction($o, $this_num, $user, $legal_id, $currency_id);
                //             }
                //         } else {
                //             break;
                //         }
                //     }
                // }
                $remain_num = bc_sub($num, $has_num,5); //匹配后的剩余数量
                if (bc_comp($remain_num, '0') > 0) {
                    $in = new TransactionIn();
                    $in->user_id = $user_id;
                    $in->price = $price;
                    $in->type = $type;
                    $in->number = $remain_num;
                    $in->currency = $currency_id;
                    $in->legal = $legal_id;
                    $in->is_active = 1;
                    $in->create_time = time();
                    $in->save();
                    $all_balance = bc_mul($price, $remain_num);
                    
                    
                    //提交买入记录扣除
                    $result = change_wallet_balance($user_legal, 1, -$all_balance, AccountLog::TRANSACTIONIN_SUBMIT_REDUCE, '提交挂买' . $currency_match->symbol . '扣除','Submit for purchase ' . $currency_match->symbol . ' deduction');
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                    //提交买入记录扣除冻结
                    $result = change_wallet_balance($user_legal, 1, $all_balance, AccountLog::TRANSACTIONIN_SUBMIT_REDUCE, '提交挂买' . $currency_match->symbol . '冻结','Submit for purchase ' . $currency_match->symbol . ' frozen',true);
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                }   
            }
            
            if ($currency_match->market_from != 2) {
                Transaction::pushNews($currency_id, $legal_id);
            }

            DB::commit();
            // 机器人推送消息
            robotSendMessage($user_id,'币币买入'.$currency->name.' 数量'.$num);
            return $this->success(trans('transaction.czcg'));
        } catch (\Exception $ex) {
            DB::rollback();
            return $this->error($ex->getMessage());
        }
    }

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
            return $this->error(trans('transaction.cscw'));
        }
        $legal_currency = Currency::find($legal_id);
        $currency_match = CurrencyMatch::where('legal_id', $legal_id)
            ->where('currency_id', $currency_id)
            ->first();
        if (!$currency_match) {
            return $this->error(trans('transaction.zdsysbolsdbcz'));
        }
        $in = TransactionIn::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->where('is_active', 1)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'desc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit(10)
            ->get()
            ->toArray();
        $out = TransactionOut::with(['legalcoin', 'currencycoin'])
            ->where("number", ">", 0)
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->where('is_active', 1)
            ->groupBy('currency', 'legal', 'price')
            ->orderBy('price', 'asc')
            ->select([
                'currency',
                'legal',
                'price',
            ])->selectRaw('sum(`number`) as `number`')
            ->limit(10)
            ->get()
            ->toArray();
        $in = array_map(function ($item) {
            $item['number'] = number_format($item['number'], 4, '.', '');
            $item['price'] = number_format($item['price'], 6, '.', '');
            return $item;
        }, $in);

        $out = array_map(function ($item) {
            $item['number'] = number_format($item['number'], 4, '.', '');
            $item['price'] = number_format($item['price'], 6, '.', '');
            return $item;
        }, $out);

        krsort($out);
        $out_data = array();
        foreach ($out as $o) {
            array_push($out_data, $o);
        }

        $complete = TransactionComplete::orderBy('id', 'desc')
            ->where("currency", $currency_id)
            ->where("legal", $legal_id)
            ->take(20)
            ->get();

        $last_price = 0;
        //从行情取最新价
        $last =  CurrencyQuotation::getCurrencyQuotationDetail($currency_id,$legal_id);
        if (!$last) {
            $last = TransactionComplete::orderBy('id', 'desc')
                ->where("currency", $currency_id)
                ->where("legal", $legal_id)
                ->first();
            if (!empty($last)) {
                $last_price = $last->price;
            }
        } else {
            $last && $last_price = $last->now_price;
        }
        $user_legal = 0;
        $user_currency = 0;
        if (!empty($user_id)) {
            $legal = UsersWallet::where("user_id", $user_id)->where("currency", $legal_id)->first();
            if ($legal) {
                $user_legal = $legal->change_balance;
            }
            $currency = UsersWallet::where("user_id", $user_id)->where("currency", $currency_id)->first();
            if ($currency) {
                $user_currency = $currency->change_balance;
            }
        }

        return $this->success([
            "in" => $in,
            "out" => $out_data,
            'legal_currency' => $legal_currency,
            //all_legal"=>$all_legal,
            //"all_currency"=>$all_currency,
            "last_price" => $last_price,
            "user_legal" => $user_legal,
            "user_currency" => $user_currency,
            "complete" => $complete,
            'currency_match' => $currency_match,
        ]);
    }

    public function introduction(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $currency_id = $request->input('currency_id', "");
        if (empty($currency_id)) {
            return $this->error(trans('transaction.cscw'));
        }
        $currency = Currency::where('id', $currency_id)->select()->get();
        $data = [];
        if (empty($currency_id)) {
            $data['status'] = 0;
            $data['introduction'] = 0;
        } else {
            $data['status'] = 1;
            $data['introduction'] = $currency;
        }
        return $this->success($data);
    }
}
