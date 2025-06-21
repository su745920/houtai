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
    Follow,
    PaymentMethod,
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

class CopyTradeController extends Controller
{
    public function edit(Request $request){
        $authorityList=session()->get("authorityList");
        $uid=$request->input("id");
        $user=Users::getById($uid);

        return view("admin.copy_trade.edit",['authorityList' =>$authorityList,'result'=>$user]);
    }
    public function page(Request $request)
    {

        $authorityList=session()->get("authorityList");
        return view("admin.copy_trade.trader_page",['authorityList' =>$authorityList]);
    }
    public function tradePageData(Request $request)
    {
        $user_id = Users::getUserId();
        $list = Users::query()
            ->selectRaw('count(t.id) as closed_count')
            ->selectRaw('IFNULL(sum(t.fact_profits),0) as total_profits')   //总盈亏
            ->selectRaw('users.id,users.nickname,users.head_portrait,users.virtual_follow_num,users.account_number')
            ->leftJoin('lever_transaction as t', function ($join) {
                $join->on('users.id', '=', 't.user_id')
                    ->where('t.status', LeverTransaction::CLOSED);
            })
            ->where('users.is_trader', 1)
            ->groupBy('users.id')
            ->orderBy('total_profits', 'desc')
            ->paginate();

        $list = $list->setCollection($list->getCollection()->map(function ($item) use ($user_id) {
            //查询已平仓且盈利的总数
            $flat_count = LeverTransaction::query()
                ->where([
                    'status' => LeverTransaction::CLOSED,
                    'fact_profits' => ['>', 0],
                    'user_id' => $item->id
                ])
                ->count();
            //总准确率
            $item->correct_rate = $item->closed_count ? round($flat_count / $item->closed_count * 100, 2) : 0.00;
            if (!empty($item->virtual_follow_num)) {
                //跟随人数，有虚拟数则使用虚拟数
                $item->follow_num = $item->virtual_follow_num;
            } else {
                $item->follow_num = Follow::query()->where(['follow_user_id' => $item->id, 'status' => 1])->count();
            }
            unset($item->virtual_follow_num);

            //查询当前交易员是否已跟随
            $item->is_followed = Follow::query()
                ->where(['user_id' => $user_id, 'follow_user_id' => $item->id, 'status' => 1])
                ->exists();


            return $item;
        }));
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
        //return $this->success($list);

    }

}
