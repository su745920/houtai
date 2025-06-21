<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Utils\RPC;
use App\Models\{Currency,
    Address,
    AccountLog,
    Setting,
    Users,
    UsersWallet,
    UsersWalletOut,
    LeverTransaction,
    CurrencyQuotation
};
use App\Events\WithdrawSubmitEvent;
use App;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Console\Input\Input;

class WalletV2Controller extends Controller
{


    public function legalWalletList(Request $request)
    {
        $lang = request()->input('lang', 'en');
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();

        $currency_id = 63;
        $settingVO = 7.19;
        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }


        $cache_key_name = "user_wallet_data_{$user_id}";
        // if (Cache::has($cache_key_name)) {
        //     $wallet_data = Cache::get($cache_key_name);
        // } else {
        // 63,
        
        $usdtPriceList=Currency::getAllUsdtPriceV2();
        
        $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)->whereIn('currency',[23,57,81,82,84,32,35,100,101,102])
            ->whereHas('currencyCoin', function ($query) use ($currency_name) {

                empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');

            })->get();

        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'logo',
                'id', 'currency', 'currency_name', 'sort',
                'currency_type',
                'usdt_price', 'usd_price',
                'legal_balance', 'lock_legal_balance',
                'address', 'erc20_address',

            ]);
            return $item;
        });
        if ($lang == "vi") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 82, 84])->sortByDesc('legal_balance')->values()->all();
        } elseif ($lang == "th") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 81, 84])->sortByDesc('legal_balance')->values()->all();
        } elseif ($lang == "id") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63, 81, 82])->sortByDesc('legal_balance')->values()->all();
        } elseif ($lang == "zh") {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [81, 82, 84])->sortByDesc('legal_balance')->values()->all();
        } else {
            $legal_wallet['balance'] = $user_wallet->where('is_legal', 1)->whereNotIn('currency', [63,81,82,84])->sortByDesc('legal_balance')->values()->all();
        }

        $legal_wallet['totle'] = 0;
        $legal_wallet['CNY'] = 0;
        foreach ($legal_wallet['balance'] as $k => $v) {
            //$num = $v['legal_balance'] + $v['lock_legal_balance'];
            $num = $v['legal_balance'];
            $currency = $v['currency'];
            if ($v["currency"] == 23) {
                $legal_wallet['totle'] += $num;
                $legal_wallet['CNY'] += $num;
            }elseif ($v["currency"] == 57) {
                $legal_wallet['totle'] += $num;
                $legal_wallet['CNY'] += $num;
            } else {
                //$settingVO
                if ($lang == "vi") {
                    if ($currency == 81) {
                        $settingVO = Setting::getValueByKey('vnd');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    } else {
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        // $legal_wallet['totle'] += $num;
                        // $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                        $legal_wallet['CNY'] += $num;
                    }


                } else if ($lang == "th") {
                    $settingVO = Setting::getValueByKey('thb');
                    if ($currency == 82) {
                        $settingVO = Setting::getValueByKey('vnd');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    } else {
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        //  $legal_wallet['totle'] += $num;
                        // $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                        $legal_wallet['CNY'] += $num;
                    }

                } else if ($lang == "id") {

                    if ($currency == 83) {
                        $settingVO = Setting::getValueByKey('idr');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    } else {
                        $legal_wallet['totle'] += $num * $v['usdt_price'];
                        //  $legal_wallet['totle'] += $num;
                        // $legal_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
                        $legal_wallet['CNY'] += $num;
                    }
                } else {

                   if ($currency == 100) {
                        $settingVO = Setting::getValueByKey('usd');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }
                    else if ($currency == 101) {
                        $settingVO = Setting::getValueByKey('eur');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }
                    else if ($currency == 102) {
                        $settingVO = Setting::getValueByKey('gbp');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    }
                    else if ($currency == 63) {
                        $settingVO = Setting::getValueByKey('rmb');
                        $legal_wallet['totle'] += bc_div($num, $settingVO);
                        $legal_wallet['CNY'] += bc_div($num, $settingVO);
                    } else {
                        if($num > 0) {
                            $usdtVal=$this->getUsdtPriceV3($usdtPriceList,$v['currency_name']);
                            $legal_wallet['totle'] += bc_mul($num,$usdtVal, 6);
                            //  $legal_wallet['totle'] += $num;
                            $legal_wallet['CNY'] += bc_mul($num, $usdtVal, 6);
                            // $legal_wallet['CNY'] += $num;
                        }
                    }
                }
                ///


            }
        }

        $wallet_data = [
            'legal_wallet' => $legal_wallet,
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }
    
    
    
    public function spotWalletList(Request $request)
    {
        $lang = request()->input('lang', 'en');
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        //
        $currency_id = 63;
        $settingVO = 7.19;


        //


        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);

        $cache_key_name = "user_wallet_data_{$user_id}";
        // if (Cache::has($cache_key_name)) {
        //     $wallet_data = Cache::get($cache_key_name);
        // } else {
       $show0=request()->input('show0','');
        $user_wallet=null;
        if ($show0=="no") {
            $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)->where('change_balance', '>', 0)
                ->whereHas('currencyCoin', function ($query) use ($currency_name) {

                    empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');

                })->get();
        }else{
            $user_wallet = UsersWallet::with(['currencyCoin'])->where('user_id', $user_id)
                ->whereHas('currencyCoin', function ($query) use ($currency_name) {

                    empty($currency_name) || $query->where('name', 'like', '%' . $currency_name . '%');

                })->get();
        }
        

        $user_wallet->transform(function ($item, $key) {
            $item->setVisible([
                'logo',
                'id', 'currency', 'currency_name', 'sort',
                'currency_type', 'contract_address',
                'usdt_price', 'usd_price',


                'change_balance', 'lock_change_balance',

                'address', 'erc20_address',

            ]);
            return $item;
        });

        $usdtPriceList=Currency::getAllUsdtPriceV2();


        //221120 币币资产只显示余额不为0的资产（含冻结）
        $change_list = $user_wallet->where('is_match', 1)->sortByDesc("change_balance")->values()->all();
        $change_wallet['balance'] = $change_list;
        $change_wallet['totle'] = 0;
        $change_wallet['CNY'] = 0;
        $total = 0;
        foreach ($change_wallet['balance'] as $k => $v) {
            $total += bc_mul($v['change_balance'],'1',6);
            //$num = $v['change_balance'] + $v['lock_change_balance'];
            $num = $v['change_balance'];
            if ($v["currency"] == 23 || $v["currency"] == 57) {
                $change_wallet['totle'] += $num;
                $change_wallet['CNY'] += $num;
            } else {
                if($num > 0) {
                    $usdtVal=$this->getUsdtPriceV3($usdtPriceList,$v['currency_name']);
                    $change_wallet['totle'] += bc_mul($num,$usdtVal, 6);
                    //  $change_wallet['totle'] += $num;
                    $change_wallet['CNY'] += bc_mul($num, $usdtVal, 6);
                    // $change_wallet['CNY'] += $num;
                }
            }
            // 20221126 小数点
            if (in_array($v['currency'], [32, 35])) {
                $change_wallet['balance'][$k]['change_balance'] = number_format($v['change_balance'], 6, '.', '');
                $change_wallet['balance'][$k]['lock_change_balance'] = number_format($v['lock_change_balance'], 6, '.', '');
            } else {
                $change_wallet['balance'][$k]['change_balance'] = number_format($v['change_balance'], 6, '.', '');
                $change_wallet['balance'][$k]['lock_change_balance'] = number_format($v['lock_change_balance'], 6, '.', '');
            }
        }

        $wallet_data = [
            'change_wallet' => $change_wallet,
            'total' => $total
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }
    
    
    
    public function contractsWalletList(Request $request)
    {
        $lang = request()->input('lang', 'en');
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        //
        $currency_id = 63;
        $settingVO = 7.19;


        //


        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);

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
                'usdt_price', 'usd_price',

                'lever_balance', 'lock_lever_balance',


                'address', 'erc20_address',

            ]);
            return $item;
        });
        
        $usdtPriceList=Currency::getAllUsdtPriceV2();

        //221120 账户资产只显示 USDT，BTC，ETH，USDC，USD，CNY
        $lever_list = $user_wallet->where('is_lever', 1)->whereIn('currency', [23, 32, 35, 57, 58, 63])->sortByDesc("lever_balance")->values()->all();

        $lever_wallet['balance'] = $lever_list;
        $lever_wallet['totle'] = 0;
        $lever_wallet['CNY'] = 0;

        foreach ($lever_wallet['balance'] as $k => $v) {
            //$num = bc_add($v['lever_balance'], $v['lock_lever_balance'], 6);
            $num = $v['lever_balance'];
            if ($v["currency"] == 23) {
                $lever_wallet['totle'] += $num;
                $lever_wallet['CNY'] += $num;

            } else {
                 if($num > 0) {
                    $usdtVal=$this->getUsdtPriceV3($usdtPriceList,$v['currency_name']);
                    $lever_wallet['totle'] += bc_mul($num,$usdtVal, 6);
                    //  $lever_wallet['totle'] += $num;
                    $lever_wallet['CNY'] += bc_mul($num, $usdtVal, 6);
                    // $lever_wallet['CNY'] += $num;
                }
            }

            // 20221126 小数点
            if (in_array($v['currency'], [32, 35])) {
                $lever_wallet['balance'][$k]['lever_balance'] = number_format($v['lever_balance'], 8, '.', '');
                $lever_wallet['balance'][$k]['lock_lever_balance'] = number_format($v['lock_lever_balance'], 8, '.', '');
            } else {
                $lever_wallet['balance'][$k]['lever_balance'] = number_format($v['lever_balance'], 4, '.', '');
                $lever_wallet['balance'][$k]['lock_lever_balance'] = number_format($v['lock_lever_balance'], 4, '.', '');
            }

        }

        $wallet_data = [
            'lever_wallet' => $lever_wallet,
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }
    
    
    
    public function optionWalletList(Request $request)
    {
        $lang = request()->input('lang', 'en');
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        //
        $currency_id = 63;
        $settingVO = 7.19;


        //


        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);

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
                'currency_type',
                'usdt_price', 'usd_price',

                'micro_balance', 'lock_micro_balance',

                'address', 'erc20_address',

            ]);
            return $item;
        });


        $usdtPriceList=Currency::getAllUsdtPriceV2();
        
        $micro_list = $user_wallet->where('is_micro', 1)->sortByDesc("micro_balance")->values()->all();

        //秒合约账户
        $micro_wallet['CNY'] = 0;
        $micro_wallet['totle'] = 0;
        $micro_wallet['balance'] = $micro_list;

        foreach ($micro_wallet['balance'] as $k => $v) {
            //$num = bc_add($v['micro_balance'], $v['lock_micro_balance'], 6);
            $num = $v['micro_balance'];
            if ($v["currency"] == 23) {
                $micro_wallet['totle'] += $num;
                $micro_wallet['CNY'] += bc_mul($num, $USDTRate, 6);
                // $micro_wallet['CNY'] += $num;
            } else {
                if($num > 0) {
                    $usdtVal=$this->getUsdtPriceV3($usdtPriceList,$v['currency_name']);
                    $micro_wallet['totle'] += bc_mul($num,$usdtVal, 6);
                    //  $micro_wallet['totle'] += $num;
                    $micro_wallet['CNY'] += bc_mul($num, $usdtVal, 6);
                    // $micro_wallet['CNY'] += $num;
                }
            }

        }



        $wallet_data = [
            'micro_wallet' => $micro_wallet,
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }
    
    
    
    public function earnWalletList(Request $request)
    {
        $lang = request()->input('lang', 'en');
        $currency_name = $request->input('currency_name', '');
        $user_id = Users::getUserId();
        //
        $currency_id = 63;
        $settingVO = 7.19;


        //


        if (empty($user_id)) {
            return $this->error(trans('wallet.cscw'));
        }

        $USDTRate = Setting::getValueByKey('USDTRate', 7.22);

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
                'currency_type',
                'usdt_price', 'usd_price',

                'earn_balance', 'lock_earn_balance',
                'address', 'erc20_address',

            ]);
            return $item;
        });




        //理财账户
        //$earn_list = $user_wallet->where('is_earn', 1)->values()->all();
        $earn_list = $user_wallet->sortByDesc("earn_balance")->values()->all();
        $earn_wallet['CNY'] = 0;
        $earn_wallet['totle'] = 0;
        $earn_wallet['balance'] = $earn_list;
        foreach ($earn_wallet['balance'] as $k => $v) {

            //$num = bc_add($v['earn_balance'], $v['lock_earn_balance'], 6);
            $num = $v['earn_balance'];
            if ($v["currency"] == 23) {
                $earn_wallet['totle'] += $num;
                $earn_wallet['CNY'] += bc_mul($num, $USDTRate, 6);
            } else {
                $earn_wallet['totle'] += bc_mul($num, $v['usdt_price'], 6);
                $earn_wallet['CNY'] += bc_mul($num, $v['usdt_price'], 6);
            }
        }
        //



        $wallet_data = [
            'earn_wallet' => $earn_wallet,
        ];
        //Cache::put($cache_key_name, $wallet_data, 60);
        //  }
        return $this->success($wallet_data);
    }
    
    public function getUsdtPriceV3($usdtPriceList,$coin){
            foreach ($usdtPriceList as $tmpCoin){
                $currencyName=$tmpCoin['currencyName'];
                if ($coin==$currencyName){
                    return  $tmpCoin['close'];
                    break;
                }
            }
    }

}