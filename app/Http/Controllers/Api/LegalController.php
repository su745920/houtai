<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Users, Setting, UsersWallet, WalletSetting};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App;

class LegalController extends Controller
{
    public function legalConvertEn2GBP()
    {
        $lang=request()->input("lang","en");
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $currency = request()->input('currency', '');
        $user_id = Users::getUserId();
        $currency_id = 63;
        $settingVO=7.19;
        $info = "";
        $enInfo="";

        if ($currency=="100"){
            $settingVO=Setting::getValueByKey('usd');
            $info = "从USDT转换美元";
            $enInfo="Convert USD From USDT";
        } else if ($currency=="101"){

            $settingVO=Setting::getValueByKey('eur');
            $info = "从USDT转换欧元";
            $enInfo="Convert EUR From USDT";
        } else if ($currency=="102"){

            $settingVO=Setting::getValueByKey('gbp');
            $info = "将GBP转换为USDT";
            $enInfo="Convert GBP From USDT";
        }



        $amount = request()->get("number", '');


        $gbpAmount = bc_mul($amount, $settingVO);

        try {
            $enmemo="";
            DB::beginTransaction();
            //用户法币钱包
            $legalwallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency)
                ->first();
            if (!$legalwallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví tiền fiat không tồn tại');
                }else{
                    throw new \Exception('资金法币钱包不存在');
                }

            }

            $result = change_wallet_balance($legalwallet, 0,$gbpAmount, AccountLog::WALLET_LEGAL_OUT,$info,$enInfo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            //用户USDT钱包
            $usdtWallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', 23)
                ->first();
            if (!$usdtWallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví USDT không tồn tại');
                }else{
                    throw new \Exception('资金USDT钱包不存在');
                }

            }

