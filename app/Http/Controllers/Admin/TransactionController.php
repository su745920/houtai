<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FromArrayExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{AccountLog, Currency, CurrencyMatch, Transaction, TransactionComplete, TransactionIn, TransactionOut, LeverTransaction, TransactionInDel, TransactionOrder, TransactionOutDel, Users, UsersWallet,CurrencyQuotation};

class TransactionController extends Controller
{

    public function index()
    {
        $currency = Currency::all();
        return view("admin.transaction.index", ['currency' => $currency]);
    }

    public function lists()
    {
        $limit = request()->input('limit', 10);
        $account_number = request()->input('account_number', ''); //用户交易账号
        $type = request()->input('type', '');
        $currency = request()->input('currency', '');
        $status = request()->input('status', '');
        $result = new Transaction();
        if (!empty($account_number)) {

            $users = Users::where('account_number', 'like', '%' . $account_number . '%')->get()->pluck('id');
            $result = $result->where(function ($query) use ($users) {
                $query->whereIn('from_user_id', $users);
            });
        }

        if (!empty($type)) {
            $result = $result->where('type', '=', $type);
        }
        if (!empty($currency)) {
            $result = $result->where('currency', $currency);
        }
        if (!empty($status)) {
            $result = $result->where('status', $status);
        }


        $list = $result->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function completeIndex()
    {
        $legal_currencies = Currency::where('is_legal', 1)->get();
        $currencies = Currency::get();
        $authorityList=session()->get("authorityList");
        return view("admin.transaction.complete", [
            'legal_currencies' => $legal_currencies,
            'currencies' => $currencies,
            'authorityList' => $authorityList
        ]);
    }

    public function inIndex()
    {
        $legal_currencies = Currency::where('is_legal', 1)->get();
        $authorityList=session()->get("authorityList");

        $currencies = Currency::get();
        return view("admin.transaction.in", [
            'legal_currencies' => $legal_currencies,
            'currencies' => $currencies,
            'authorityList' => $authorityList
        ]);
    }

    public function outIndex()
    {
        $legal_currencies = Currency::where('is_legal', 1)->get();
        $currencies = Currency::get();
        $authorityList=session()->get("authorityList");
        return view("admin.transaction.out", [
            'legal_currencies' => $legal_currencies,
            'currencies' => $currencies,
            'authorityList' => $authorityList
        ]);
    }

    public function cnyIndex()
    {
        return view("admin.transaction.cny");
    }

    public function trade()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.transaction.trade',['authorityList' => $authorityList]);
    }

    public function completeList(Request $request)
    {
        $limit = $request->input('limit', 10);
        //$account_number = $request->input('account_number', '');
        $result = TransactionComplete::whereHas('user', function ($query) use ($request) {
            //$account_number = $request->input('buy_account_number', '');
            //$account_number != '' && $query->where('account_number', 'like', '%' . $account_number . '%');
        })->where(function ($query) use ($request) {

//            $legal = $request->input('legal', 23);
//            $currency = $request->input('currency', 32);
//            $legal != -1 && $query->where('legal', $legal);
//            $currency != -1 && $query->where('currency', $currency);
//            $start_time = $request->input('start_time', '');
//            $end_time = $request->input('end_time', '');
//            if (!empty($start_time)) {
//                $start_time = strtotime($start_time);
//                $query->where('create_time', '>=', $start_time);
//            }
//            if (!empty($end_time)) {
//                $end_time = strtotime($end_time);
//                $query->where('create_time', '<=', $end_time);
//            }



        })->orderBy('id', 'desc')->paginate($limit);
        $sum = $result->sum('number');
        //return $this->layuiData($result, $sum);
        foreach ($result as $item){
            $from_number=$item->from_user_id;
            if (empty($from_number)||$from_number==0){
                $from_number="机器人";
                $item->fromNumber=$from_number;
            }else{
                $item->fromNumber=$item->from_number;
            }

        }
        return response()->json(['code' => 0, 'data' => $result->items(), 'count' => $result->total(),'sum'=>$sum]);
    }

