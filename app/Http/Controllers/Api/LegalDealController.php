<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{AccountLog, Currency, LegalDeal, LegalDealSend, Seller, Users, UsersWallet, UserReal, UserCashInfo, Setting, SellerAccountLog};
use Illuminate\Support\Facades\Cache;
use App;

class LegalDealController extends Controller
{
    /**
     * 商家发布法币交易信息
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSend(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $type = $request->input('type', null);
        $way = $request->input('way', null);
        $price = $request->input('price', null);
        $total_number = $request->input('total_number', null);
        $min_number = $request->input('min_number', null);
        $currency_id = $request->input('currency_id', null);
        $max_number = $request->input('max_number', $total_number);
        $coin_code = strtoupper($request->input('coin_code', ''));
        $pay_ways = $request->input('pay_ways', []); //支付方式 多选
        $user_id = Users::getUserId();
        if (empty($coin_code) || !in_array($coin_code, ['CNY', 'USD', 'JPY'])) {
            return $this->error(trans('legaldeal.hbdmwx'));
        }
        if (empty($type)) return $this->error(trans('legaldeal.qxzxqlx'));
        if (empty($price)) return $this->error(trans('legaldeal.qtxdj'));
        if (empty($total_number)) return $this->error(trans('legaldeal.qtxsl'));
        if (empty($min_number)) return $this->error(trans('legaldeal.qtxzxjysl'));
        if (empty($currency_id)) return $this->error(trans('legaldeal.qxzbz'));
        if ($min_number > $total_number) return $this->error(trans('legaldeal.zxjyslbndyzsl'));

        if (empty($max_number)) {
            return $this->error(trans('legaldeal.qtxzdjyl'));
        }
        if ($max_number > $total_number || $max_number <= 0 || !is_numeric($max_number)) {
            return $this->error(trans('legaldeal.qtxzqdzdjyl'));
        }
        try {
            DB::BeginTransaction();
            $user_id = Users::getUserId();
            $seller = Seller::lockForUpdate()
                ->where('user_id', $user_id)
                ->where('currency_id', $currency_id)
                ->first();
            if (empty($seller)) {
                throw new \Exception(trans('legaldeal.dbqnbsgfbsj'));
            }
            $fee = 0;
            if ($type == 'sell') {   //如果商家发布出售信息
                $legal_sell_fee = Setting::getValueByKey('legal_sell_fee', 0);
                $legal_sell_fee = bc_div($legal_sell_fee, 100);
                $fee = bc_mul($total_number, $legal_sell_fee);
                $should_deduct_number = bc_add($total_number, $fee);
                if ($seller->seller_balance < $should_deduct_number) {
                    throw new \Exception(trans('legaldeal.dbqndsjzhbz'));
                }
                change_seller_balance(
                    $seller,
                    -$total_number,
                    AccountLog::LEGAL_DEAL_SEND_SELL,
                    "法币交易:商家发布法币出售减少"
                );
                change_seller_balance(
                    $seller,
                    $total_number,
                    AccountLog::LEGAL_DEAL_SEND_SELL,
                    "法币交易:商家发布法币出售,冻结增加",
                    true
                );
                if (bc_comp_zero($seller, 0) > 0) {
                    change_seller_balance(
                        $seller,
                        -$fee,
                        AccountLog::LEGAL_TRADE_FREE,
                        "法币交易:商家发布法币出售,扣除手续费"
                    );
                }
            }
            $legal_deal_send_data = [
                'seller_id' => $seller->id,
                'currency_id' => $currency_id,
                'type' => $type,
                'way' => $way,
                'price' => $price,
                'total_number' => $total_number,
                'surplus_number' => $total_number,
                'min_number' => $min_number,
                'max_number' => $max_number,
                'coin_code' => $coin_code,
                'out_fee' => $fee,
                'create_time' => time(),
                'update_time' => time(),
            ];
            $legal_send = LegalDealSend::unguarded(function () use ($legal_deal_send_data) {
                return LegalDealSend::create($legal_deal_send_data);
            });
            throw_unless(isset($legal_send->id), new \Exception(trans('legaldeal.fbsb')));
            DB::commit();
            return $this->success(trans('legaldeal.fbcg'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 商家详情信息
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerInfo(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        $type = $request->input('type', null);
        $was_done = $request->get('was_done', null);
        $is_done = $request->input('is_done', -1);
        $limit = $request->input('limit', 10);

        if (empty($id)) return $this->error(trans('common.cscw'));
        $seller = Seller::find($id);
        if (empty($seller)) return $this->error(trans('legaldeal.sjxxyw'));
        $beforeThirtyDays = Carbon::today()->subDay(30)->timestamp;   //30天前
        $results = Seller::withCount(['legalDeal as total', 'legalDeal as done' => function ($query) {
            $query->where('is_sure', 1);
        }, 'legalDeal as thirtyDays' => function ($query) use ($beforeThirtyDays) {
            $query->where('is_sure', 1)->where('update_time', '>=', $beforeThirtyDays);
        }])->find($id);

        $lists = LegalDealSend::where('seller_id', $id);
        if ($is_done > -1) {
            $lists = $lists->where('is_done', $is_done);
        } else {
            //是否完成
            if ($was_done == 'true') {
                $lists = $lists->where('is_done', 1);
            } elseif ($was_done == 'false') {
                $lists = $lists->where('is_done', 0);
            }
        }
        //出售还是购买
        if ($type == 'buy') {
            $type = 'buy';
            $lists = $lists->where('type', $type);
        } elseif ($type == 'sell') {
            $type = 'sell';
            $lists = $lists->where('type', $type);
        }

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $results->lists = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        //追加用户余额
        $wallet = UsersWallet::where('currency', $seller->currency_id)->where('user_id', $seller->user_id)->first();
        $results->user_legal_balance = $wallet->legal_balance ?? 0;
        return $this->success($results);
    }

    public function tradeList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $type = $request->input('type', null);
        $is_done = $request->input('is_done', -1);
        $seller_id = $request->input('id', 0);
        $limit = $request->input('limit', 10);

        $user_id = Users::getUserId();
        $seller = Seller::where('user_id', $user_id)
            ->where('id', $seller_id)
            ->first();

        if (empty($seller)) {
            return $this->error(trans('legaldeal.nbssj'));
        }
        $lists = LegalDealSend::where('seller_id', $seller->id);
        //是否完成
        if ($is_done > -1) {
            $lists = $lists->where('is_done', $is_done);
        }
        //出售还是购买
        if ($type == 'buy') {
            $type = 'buy';
            $lists = $lists->where('type', $type);
        } elseif ($type == 'sell') {
            $type = 'sell';
            $lists = $lists->where('type', $type);
        }

        $lists = $lists->orderBy('id', 'desc')->paginate($limit);
        $result = array('data' => $lists->items(), 'page' => $lists->currentPage(), 'pages' => $lists->lastPage(), 'total' => $lists->total());
        return $this->success($result);
    }


    /**
     * 商家发布法币交易信息列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealPlatform(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $currency_id = $request->input('currency_id', '');
        $type = $request->input('type', 'sell');
        if (empty($currency_id)) return $this->error(trans('common.cscw'));
        if (empty($type)) return $this->error(trans('common.cscw'));
        $currency = Currency::find($currency_id);
        if (empty($currency)) return $this->error(trans('currency.wcbz'));
        if (empty($currency->is_legal)) return $this->error(trans('currency.gbbsfb'));

        $results = LegalDealSend::where('currency_id', $currency_id)
            ->where('is_shelves', 1)
            ->where('is_done', 0)
            ->where('type', $type)
            ->where('surplus_number', '>', 0)
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->pageDate($results);
    }

    /**
     * 法币交易详情
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealSendInfo(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error(trans('common.cscw'));
        }
        $legal_deal_send = LegalDealSend::find($id);

        $userWallet = UsersWallet::where('currency', $legal_deal_send->currency_id)
            ->where('user_id', Users::getUserId())->first();

        //加上用户的余额
        $legal_deal_send->user_legal_balance = $userWallet->legal_balance;

        if (empty($legal_deal_send)) return $this->error(trans('legaldeal.wcjl'));
        return $this->success($legal_deal_send);
    }

    /**
     * 法币交易按钮
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function doDeal(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $deal_send_id = $request->input('id', null);
        $value = $request->input('value', 0);
        $means = $request->input('means', '');
        if (empty($deal_send_id)) {
            return $this->error(trans('common.cscw'));
        }
        $user_id = Users::getUserId();
        //实名认证检测
        $user_real = UserReal::where('user_id', $user_id)
            ->where('review_status', 2)
            ->first();
        if (!$user_real) {
            return response()->json(['type' => '998', 'message' => trans('legaldeal.nhmytgsmrz')]);
        }
        //收款信息检测
        $user_cash_info = UserCashInfo::where('user_id', $user_id)->first();
        if (!$user_cash_info) {
            return response()->json(['type' => '997', 'message' => trans('legaldeal.nhmyszskxx')]);
        }

        if (!in_array($means, ['number', 'money'])) {
            return $this->error(trans('legaldeal.gmcscw'));
        }

        if (empty($value)) {
            return $this->error(trans('legaldeal.qtxgme'));
        }
        if (!is_numeric($value)) {
            return $this->error(trans('legaldeal.gmeqtxsz'));
        }

        //限制未完成单数最多为3单
        $is_morethan = LegalDeal::where("user_id", "=", $user_id)->whereIn('is_sure', [0, 3])->count();
        if ($is_morethan >= 3) {
            return $this->error(trans('legaldeal.wwcdzcgsdqwchzcz'));
        }
        try {
            DB::beginTransaction();
            $fee = 0;
            $legal_deal_send = LegalDealSend::lockForUpdate()->find($deal_send_id);
            if (empty($legal_deal_send)) {
                throw new \Exception(trans('legaldeal.wcjl'));
            }
            if ($legal_deal_send->is_shelves != 1 || $legal_deal_send->is_done != 0) {
                throw new \Exception(trans('legaldeal.nhmyszskxx'));
            }
            if (bc_comp_zero($legal_deal_send->surplus_number) <= 0) {
                throw new \Exception(trans('legaldeal.sjgdsykjyslbz'));
            }
            if ($means == 'money') {
                $number = bc_div($value, $legal_deal_send->price, 5);
            } else {
                $number = $value;
            }
            if ($number <= 0) {
                throw new \Exception(trans('legaldeal.fftjslbxdyl'));
            }
            $money = bc_mul($number, $legal_deal_send->price, 5);
            if (bc_comp($legal_deal_send->surplus_number, $legal_deal_send->min_number) >= 0) {
                // 如果剩余数量小于最小交易额则不限制
                if (bc_comp($money, $legal_deal_send->limitation['min']) < 0) {
                    throw new \Exception(trans('legaldeal.ndyzdxe'));
                }
            } else {
                $min_money =  bc_mul($legal_deal_send->surplus_number, $legal_deal_send->price, 5);
                if ($money < $min_money) {
                    throw new \Exception(trans('legaldeal.nbmzzdxeyq'));
                }
            }
            if ($money > $legal_deal_send->limitation['max']) {
                throw new \Exception(trans('legaldeal.ngyzdxe'));
            }
            if ($number > $legal_deal_send->max_number) {
                throw new \Exception(trans('legaldeal.ngydzxesl'));
            }
            $seller = Seller::find($legal_deal_send->seller_id);
            if (empty($seller)) {
                throw new \Exception(trans('legaldeal.wzdgsj'));
            }

            if ($user_id == $seller->user_id) {
                throw new \Exception(trans('legaldeal.bnhzjjy'));
            }
            $users_wallet = UsersWallet::where('user_id', $user_id)
                ->where('currency', $legal_deal_send->currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($users_wallet)) {
                throw new \Exception(trans('legaldeal.nwcqbzh'));
            }
            if (!empty($users_wallet->status)) {
                throw new \Exception(trans('legaldeal.ndqbybsdqlxgly'));
            }

            //检查购买数量是否大于剩余余额
            if ($number > $legal_deal_send->surplus_number) {
                throw new \Exception(trans('legaldeal.njysldysjfbdsysl'));
            }
            if ($legal_deal_send->type == 'buy') {
                // 商家求购,用户卖出
                // $hasNonDone = LegalDeal::where('user_id', $user_id)
                //     ->whereIn('is_sure', [0, 3])
                //     ->first();
                // if($hasNonDone) {
                //     throw new \Exception('检测有未完成交易，请完成后再来！');
                // }
                // 卖出方收取手续费
                $legal_sell_fee = Setting::getValueByKey('legal_sell_fee', 0);
                $legal_sell_fee = bc_div($legal_sell_fee, 100);
                $fee = bc_mul($number, $legal_sell_fee);
                $should_deduct_number = bc_add($number, $fee);
                if ($users_wallet->legal_balance < $should_deduct_number) {
                    throw new \Exception(trans('legaldeal.yhfbyebz'));
                }
                if ($users_wallet->lock_legal_balance < 0) {
                    throw new \Exception(trans('legaldeal.ndfbdjzjyc'));
                }
                change_wallet_balance(
                    $users_wallet,
                    1,
                    -$number,
                    AccountLog::LEGAL_DEAL_USER_SELL,
                    '法币交易:出售给商家法币:扣除余额',
                    'Legal currency transaction:Legal currency sold to merchants:Deduction balance'
                );
                change_wallet_balance(
                    $users_wallet,
                    1,
                    $number,
                    AccountLog::LEGAL_DEAL_USER_SELL,
                    '法币交易:售给商家法币:增加冻结',
                    'Legal currency transaction:Legal currency sold to merchants:Add freeze',
                    true
                );
                //扣除手续费
                if (bc_comp($fee, '0') > 0) {
                    change_wallet_balance(
                        $users_wallet,
                        1,
                        -$fee,
                        AccountLog::LEGAL_TRADE_FREE,
                        '法币交易:出售给商家法币,扣除手续费',
                        'Legal currency transaction:Legal currency sold to merchants,Deduct handling charges'
                    );
                }
            }
            // 这里只下架交易,因为商家发布的交易数量只是被匹配到的用户占用了，并不是交易全都完成了
            $legal_deal_send->surplus_number = bc_sub($legal_deal_send->surplus_number, $number, 8);
            // $legal_deal_send->surplus_number <= 0 &&  $legal_deal_send->is_shelves = 2; // 有争议
            $legal_deal_send->save();
            $legal_deal = new LegalDeal();
            $legal_deal->legal_deal_send_id = $deal_send_id;
            $legal_deal->user_id = $user_id;
            $legal_deal->seller_id = $seller->id;
            $legal_deal->number = $number; //交易数量
            $legal_deal->out_fee = $fee;
            $legal_deal->create_time = time();
            $legal_deal->update_time = time();
            $legal_deal->save();
            DB::commit();
            return $this->success([
                'msg' => trans('legaldeal.czcgqlssjqrdd'),
                'data' => $legal_deal,
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage() . trans('legaldeal.cwwydnh',['line' => $exception->getLine()]));
        }
    }

    /**
     * 法币交易商家端列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sellerLegalDealList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $type = $request->input('type', 'sell');
        $currency_id = $request->input('currency_id', '');

        if (empty($currency_id)) {
            return $this->error(trans('common.cscw'));
        }
        if (empty($type)) {
            return $this->error(trans('common.cscw'));
        }
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error(trans('currency.wcbz'));
        }
        if (empty($currency->is_legal)) {
            return $this->error(trans('currency.gbbsfb'));
        }
        $user_id = Users::getUserId();
        $seller = Seller::where('user_id', $user_id)->where('currency_id', $currency_id)->first();
        if (empty($seller)) {
            return $this->error(trans('legaldeal.dbqnbsgfbsj'));
        }
        $results = LegalDeal::where('seller_id', $seller->id)
            ->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            })->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->pageDate($results);
    }

    /**
     * 法币交易用户端列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $type = $request->input('type', null);
        $currency_id = $request->input('currency_id', '');
        $is_sure = $request->input('is_sure', null); //0未确认 1已确认 2已取消 3已付款
        if (!empty($currency_id)) {
            $currency = Currency::find($currency_id);
            if (empty($currency)) return $this->error(trans('currency.wcbz'));
            if (empty($currency->is_legal)) return $this->error(trans('currency.gbbsfb'));
        }
        $user_id = Users::getUserId();
        $results = LegalDeal::where('user_id', $user_id);
        if (!empty($type)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($type) {
                $query->where('type', $type);
            });
        }
        if (!empty($currency_id)) {
            $results = $results->whereHas('legalDealSend', function ($query) use ($currency_id) {
                $query->where('currency_id', $currency_id);
            });
        }
        if (!is_null($is_sure)) {
            $results = $results->where('is_sure', $is_sure);
        }
        $results = $results->orderBy('id', 'desc')->paginate($limit);
        return $this->pageDate($results);
    }

    /**
     * 订单详情页
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function legalDealInfo(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error(trans('currency.wcbz'));
        }
        $legal_deal = LegalDeal::find($id);
        if (empty($legal_deal)) {
            return $this->error(trans('legaldeal.wcjl'));
        }
        return $this->success($legal_deal);
    }

    /**
     * 用户确认支付
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealPay(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);

        DB::beginTransaction();
        try {
            if (empty($id)) return $this->error(trans('common.cscw'));
            $legal_deal = LegalDeal::lockForUpdate()->find($id);
            if (empty($legal_deal)) {
                throw new \Exception(trans('legaldeal.wcjl'));
            }
            if ($legal_deal->is_sure > 0) {
                throw new \Exception(trans('legaldeal.gddyczgqwcfcz'));
            }
            $user_id = Users::getUserId();
            if ($legal_deal->type == 'sell') { //用户端-购买
                if ($user_id != $legal_deal->user_id) {
                    throw new \Exception(trans('legaldeal.dbqnwqcz'));
                }
            } elseif ($legal_deal->type == 'buy') {
                $seller = Seller::find($legal_deal->seller_id);
                if ($user_id != $seller->user_id) {
                    throw new \Exception(trans('legaldeal.dbqnwqcz'));
                }
            }
            $legal_deal->is_sure = 3;
            $legal_deal->save();
            DB::commit();
            return $this->success(trans('legaldeal.czcgqlssjqrdd'));
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 取消交易(限买方,超时不付款将自动取消)
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userLegalDealCancel(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error(trans('common.cscw'));
        }

        try {
            DB::beginTransaction();
            $legal_deal = LegalDeal::lockForUpdate()->find($id);
            if (empty($legal_deal)) {
                return $this->error(trans('legaldeal.wcjl'));
            }
            if ($legal_deal->is_sure > 0) {
                throw new \Exception(trans('legaldeal.gddyczqwqx'));
            }
            $user_id = Users::getUserId();
            if ($legal_deal->type == 'sell') {
                //用户端-购买
                if ($user_id != $legal_deal->user_id) {
                    throw new \Exception(trans('legaldeal.dbqnwqcz'));
                }
            } elseif ($legal_deal->type == 'buy') {
                //用户端出售
                $seller = Seller::find($legal_deal->seller_id);
                if ($user_id == $legal_deal->user_id) {
                    throw new \Exception(trans('legaldeal.dbqnwqcz'));
                }
            }
            LegalDeal::cancelLegalDealById($id);
            DB::commit();
            return $this->success(trans('legaldeal.czcgddyqx'));
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    public function mySellerList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $user_id = Users::getUserId();
        $user = Users::find($user_id);
        if (empty($user->is_seller)) {
            return $this->error(trans('legaldeal.nbssj'));
        }
        $results = Seller::where('user_id', $user_id)->orderBy('id', 'desc')->paginate($limit);
        // foreach ($results->items() as &$value) {
        //     $wallet = UsersWallet::where('currency', $value->currency_id)->where('user_id', $value->user_id)->first();
        //     $value->user_legal_balance = $wallet->legal_balance ?? 0;
        // }

        return $this->pageDate($results);
    }

    public function legalDealSellerList(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $id = $request->input('id', null);
        $is_sure = $request->input('is_sure', null);
        if (empty($id)) return $this->error(trans('common.cscw'));
        $legal_send = LegalDealSend::find($id);
        if (empty($legal_send)) {
            return $this->error(trans('common.cscw'));
        }
        $seller = Seller::find($legal_send->seller_id);
        if (empty($seller->is_myseller)) {
            return $this->error(trans('legaldeal.nbssj'));
        }
        if (!is_null($is_sure)) {
            $results = LegalDeal::where('legal_deal_send_id', $id)
                ->where('is_sure', $is_sure)
                ->orderBy('id', 'desc')
                ->paginate($limit);
        } else {
            $results = LegalDeal::where('legal_deal_send_id', $id)
                ->orderBy('id', 'desc')
                ->paginate($limit);
        }
        return $this->pageDate($results);
    }

    /**
     * 商家确认交易
     *
     * @param Request $request
     * @return void
     */
    public function doSure(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        $user_id = Users::getUserId();
        try {
            DB::beginTransaction();
            throw_if(empty($id), new \Exception(trans('common.cscw')));
            $legal_deal = LegalDeal::lockForUpdate()->findOrFail($id);
            throw_if($legal_deal->seller->user_id != $user_id, new \Exception(trans('legaldeal.dbqnwqcz')));
            throw_if($legal_deal->is_sure != 3, new \Exception(trans('legaldeal.gddhwfkhzjczg')));
            $legal_send = LegalDealSend::findOrFail($legal_deal->legal_deal_send_id);
            throw_if($legal_send->type == 'buy', new \Exception(trans('legaldeal.nwqqrcdd')));
            LegalDeal::confirmLegalDealById($id);
            DB::commit();
            return $this->success(trans('legaldeal.qrcg'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            DB::rollBack();
            return $this->error($ex->getModel() . trans('common.sjwzd'));
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 用户确认交易
     *
     * @param Request $request
     * @return void
     */
    public function userDoSure(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        $user_id = Users::getUserId();
        try {
            DB::beginTransaction();
            throw_if(empty($id), new \Exception(trans('common.cscw')));
            $legal_deal = LegalDeal::lockForUpdate()->findOrFail($id);
            throw_if($legal_deal->user_id != $user_id, new \Exception(trans('legaldeal.dbqnwqcz')));
            throw_if($legal_deal->is_sure != 3, new \Exception(trans('legaldeal.gddhwfkhzjczg')));
            $legal_send = LegalDealSend::findOrFail($legal_deal->legal_deal_send_id);
            throw_if($legal_send->type == 'sell', new \Exception(trans('legaldeal.nwqqrcdd')));
            Seller::lockForUpdate()->findOrFail($legal_deal->seller_id);
            LegalDeal::confirmLegalDealById($id);
            DB::commit();
            return $this->success(trans('legaldeal.qrcg'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            DB::rollBack();
            return $this->error($ex->getModel . trans('common.sjwzd'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 标记交易下架
     *
     */
    public function down(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        if (empty($id)) return $this->error(trans('common.cscw'));

        try {
            DB::beginTransaction();

            $legal_send = LegalDealSend::lockForUpdate()->find($id);

            if (empty($legal_send)) {
                throw new \Exception(trans('legaldeal.wcjl'));
            }
            if ($legal_send->is_done != 0 || $legal_send->is_shelves != 1) {
                throw new \Exception(trans('legaldeal.cztwfxj'));
            }
            $user_id = Users::getUserId();
            $seller = Seller::where('user_id', $user_id)
                ->where('currency_id', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($seller)) {
                throw new \Exception(trans('legaldeal.dbqnbsgfbsj'));
            }
            $legal_send->is_shelves = 2;
            $legal_send->save();
            DB::commit();
            return $this->success(trans('legaldeal.fbxjcgjbhzyxyhpp'));
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 商家撤回发布
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function backSend(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        try {
            DB::transaction(function () use ($id) {
                $user_id = Users::getUserId();
                throw_if(empty($id), new \Exception(trans('common.cscw')));
                $legal_send = LegalDealSend::lockForUpdate()->find($id);
                $seller = $legal_send->seller;
                throw_if($seller->user_id != $user_id, new \Exception(trans('legaldeal.dbqnbsgfbsj')));
                LegalDealSend::sendBack($id, 2);
            });
            return $this->success(trans('legaldeal.chcg'));
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 异常发布撤回
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse 
     * @deprecated 20191201 该功能已被正常撤回功能取代
     */
    public function errorSend(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $id = $request->input('id', null);
        if (empty($id)) {
            return $this->error(trans('common.cscw'));
        }
        return $this->error(trans('legaldeal.ggnwfsyqxjhhchfbtd'));
        try {
            DB::beginTransaction();
            $legal_send = LegalDealSend::lockForUpdate()->find($id);
            if (empty($legal_send)) {
                throw new \Exception(trans('legaldeal.wcjl'));
            }
            if (LegalDealSend::isHasIncompleteness($id)) {
                throw new \Exception(trans('legaldeal.gfbxxxyjywwcbnbjwyc'));
            }
            if (bc_comp($legal_send->surplus_number, $legal_send->min_number) >= 0) {
                throw new \Exception(trans('legaldeal.gfbxxwyc'));
            }
            if (bc_comp($legal_send->surplus_number, 0) <= 0) {
                throw new \Exception(trans('legaldeal.gfbsysjbzwfbch'));
            }
            $user_id = Users::getUserId();
            $seller = Seller::where('user_id', $user_id)
                ->where('currency_id', $legal_send->currency_id)
                ->lockForUpdate()
                ->first();
            if (empty($seller)) {
                throw new \Exception(trans('legaldeal.dbqnbsgfbsj'));
            }
            if ($legal_send->type == 'sell') {
                // 如果商家发布出售信息
                if (bc_comp($seller->lock_seller_balance, $legal_send->surplus_number) < 0) {
                    throw new \Exception(trans('legaldeal.dbqndsjzhdjzjbz'));
                }
                change_seller_balance(
                    $seller,
                    -$legal_send->surplus_number,
                    AccountLog::LEGAL_DEAL_ERROR_SEND_SELL,
                    "法币交易:商家处理异常发布,减少冻结",
                    true
                );
                change_seller_balance(
                    $seller,
                    $legal_send->surplus_number,
                    AccountLog::LEGAL_DEAL_ERROR_SEND_SELL,
                    "法币交易:商家处理异常发布,退回余额",
                    true
                );
            }
            $legal_send->is_done = 2;
            $legal_send->save();
            DB::commit();
            return $this->success(trans('legaldeal.clcg'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 提交维权
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitArbitrate(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        try {
            DB::transaction(function () use ($request) {
                $user_id = Users::getUserId();
                $id = $request->input('id', 0);
                $legal_deal = LegalDeal::lockForUpdate()->findOrFail($id);
                throw_if($legal_deal->is_sure != 3, new \Exception(trans('legaldeal.dqztbnsqwq')));
                // 只有卖方才能申请维权
                if ($legal_deal->type == 'sell') {
                    // 卖方是商家
                    $sell_user_id = $legal_deal->seller->user_id;
                    $arbitrated_from = 2;
                } else {
                    // 卖方是用户
                    $sell_user_id = $legal_deal->user_id;
                    $arbitrated_from = 1;
                }
                throw_if($user_id != $sell_user_id, new \Exception(trans('legaldeal.dqjyztzymfcnwq')));
                $legal_deal->is_sure = 4;
                $legal_deal->arbitrated_from = $arbitrated_from;
                $legal_deal->save();
            });
            return $this->success(trans('legaldeal.tjwqcgjyydjqddhtcl'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $ex) {
            return $this->error(trans('legaldeal.jyxxbczqsxhcs'));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    /**
     * 商家余额与用户的法币余额相互划转
     * @param Request $request 
     * @return Illuminate\Http\JsonResponse 
     */
    public function transfer(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $key = 'lan_type_'.$user_id;
        $lang = Cache::get($key);
        if(!$lang){
            $lang = 'en';
        }
        $seller_id = $request->input("seller_id", 0);
        $number = $request->input("number", 0);
        $type = $request->input("type", 0); //1商家到用户  2用户到商家
        if (empty($user_id) || empty($seller_id) || empty($type)) {
            return $this->error(trans('legaldeal.cscw'));
        }
        if ($number <= 0) {
            return $this->error(trans('legaldeal.srdjebnwfs'));
        }
        if (!in_array($type, [1, 2])) {
            return $this->error(trans('legaldeal.hzlxcsyw'));
        }
        try {
            DB::beginTransaction();
            $seller = Seller::lockForUpdate()->find($seller_id);
            if (!$seller) {
                throw new \Exception(trans('legaldeal.sjxxyw'));
            }
            if ($seller->user_id != $user_id) {
                throw new \Exception(trans('legaldeal.wqcz'));
            }
            $user_wallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $seller->currency_id)
                ->first();
            if (!$user_wallet) {
                throw new \Exception(trans('legaldeal.qbbcz'));
            }
            $enmemo = '';
            if ($type == 1) {
                if ($seller->seller_balance < $number) {
                    throw new \Exception(trans('legaldeal.sjyebz'));
                }
                $user_number = $number;
                $seller_number = -$number;
                $log_type = AccountLog::SELLER_TRANSFER_USER_BALANCE;

                $memo = '商家余额划转到用户余额';
                $enmemo = 'Merchant balance transferred to user balance';
            } else if ($type == 2) {
                if ($user_wallet->legal_balance < $number) {
                    throw new \Exception(trans('legaldeal.yhfbyebz'));
                }
                $user_number = -$number;
                $seller_number = $number;
                $log_type = AccountLog::USER_TRANSFER_SELLER_BALANCE;
                $memo = '用户余额划转到商家';
                $enmemo = 'User balance transferred to merchant';
            }
            change_wallet_balance(
                $user_wallet,
                1,
                $user_number,
                $log_type,
                $memo,
                $enmemo
            );
            change_seller_balance(
                $seller,
                $seller_number,
                $log_type,
                $memo
            );
            DB::commit();
            return $this->success(trans('legaldeal.lzcg'));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(trans('legaldeal.czsb').':' . $e->getMessage());
        }
    }

    /**
     * 商家余额日志
     * @param Request $request 
     * @return Illuminate\Http\JsonResponse 
     */
    public function balanceLog(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $limit = $request->input('limit', 10);
        $seller_id = $request->input('seller_id', 0);
        $is_lock = $request->input('is_lock', -1);
        $user_id = Users::getUserId();
        $seller = Seller::find($seller_id);
        if (empty($seller) || $seller->user_id != $user_id) {
            return $this->error(trans('legaldeal.sjxxyw'));
        }
        $list = SellerAccountLog::where('seller_id', $seller_id)
            ->where('user_id', $user_id)
            ->when($is_lock > -1, function ($query) use ($is_lock) {
                $query->where('is_lock', $is_lock);
            })
            ->orderBy('id', 'desc')
            ->paginate($limit);
        return $this->pageDate($list);
    }
}
