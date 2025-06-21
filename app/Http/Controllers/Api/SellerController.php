<?php

namespace App\Http\Controllers\Api;

use App\Models\Currency;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UserReal;
use App\Models\UsersWallet;
use App\Models\AccountLog;
use App\Models\SellerAccountLog;
use Illuminate\Http\Request;
use App;

class SellerController extends Controller
{

    public function saveMerchantApply(Request $request)
    {

        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        App::setLocale($lang);
        //select * from settings t where t.key='tobe_seller_lockusdt'
        $tobe_seller_lockusdt = Setting::getValueByKey("tobe_seller_lockusdt");
        $id = $request->get('id', 0);

        $user_id = Users::getUserId();
        $user=Users::getById($user_id);
        $account_number = $user->account_number;


        $name = $request->get('name', '');
        $mobile = $request->get('mobile', '');
        $currency_id = 23;
        $seller_balance = $request->get('seller_balance', 0);
        $wechat_nickname = $request->get('wechat_nickname', '');
        $wechat_account = $request->get('wechat_account', '');
        $ali_nickname = $request->get('ali_nickname', '');
        $ali_account = $request->get('ali_account', '');
        $bank_id = $request->get('bank_id', 0);
        $bank_account = $request->get('bank_account', '');
        $bank_address = $request->get('bank_address', '');
        $alipay_qr_code = $request->get('alipay_qr_code', '');
        $wechat_qr_code = $request->get('wechat_qr_code', '');
        if (empty($account_number)) return $this->error('用户名不能为空');
        if (empty($name)) return $this->error('名称不能为空');
        if (empty($mobile)) return $this->error('电话不能为空');
        //if (empty($currency_id)) return $this->error('资产不能为空');
        //自定义验证错误信息
        $messages = [
            'required' => ':attribute 为必填字段',
        ];

//        $validator = Validator::make($request->all(), [
//            // 'account_number'=>'required',
//            // 'name'=>'required',
//            // 'mobile'=>'required',
//            // 'currency_id'=>'required',
////            'seller_balance'=>'required',
//            // 'wechat_nickname'=>'required',
//            // 'wechat_account'=>'required',
//            // 'ali_nickname'=>'required',
//            // 'ali_account'=>'required',
//            // 'bank_id'=>'required',
//            // 'bank_account'=>'required',
//            // 'bank_address'=>'required',
//            // 'alipay_qr_code'=>'required',
//            // 'wechat_qr_code'=>'required',
//        ], $messages);


        //如果验证不通过
//        if ($validator->fails()) {
//            return $this->error($validator->errors()->first());
//        }
        $self = Users::where('account_number', $account_number)->first();

        $real = UserReal::where('user_id', $self->id)->where('review_status', 2)->first();
        if (empty($real)) return $this->error('此用户还未通过实名认证');
        $currency = Currency::find(23);


        $has = Seller::where('name', $name)->where('user_id', '!=', $self->id)->where('currency_id', $currency_id)->first();
        if (empty($id) && !empty($has)) {
            return $this->error($this->returnStr('此法币') . $name . $this->returnStr('商家名称已存在'));
        }
        $has_user = Seller::where('user_id', $self->id)->where('currency_id', $currency_id)->first();
        if (!empty($has_user) && empty($id)) {
            return $this->error('此用户已是此法币商家');
        }

        if (empty($id)) {
            $acceptor = new Seller();
            $acceptor->create_time = time();
        } else {
            $acceptor = Seller::find($id);
        }
        $acceptor->user_id = $self->id;
        $acceptor->name = $name;
        $acceptor->mobile = $mobile;
        $acceptor->currency_id = $currency_id;
        $acceptor->seller_balance = floatval($seller_balance);
        $acceptor->wechat_nickname = $wechat_nickname;
        $acceptor->wechat_account = $wechat_account;
        $acceptor->ali_nickname = $ali_nickname;
        $acceptor->ali_account = $ali_account;
        $acceptor->bank_id = intval($bank_id);
        $acceptor->bank_account = $bank_account;
        $acceptor->bank_address = $bank_address;
        $acceptor->alipay_qr_code = $alipay_qr_code;
        $acceptor->wechat_qr_code = $wechat_qr_code;
        $acceptor->account_number=$account_number;

        $acceptor->audit_status=0;

        $link_email=$request->get('link_email', '');
        if (!empty($link_email)){
            $acceptor->link_email=$link_email;
        }

        try {

            //成为商家扣除usdt币并记录日志
            $usdt = Currency::where('name', 'USDT')->select(['id'])->first();
            $user_wallet = UsersWallet::where("user_id", $self->id)->where("currency", $usdt->id)->first();
            //日志开始



            //增加杠杆币日志记录
            $result = change_wallet_balance(//1.法币,2.币币交易,3.杠杆交易
                $user_wallet,
                0,
                -$tobe_seller_lockusdt,
                AccountLog::TOBE_SELLER_SUB_USDT,
                '申请成为C2C商家，扣除USDT' . -$tobe_seller_lockusdt,
                'Apply to become a C2C merchant and deduct USDT ' . -$tobe_seller_lockusdt,
                false,
                $self->id,
                0
            );
            if ($result != "true") {
                return $this->error($result);
            }

            $acceptor->save();

            return $this->success(trans('commin.tjcg'));

        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }


    }

    public function lists(Request $request)
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $limit = $request->input('limit', 10);
        $currency_id = $request->input('currency_id', 0);
        if (empty($currency_id)) {
            return $this->error(trans('common.cscw'));
        }
        $currency = Currency::find($currency_id);
        if (empty($currency)) {
            return $this->error(trans('currency.wcbz'));
        }
        if (empty($currency->is_legal)) {
            return $this->error(trans('currency.gbbsfb'));
        }
        $results = Seller::where('currency_id', $currency->id)->orderBy('id', 'desc')->paginate($limit);
        return $this->pageDate($results);
    }
}