    public function inList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $result = TransactionIn::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number', '');
            $account_number != '' && $query->where('account_number', 'like', '%' . $account_number . '%');
            $user_id = $request->input('user_id', 0);
            $user_id != '' && $query->where('user_id',  $user_id );
        })->where(function ($query) use ($request) {
            $legal = $request->input('legal', -1);
            $currency = $request->input('currency', -1);
            $legal != -1 && $query->where('legal', $legal);
            $currency != -1 && $query->where('currency', $currency);
            $start_time = $request->input('start_time', '');
            $end_time = $request->input('end_time', '');
            if (!empty($start_time)) {
                $start_time = strtotime($start_time);
                $query->where('create_time', '>=', $start_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $query->where('create_time', '<=', $end_time);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        $sum = $result->sum('number');
        return $this->layuiData($result, $sum);
    }

    public function outList(Request $request)
    {
        $limit = $request->input('limit', 10);

        $result = TransactionOut::whereHas('user', function ($query) use ($request) {
            $account_number = $request->input('account_number', '');
            $account_number != '' && $query->where('account_number', 'like', '%' . $account_number . '%');
            $user_id = $request->input('user_id', 0);
            $user_id != '' && $query->where('user_id',  $user_id );
        })->where(function ($query) use ($request) {
            $legal = $request->input('legal', -1);
            $currency = $request->input('currency', -1);
            $legal != -1 && $query->where('legal', $legal);
            $currency != -1 && $query->where('currency', $currency);
            $start_time = $request->input('start_time', '');
            $end_time = $request->input('end_time', '');
            if (!empty($start_time)) {
                $start_time = strtotime($start_time);
                $query->where('create_time', '>=', $start_time);
            }
            if (!empty($end_time)) {
                $end_time = strtotime($end_time);
                $query->where('create_time', '<=', $end_time);
            }
        })->orderBy('id', 'desc')->paginate($limit);
        $sum = $result->sum('number');
        return $this->layuiData($result, $sum);
    }

    public function cnyList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $result = new AccountLog();
        if (!empty($account_number)) {
            $users = Users::where('account_number', 'like', '%' . $account_number . '%')->get()->pluck('id');
            $result = $result->whereIn('user_id', $users);
        }
        $types = array(13, 14, 15, 20, 22, 24);
        $result = $result->whereIn('type', $types)->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function Leverdeals_show()
    {
        $matches = CurrencyMatch::where('open_lever', 1)->get();
        $authorityList=session()->get("authorityList");
        return view("admin.leverdeals.list", [
            'matches' => $matches,
        ]);
    }

    //杠杆交易
    public function Leverdeals(Request $request)
    {
        $limit = $request->input("limit", 10);
        $match_id = $request->input('match_id', 0);
        $user_id = request()->input('user_id', ''); //用户ID
        $account_number = $request->input("account_number", '');
        $status = $request->input("status", -1);
        $type = $request->input("type", 0);
        $start_time = $request->input("start_time", '');
        $end_time = $request->input("end_time", '');
        $legal_id = 0;
        $currency_id = 0;
        if ($match_id > 0) {
            $match = CurrencyMatch::find($match_id);
            $legal_id = $match->legal_id ?? 0;
            $currency_id = $match->currency_id ?? 0;
        }
        $order_list = LeverTransaction::when($legal_id > 0, function ($query) use ($legal_id) {
            $query->where('legal', $legal_id);
        })->when($currency_id > 0, function ($query) use ($currency_id) {
            $query->where('currency', $currency_id);
        })->when($user_id > 0, function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->when($account_number != '', function ($query) use ($account_number) {
            $query->whereHas('user', function ($query) use ($account_number) {
                $query->where('account_number', $account_number)
                    ->orWhere('phone', $account_number)
                    ->orWhere('email', $account_number);
            });
        })->when($type > 0, function ($query) use ($type) {
            $query->where('type', $type);
        })->when($status <> -1, function ($query) use ($status) {
            $query->where('status', $status);
        })->when($start_time != '', function ($query) use ($start_time) {
            $query->where('create_time', '>=', strtotime($start_time));
        })->when($end_time != '', function ($query) use ($end_time) {
            $query->where('create_time', '<=', strtotime($end_time));
        })->orderBy('id', 'desc')
            ->paginate($limit);
        // 计算实时盈亏
        $quotation_list = CurrencyQuotation::getCurrencyQuotationList();
        foreach ($order_list as $k => $v) {
            if(!$v->handle_time) {
                 foreach ($quotation_list as $qk => $qv) {
                    if($v->currency == $qv->currency_id && $v->legal == $qv->legal_id) {
                        // 判断类型
                        if($v->type == 1) {
                            // 盈亏 =（当前价-开仓价）×手数×杠杆
                           $sub_price = bcsub($qv->close,$v->origin_price,4);
                        }else {
                            // 盈亏 =（开仓价-当前价）×手数×杠杆
                            $sub_price = bcsub($v->origin_price,$qv->close,4);
                        }
                        $order_list[$k]['fact_profits'] = bcmul(bcmul($sub_price,$v->share,4),$v->multiple,4); 
                        break;
                    }
                }   
            }
        }
        return $this->layuiData($order_list);
    }

    //风险设置
    public function cautionView(Request $request){
        $data = [
            'id' => $request->get('id'),
            'symbol' => $request->get('symbol'),
            'account_number' => $request->get('account_number'),
            'caution_time' => $request->get('caution_time'),
            'caution_price' => $request->get('caution_price'),
        ];

        return view('admin.leverdeals.caution',$data);
    }

    //风险设置确定
    public function cautionConfirm(Request $request){
        $id = $request->input('id');
        $caution_time = $request->input('caution_time');
        $caution_price = $request->input('caution_price');
        if(empty($id)){
            return $this->error('参数错误');
        }
        $legalDeal = LeverTransaction::LockForUpdate()->find($id);
        if (empty($legalDeal)) {
            return $this->error('找不到交易订单');
        }
        if($legalDeal->status != 1){
            return $this->error('订单状态异常');
        }
        $legalDeal->caution_time = $caution_time;
        $legalDeal->caution_price = $caution_price;
        $legalDeal->save();
        return $this->success('success');
    }

    //导出合约交易 团队所有订单excel
    public function csv(Request $request)
    {
        $id = $request->input("id", 0);
        $username = $request->input("phone", '');
        $status = $request->input("status", 10);
        $type = $request->input("type", 0);

        $start = $request->input("start", '');
        $end = $request->input("end", '');
        $where = [];
        if ($id > 0) {
            $where[] = ['lever_transaction.id', '=', $id];
        }
        if (!empty($username)) {
            $s = DB::table('users')->where('account_number', $username)->first();
            if ($s !== null) {
                $where[] = ['lever_transaction.user_id', '=', $s->id];
            }
        }

        if ($status != -1 && in_array($status, [LeverTransaction::ENTRUST, LeverTransaction::BUY, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING])) {
            $where[] = ['lever_transaction.status', '=', $status];
        }

        if ($type > 0 && in_array($type, [1, 2])) {
            $where[] = ['type', '=', $type];
        }
        if (!empty($start) && !empty($end)) {
            $where[] = ['lever_transaction.create_time', '>', strtotime($start . ' 0:0:0')];
            $where[] = ['lever_transaction.create_time', '<', strtotime($end . ' 23:59:59')];
        }

        $order_list = TransactionOrder::leftjoin("users", "lever_transaction.user_id", "=", "users.id")->select("lever_transaction.*", "users.phone")->whereIn('lever_transaction.status', [LeverTransaction::ENTRUST, LeverTransaction::BUY, LeverTransaction::CLOSED, LeverTransaction::CANCEL, LeverTransaction::CLOSING])->where($where)->get();

        foreach ($order_list as $key => $value) {
            $order_list[$key]["create_time"] = date("Y-m-d H:i:s", $value->create_time);
            $order_list[$key]["transaction_time"] = date("Y-m-d H:i:s", substr($value->transaction_time, 0, strpos($value->transaction_time, '.')));
            $order_list[$key]["update_time"] = date("Y-m-d H:i:s", substr($value->update_time, 0, strpos($value->update_time, '.')));
            $order_list[$key]["handle_time"] = date("Y-m-d H:i:s", substr($value->handle_time, 0, strpos($value->handle_time, '.')));
            $order_list[$key]["complete_time"] = date("Y-m-d H:i:s", substr($value->complete_time, 0, strpos($value->complete_time, '.')));
        }
        $data = $order_list;
        return Excel::download(new FromArrayExport($data), '交易明细.xlsx');
    }

    /**
     * 后台强制平仓
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function close()
    {
        $id = request()->input("id");
        $last_price = request()->input("price");
        if (empty($id)) {
            return $this->error("参数错误");
        }
        if (empty($last_price)) {
            return $this->error("请输入平仓价格");
        }

        DB::beginTransaction();
        try {
            $lever_transaction = LeverTransaction::lockForupdate()->find($id);
            if (empty($lever_transaction)) {
                throw new \Exception("数据未找到");
            }

            if ($lever_transaction->status != LeverTransaction::TRANSACTION) {
                throw new \Exception("交易状态异常,请勿重复提交");
            }
            $return = LeverTransaction::leverClose($lever_transaction, LeverTransaction::CLOSED_BY_ADMIN,$last_price);
            if (!$return) {
                throw new \Exception("平仓失败,请重试");
            }
            DB::commit();
            return $this->success("操作成功");
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    /**
     * 取消撮合交易挂单
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        $id = $request->input('id', 0);
        $type = $request->input('type', '');
        try {
            throw_if(!in_array($type, ['in', 'out']), new \Exception('交易类型异常'));
            DB::transaction(function () use ($id, $type) {
                $transaction_class = $type == 'in' ? TransactionIn::class : TransactionOut::class;
                $transaction_del_class = $type == 'in' ? TransactionInDel::class : TransactionOutDel::class;
                $trade = $transaction_class::findOrFail($id);
                $user = Users::findOrFail($trade->user_id);
                $currency_match = CurrencyMatch::where('currency_id', $trade->currency)
                    ->where('legal_id', $trade->legal)
                    ->firstOrFail();
                // 退回原冻结数量
                if ($type == 'in') {
                    $shoud_refund_number = bc_mul($trade->price, $trade->number, 8);
                    $currency_id = $trade->legal;
                    $type_name = "挂买";
                    $type_name_en = "Hang buy";
                    $currency_name = $currency_match->legal_name;
                } else {
                    $shoud_refund_number = $trade->number;
                    $currency_id = $trade->currency;
                    $type_name = "挂卖";
                    $type_name_en = "Hanging sale";
                    $currency_name = $currency_match->currency_name;
                }
                $user_wallet = UsersWallet::where('user_id', $user->id)
                    ->where('currency', $currency_id)
                    ->firstOrFail();
                if (bc_comp($user_wallet->lock_change_balance, $shoud_refund_number) < 0) {
                    throw new \Exception("撤回{$type_name}失败,冻结余额不足");
                }
                change_wallet_balance(
                    $user_wallet,
                    2,
                    -$shoud_refund_number,
                    AccountLog::TRANSACTIONIN_IN_DEL,
                    "币币交易:SYS取消{$type_name}{$currency_match->symbol},解除锁定{$currency_name},交易号:{$trade->id}",
                    "Currency transaction:SYS cancel {$type_name_en}{$currency_match->symbol},unlock {$currency_name},transaction number:{$trade->id}",
                    true
                );
                change_wallet_balance(
                    $user_wallet,
                    2,
                    $shoud_refund_number,
                    AccountLog::TRANSACTIONIN_IN_DEL,
                    "币币交易:SYS取消{$type_name}{$currency_match->symbol},退回{$currency_name},交易号:{$trade->id}",
                    "Currency transaction:SYS cancel {$type_name_en}{$currency_match->symbol},return {$currency_name},transaction number:{$trade->id}"
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
                throw_if(!isset($trade_del->id), new \Exception('撤回失败:记录交易信息失败'));
                // 删除该挂单
                throw_unless($trade->delete(), new \Exception('撤回失败:清除交易失败'));
            });
            return $this->success('撤回成功!');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 删除撮合交易挂单
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function del(Request $request)
    {
        $id = $request->input('id', 0);
        $type = $request->input('type', '');
        try {
            throw_if(!in_array($type, ['in', 'out']), new \Exception('交易类型异常'));
            $transaction = $type == 'in' ? TransactionIn::class : TransactionOut::class;
            DB::transaction(function () use ($id, $transaction) {
                $trade = $transaction::lockForupdate()->findOrFail($id);
                $trade->delete();
            });
            return $this->success('删除成功!');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
    //强制买入
    public function trading(Request $request){
        $id = $request->input('id',0);
        $type = 'in';
        $price = $request->input('price',0);
        if (empty($price)) {
            return $this->error("请输入交易价格");
        }
        try {
            DB::transaction(function () use ($id, $type, $price) {
                throw_if(!in_array($type, ['in', 'out']), new \Exception('交易类型异常'));
                $transaction_class = $type == 'in' ? TransactionIn::class : TransactionOut::class;
                $transaction_del_class = $type == 'in' ? TransactionInDel::class : TransactionOutDel::class;
                $trade = $transaction_class::lockForupdate()->findOrFail($id);
                $user = Users::findOrFail($trade->user_id);
                $currency_match = CurrencyMatch::where('currency_id', $trade->currency)
                    ->where('legal_id', $trade->legal)
                    ->firstOrFail();
                
                // $shoud_refund_number = bc_mul($trade->price, $trade->number, 8);
                $shoud_refund_number = bc_mul($price, $trade->number, 8);
                $currency_id = $trade->legal;
                $trade_id = $trade->currency;
                // $type_name = "强制买入交易";
                // $type_name_en = "Forced buy transaction";
                $type_name = "买入交易";
                $type_name_en = "Buying transaction";
                $currency_name = $currency_match->legal_name;
                
                
                $user_wallet = UsersWallet::where('user_id', $user->id)
                    ->where('currency', $currency_id)
                    ->firstOrFail();
                $trading_wallet = UsersWallet::where('user_id', $user->id)
                    ->where('currency', $trade_id)
                    ->firstOrFail();
                if (bc_comp($user_wallet->lock_change_balance, $shoud_refund_number) < 0) {
                    throw new \Exception("强制交易{$type_name}失败,冻结余额不足".$user->id);
                }

                //扣除usdt冻结金额
                change_wallet_balance(
                    $user_wallet,
                    1,
                    -$shoud_refund_number,
                    AccountLog::DEDUCT_THE_FROZEN_AMOUNT,
                    "币币交易:SYS取消{$type_name}{$currency_match->symbol},扣除{$currency_name},交易号:{$trade->id}",
                    "Currency transaction: sys cancel {$type_name_en}{$currency_match->symbol},deduction {$currency_name},transaction number:{$trade->id}",
                    true
                );

                //增加交易币种数量
                change_wallet_balance(
                    $trading_wallet,
                    1,
                    $trade->number,
                    AccountLog::MANDATORY_TRANSACTION,
                    "币币交易:SYS交易{$type_name}{$currency_match->symbol},交易{$currency_name},交易号:{$trade->id}",
                    "Currency transaction: sys cancel {$type_name_en}{$currency_match->symbol},transaction {$currency_name},transaction number:{$trade->id}"
                );

                // 删除该挂单
                throw_unless($trade->delete(), new \Exception('交易失败:清除交易失败'));
                //添加完成记录
                $complete = new TransactionComplete();
                $complete->way = 1; //挂买
                $complete->type = $trade->type;
                $complete->user_id = $user->id; //买方
                $complete->from_user_id = $user->id; //卖方
                // $complete->price = $trade->price;
                $complete->price = $price;
                $complete->number = $trade->number;
                $complete->currency = $trade->currency;
                $complete->legal = $trade->legal;
                $complete->in_fee = 0; //写入手续费
                $complete->create_time = time();
                $complete->save();
            });
        } catch (\Throwable $th){
            return $this->error($th->getMessage());
        }
        return $this->success('交易成功!');
    }


     //强制卖出
    public function tradingout(Request $request){
        $id = $request->input('id',0);
        $type = 'out';
        $price = $request->input('price',0);
        if (empty($price)) {
            return $this->error("请输入交易价格");
        }
        try {
            DB::transaction(function () use ($id, $type, $price) {
                throw_if(!in_array($type, ['in', 'out']), new \Exception('交易类型异常'));
                $transaction_class = $type == 'in' ? TransactionIn::class : TransactionOut::class;
                $transaction_del_class = $type == 'in' ? TransactionInDel::class : TransactionOutDel::class;
                $trade = $transaction_class::lockForupdate()->findOrFail($id);
                $user = Users::findOrFail($trade->user_id);
                $currency_match = CurrencyMatch::where('currency_id', $trade->currency)
                    ->where('legal_id', $trade->legal)
                    ->firstOrFail();

                // $shoud_refund_number = bc_mul($trade->price, $trade->number, 8);
                $shoud_refund_number = bc_mul($price, $trade->number, 8);
                $currency_id = $trade->currency;
                $trade_id = $trade->legal;
                $type_name = "卖出交易";
                $currency_name = $currency_match->currency_name;
                $user_wallet = UsersWallet::where('user_id', $user->id)
                    ->where('currency', $currency_id)
                    ->firstOrFail();
                $trading_wallet = UsersWallet::where('user_id', $user->id)
                    ->where('currency', $trade_id)
                    ->firstOrFail();

                if (bc_comp($user_wallet->lock_change_balance, $trade->number) < 0) {
                    throw new \Exception("强制卖出{$type_name}失败,冻结余额不足");
                }

                //扣除交易币数量
                change_wallet_balance(
                    $user_wallet,
                    1,
                    -$trade->number,
                    AccountLog::DEDUCT_THE_FROZEN_AMOUNT,
                    "币币交易:SYS取消{$type_name}{$currency_match->symbol},扣除{$currency_name},交易号:{$trade->id}",
                    "Currency transaction:SYS cancel {$type_name}{$currency_match->symbol},deduction {$currency_name},Transaction number:{$trade->id}",
                    true
                );

                //增加USDT
                change_wallet_balance(
                    $trading_wallet,
                    1,
                    $shoud_refund_number,
                    AccountLog::MANDATORY_TRANSACTION,
                    "币币交易:SYS交易{$type_name}{$currency_match->symbol},卖出{$currency_name},交易号:{$trade->id}",
                    "Currency transaction:SYS cancel {$type_name}{$currency_match->symbol},sell out {$currency_name},Transaction number:{$trade->id}"
                );

                // 删除该挂单
                throw_unless($trade->delete(), new \Exception('交易失败:清除交易失败'));
                 //添加完成记录
                $complete = new TransactionComplete();
                $complete->way = 2; //
                $complete->type = $trade->type;
                $complete->user_id = $user->id; //买方
                $complete->from_user_id = $user->id; //卖方
                // $complete->price = $trade->price;
                $complete->price = $price;
                $complete->number = $trade->number;
                $complete->currency = $trade->currency;
                $complete->legal = $trade->legal;
                $complete->in_fee = 0; //写入手续费
                $complete->create_time = time();
                $complete->save();
            });
        } catch (\Throwable $th){
            return $this->error($th->getMessage());
        }
        return $this->success('交易成功!');
    }
}