            $result = change_wallet_balance($usdtWallet,0, -$amount, AccountLog::WALLET_LEGAL_IN, $info,$enInfo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();


            if ($lang=="vi"){
                return $this->success('Chuyển đổi thành công');
            }else if ($lang=="en"){
                return $this->success('Transfer successful');
            }else if ($lang=="hk"){
                return $this->success('劃轉成功');
            }else if ($lang=="id"){
                return $this->success('Transfer berhasil');
            }else if ($lang=="th"){
                return $this->success('ความสำเร็จในการขนส่ง');
            }else if ($lang=="jp"){
                return $this->success('スクロール成功');
            }else if ($lang=="kor"){
                return $this->success('회전 성공');
            }
            else {
                return $this->success('划转成功');
            }

        } catch (\Exception $e) {
            DB::rollBack();
          
            $czsb=trans('wallet.czsb');
            return $this->error($czsb . $e->getMessage());
        }

    }


    public function legalConvertEn2USDT()
    {
        $lang=request()->input("lang","en");
        
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        
        $currency = request()->input('currency', '');
        $user_id = Users::getUserId();
        $currency_id = 63;
        $settingVO=7.19;
        $info = "";
        $enInfo="";
        if ($currency=="100"){
            $settingVO=Setting::getValueByKey('usd');
            $info = "USD转换为USDT";
            $enInfo="USD Convert to USDT";
        } else if ($currency=="101"){
            $settingVO=Setting::getValueByKey('eur');
            $info = "EUR转换为USDT";
            $enInfo="EUR Convert to USDT";
        } else if ($currency=="102"){
            $settingVO=Setting::getValueByKey('gbp');
            $info = "将GBP转换为USDT";
            $enInfo="GBP Convert to USDT";
        }
        $amount = request()->get("number", '');
        $usdtAmount = bc_div($amount, $settingVO);
        try {
            $enmemo="";
            DB::beginTransaction();
            //用户法币钱包
            $legalwallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency)
                ->first();
            if (!$legalwallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví USDT không tồn tại');
                }else{
                    throw new \Exception('资金法币钱包不存在');
                }


            }
            //throw new \Exception('资金法币钱包不存在'.$amount);
            $result = change_wallet_balance($legalwallet, 0, -$amount, AccountLog::WALLET_LEGAL_OUT,$info,$enInfo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            //用户USDT钱包
            $usdtWallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', 23)
                ->first();
            if (!$usdtWallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví USDT không tồn tại');
                }else{
                    throw new \Exception('资金USDT钱包不存在');
                }

            }

            $result = change_wallet_balance($usdtWallet,0, $usdtAmount, AccountLog::WALLET_LEGAL_IN,$info, $enInfo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();

            if ($lang=="vi"){
                return $this->success('Chuyển đổi thành công');
            }else if ($lang=="en"){
                return $this->success('Transfer successful');
            }else if ($lang=="hk"){
                return $this->success('劃轉成功');
            }else if ($lang=="id"){
                return $this->success('Transfer berhasil');
            }else if ($lang=="th"){
                return $this->success('ความสำเร็จในการขนส่ง');
            }else if ($lang=="jp"){
                return $this->success('スクロール成功');
            }else if ($lang=="kor"){
                return $this->success('회전 성공');
            }
            else {
                return $this->success('划转成功');
            }
        } catch (\Exception $e) {
            DB::rollBack();
           
            $czsb=trans('wallet.czsb');
            return $this->error($czsb . $e->getMessage());
            
        }

    }


    public function getEnLegalVal(){
        $currency = request()->input('currency', '');
        $user_id = Users::getUserId();

        $settingVO=null;
        if ($currency=="100"){
            $settingVO=Setting::getValueByKey('usd');
        } else if ($currency=="101"){

            $settingVO=Setting::getValueByKey('eur');
        }else if($currency=="102"){

            $settingVO=Setting::getValueByKey('gbp');
        }

        $userWallet= UsersWallet::where('currency', $currency)->where('user_id',$user_id)->first();
        $legal_balance=$userWallet->legal_balance;
        //
        $usdtWallet= UsersWallet::where('currency', 23)->where('user_id',$user_id)->first();
        $usdt_balance=$usdtWallet->legal_balance;


        $jo["legal_balance"]=$legal_balance;
        $jo["huilv"]=$settingVO;
        $jo["usdt_balance"]=$usdt_balance;

        return $this->success($jo);

    }


    public function legalConvert2CNY()
    {
        $lang = request()->input('lang', 'en');
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $currency_id = 63;
        $settingVO=7.19;
        $info = "";
        $enInfo="";

        if ($lang=="zh"||$lang=="en"){
            $currency_id=63;
            $settingVO=Setting::getValueByKey('rmb');
            $enInfo="法币转USDT";
        } else if ($lang=="vi"){
            $currency_id=81;
            $settingVO=Setting::getValueByKey('vnd');
            $enInfo="Quy đổi USDT";
        } else if ($lang=="th"){
            $currency_id=82;
            $settingVO=Setting::getValueByKey('thb');
            $enInfo="ฟรังก์ ไปยัง USDT";
        }else if($lang=="id"){
            $currency_id=84;
            $settingVO=Setting::getValueByKey('idr');
            $enInfo="Uang Legal ke USDT";
        }



        $amount = request()->get("number", '');




        $usdtAmount = bc_mul($amount, $settingVO);





        try {
            $enmemo="";
            DB::beginTransaction();
            //用户法币钱包
            $legalwallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$legalwallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví tiền fiat không tồn tại');
                }else{
                    throw new \Exception('资金法币钱包不存在');
                }


            }

            $result = change_wallet_balance($legalwallet, 0,$usdtAmount, AccountLog::WALLET_LEGAL_OUT,$enInfo,$enmemo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            //用户USDT钱包
            $usdtWallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', 23)
                ->first();
            if (!$usdtWallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví USDT không tồn tại');
                }else {
                    throw new \Exception('资金USDT钱包不存在');
                }
            }

            $result = change_wallet_balance($usdtWallet,0, -$amount, AccountLog::WALLET_LEGAL_IN, $enInfo,$enmemo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();

            if ($lang=="vi"){
                return $this->success('Chuyển đổi thành công');
            }else if ($lang=="en"){
                return $this->success('Transfer successful');
            }else if ($lang=="hk"){
                return $this->success('劃轉成功');
            }else if ($lang=="id"){
                return $this->success('Transfer berhasil');
            }else if ($lang=="th"){
                return $this->success('ความสำเร็จในการขนส่ง');
            }else if ($lang=="jp"){
                return $this->success('スクロール成功');
            }else if ($lang=="kor"){
                return $this->success('회전 성공');
            }
            else {
                return $this->success('划转成功');
            }
        } catch (\Exception $e) {
            DB::rollBack();
           $czsb=trans('wallet.czsb');
            return $this->error($czsb . $e->getMessage());
        }

    }

    public function getLegalVal(){
        $lang = request()->input('lang', 'en');
        $user_id = Users::getUserId();
        $currency=63;//CNY
        $settingVO=null;
        if ($lang=="vi"){
            $currency=81;
            $settingVO=Setting::getValueByKey('vnd');
        } else if ($lang=="th"){
            $currency=82;
            $settingVO=Setting::getValueByKey('thb');
        }else if($lang=="id"){
            $currency=83;
            $settingVO=Setting::getValueByKey('idr');
        }else{
            $settingVO=Setting::getValueByKey('rmb');
        }

        if ($lang=="en"){
            $currency=request()->input('currencyId', '');
        }

        $userWallet= UsersWallet::where('currency', $currency)->where('user_id',$user_id)->first();
        $legal_balance=$userWallet->legal_balance;
        //
        $usdtWallet= UsersWallet::where('currency', 23)->where('user_id',$user_id)->first();
        $usdt_balance=$usdtWallet->legal_balance;


        $jo["legal_balance"]=$legal_balance;
        $jo["huilv"]=$settingVO;
        $jo["usdt_balance"]=$usdt_balance;

        return $this->success($jo);

    }
    public function legalConvert2USDT()
    {
        $lang = request()->input('lang', 'en');
        
        if ($lang) {
            if ($lang == 'zh') {
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        
        $user_id = Users::getUserId();
        $currency_id = 63;
        $settingVO=7.19;
        $info = "";
        $enInfo="";

        if ($lang=="zh"||$lang=="en"){
            $currency_id=63;
            $settingVO=Setting::getValueByKey('rmb');
            $info="法币转USDT";
            $enInfo = "Conversion of fiat currency to USDT";
        } else if ($lang=="vi"){
            $currency_id=81;
            $settingVO=Setting::getValueByKey('vnd');
            $info = "Quy đổi USDT";
            $enInfo="USDT Conversion";
        } else if ($lang=="th"){
            $currency_id=82;
            $settingVO=Setting::getValueByKey('thb');
            $info = "ฟรังก์ ไปยัง U";
            $enInfo="Franc to USDT";
        }else if($lang=="id"){
            $currency_id=84;
            $settingVO=Setting::getValueByKey('idr');
            $info="Uang Legal ke USDT";
            $enInfo="Legal Money to USDT";
        }



        $amount = request()->get("number", '');

        $usdtAmount = bc_div($amount, $settingVO);
        $lang=request()->input("lang","en");




        try {
            $enmemo="";
            DB::beginTransaction();
            //用户法币钱包
            $legalwallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', $currency_id)
                ->first();
            if (!$legalwallet) {
                if ($lang=="vi"){
                    throw new \Exception('Ví tiền fiat không tồn tại');
                }else{
                    throw new \Exception('资金法币钱包不存在');
                }

            }
            //throw new \Exception('资金法币钱包不存在'.$amount);
            $result = change_wallet_balance($legalwallet, 0, -$amount, AccountLog::WALLET_LEGAL_OUT,$info,$enInfo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            //用户USDT钱包
            $usdtWallet = UsersWallet::where('user_id', $user_id)
                ->lockForUpdate()
                ->where('currency', 23)
                ->first();
            if (!$usdtWallet) {
                throw new \Exception('资金USDT钱包不存在');
            }

            $result = change_wallet_balance($usdtWallet,0, $usdtAmount, AccountLog::WALLET_LEGAL_IN, $info,$enInfo);
            if ($result !== true) {
                throw new \Exception($result);
            }
            DB::commit();

            if ($lang=="vi"){
                return $this->success('Chuyển đổi thành công');
            }else if ($lang=="en"){
                return $this->success('Transfer successful');
            }else if ($lang=="hk"){
                return $this->success('劃轉成功');
            }else if ($lang=="id"){
                return $this->success('Transfer berhasil');
            }else if ($lang=="th"){
                return $this->success('ความสำเร็จในการขนส่ง');
            }else if ($lang=="jp"){
                return $this->success('スクロール成功');
            }else if ($lang=="kor"){
                return $this->success('회전 성공');
            }
            else {
                return $this->success('划转成功');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $czsb=trans('wallet.czsb');
            return $this->error($czsb . $e->getMessage());
        }

    }


}