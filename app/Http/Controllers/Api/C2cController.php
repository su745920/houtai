<?php

namespace App\Http\Controllers\Api;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccountLog;
use App\Models\Currency;
use App\Models\C2CUserOrder;
use App\Models\C2CInfo;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UsersWallet;
use App\Models\UserReal;
use App\Models\UserCashInfo;
use App;
use function GuzzleHttp\Psr7\str;


class C2cController extends Controller
{
    public function saveAppeal(Request $request)
    {
        $img = $request->input("img");
        $content = $request->input("content");
        $uid = Users::getUserId();
        $user = Users::getById($uid);
        $account = $user->account_number;

        $po = new App\Models\C2cAppeal();
        if (!empty($img) && strlen($img) > 10) {
            $po->img = $img;
        }
        if (!empty($content) && strlen($content) > 0) {
            $po->content = $content;
        }
        $po->uid = $uid;
        $po->account = $account;
        $po->create_date=date('Y-m-d');
        $po->save();
        return $this->success("提交成功，等待客服处理");


    }

    public function myUserCashInfo()
    {
        $user_id = Users::getUserId();
        $userCashInfo = UserCashInfo::getById($user_id);
        if (empty($userCashInfo)) {
            return $this->success("");
        }
        $bank_account = $userCashInfo->bank_account;

        $len = strlen($bank_account);
        $bank_account = substr($bank_account, $len - 4);
        $bank_account = "*" . $bank_account;
        $userCashInfo->bank_account = $bank_account;

        return $this->success($userCashInfo);
    }

    //我的资产->资金资产也就是legal
    public function legalWalletList(Request $request)
    {

        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $USDTRate = Setting::getValueByKey('USDTRate', 6.9);

        $cache_key_name = "user_wallet_data_{$user_id}";
        // if (Cache::has($cache_key_name)) {
        //     $wallet_data = Cache::get($cache_key_name);
        // } else {
        $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {

                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');

            })->get();

        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'logo',
                'id', 'currency', 'currency_name', 'sort',
                'currency_type', 'contract_address',
                'usdt_price', 'usd_price', 'multi_protocol',
                'legal_balance', 'lock_legal_balance', 'is_recharge', 'is_withdraw',
                'lever_balance', 'lock_lever_balance',
                'change_balance', 'lock_change_balance',
                'micro_balance', 'lock_micro_balance',
                'address', 'erc20_address',
                'is_legal', 'is_lever', 'is_match', 'is_transfer', 'is_micro', 'is_transfer', 'is_distransfer',
            ]);
            return $item;
        });
        $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->values()->all();
        $legal_wallet['totle'] = 0;
        $legal_wallet['CNY'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            $num = $v['legal_balance'] + $v['lock_legal_balance'];
            if ($v["id"] == 23) {
                $legal_wallet['totle'] += $num;
                //$legal_wallet['CNY'] += $num;
            } else {
                $legal_wallet['totle'] += $num * $v['usdt_price'];
                //$legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'],6);
            }
        }


        //读取是否开启充提币
        $is_open_ctbi = Setting::getValueByKey("is_open_CTbi");
        $wallet_data = [
            'legal_wallet' => $legal_wallet,
            "is_open_ctbi" => $is_open_ctbi,
            'is_open_CTbi' => $is_open_ctbi,
            'ExRate' => $USDTRate,
            'USDTRate' => $USDTRate,
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }

    /**
     * c2c-用户订单详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userOrderDetail(Request $request)
    {
        $id = $request->input("id");


        $user_id = Users::getUserId();

        $item = C2CUserOrder::getById($id);


        $seller_id = $item->seller_id;
        $user_id = $item->user_id;
        $user = App\Models\Seller::getById($seller_id);
        if (!empty($user)) {
            $c2c_name = $user->name;


            if (!empty($c2c_name)) {
                $item->c2c_name = $c2c_name;
            }

        }
        $way = $item->pay_method;

        if ($way == "bank") {
            $item->way_name2 = "银行卡";
        }
        if ($way == "we_chat") {
            $item->way_name2 = "微信";
        }
        if ($way == "ali_pay") {
            $item->way_name2 = "支付宝";  //'bank','we_chat','ali_pay'
        }
        $create_date = $item->create_date;
        if (strlen($create_date) >= 18) {
            $item->create_date = substr($create_date, 0, 16);
        }

        $type = $item->type;
        if (!empty($type)) {
            if ($type == "buy") {//‘买’ 代表商家卖币 则绑定商家银行卡
                $userCashInfo = UserCashInfo::getById($seller_id);
                $bank_account = $userCashInfo->bank_account;
                $len = strlen($bank_account);
                $bank_account = substr($bank_account, $len - 4);
                $bank_account = "*" . $bank_account;
                $userCashInfo->bank_account = $bank_account;
                $item->userCashInfo = $userCashInfo;
            }
            if ($type == "sell") {//‘卖’ 代表商家买币 则绑定用户银行卡
                $userCashInfo = UserCashInfo::getById($user_id);
                $bank_account = $userCashInfo->bank_account;
                $len = strlen($bank_account);
                $bank_account = substr($bank_account, $len - 4);
                $bank_account = "*" . $bank_account;
                $userCashInfo->bank_account = $bank_account;
                $item->userCashInfo = $userCashInfo;
            }
        }


        return $this->success($item);
    }

    //商家发布的出售或者求购列表  2023-07-27 21:03
    public function merchantPublishList(Request $request)
    {
        $currency_id = $request->input('currency_id', null);
        $type = $request->input('type', null);
        //$was_done =  $request->input('was_done',null);
        $limit = $request->input('limit', 10);
        $id = Users::getUserId();
        $user = Users::find($id);
        // $seller = Seller::where('user_id', $id)->first();

        if (empty($user)) {
            return $this->error('用户不存在');
        }
        $lists = C2CInfo::where('seller_id', $id)->where('is_done', '<', 2);
        //是否完成
        // if ($was_done == 'true') {
        //     $lists = $lists->where('is_done','=','1');
        // } elseif ($was_done == 'false') {
        //     $lists = $lists->where('is_done','=','0');
        // }
        //出售还是购买
        if ($type == 'buy') {
            $type = 'buy';
            $lists = $lists->where('type', $type);
        } elseif ($type == 'sell') {
            $type = 'sell';
            $lists = $lists->where('type', $type);
        }
        if ($currency_id) {
            $lists = $lists->where('currency_id', $currency_id);
        }
        $lists = $lists->whereDoesntHave('legalDeal', function ($query) {
            $query->where('is_sure', '=', 1);
        });

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $result = array('data' => $lists->items(),
            'type' => $type,
            'page' => $lists->currentPage(),
            'pages' => $lists->lastPage(),
            'total' => $lists->total());


        return $this->success($result);
    }

    public function getCurrentUserInfo()
    {
        $user_id = Users::getUserId();
        $has_user = Seller::where('user_id', $user_id)->where('currency_id', 23)->first();
        $isMerchant = 0;
        if (!empty($has_user)) {
            $isMerchant = 1;
        }
        $jo["isMerchant"] = $isMerchant;
        return $this->success($jo);
    }

}