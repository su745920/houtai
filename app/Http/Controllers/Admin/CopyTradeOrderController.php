<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FromQueryExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{Address,
    AccountLog,
    Bankcard,
    Currency,
    CurrencyMatch,
    Follow,
    PaymentMethod,
    Transaction,
    Users,
    UserCashInfo,
    UserReal,
    UsersWallet,
    BlindBoxOrder,
    LeverTransaction,
    LockMiningOrder,
    MicroOrder,
    RechargeRecord,
    WalletLog,
    MarketHour,
    CreditLog};
//交易员自己下的订单
class CopyTradeOrderController extends Controller
{
    public function page()
    {
        $matches = CurrencyMatch::where('open_lever', 1)->get();
        $authorityList=session()->get("authorityList");
        return view("admin.copy_trade.trade_order_index", [
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
        })->when(true, function ($query) use ($account_number) {
            $query->whereHas('user', function ($query) use ($account_number) {
                $query->where('is_trader', 1);
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
        return $this->layuiData($order_list);
    }

}
