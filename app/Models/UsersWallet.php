<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */

namespace App\Models;

use Illuminate\Support\Carbon;
use App\BlockChain\Coin\CoinManager;
use App\Jobs\UpdateBalance;
use App\Models\{Setting};
class UsersWallet extends Model
{
    
    
    
    
    
    public static function makeWalletV2($user_id,$lang)
    {
    //     $currency=null;
    //     if ($lang=="zh"){
    //         $currency = Currency::where('is_display',1)->whereNotIn('id',[81,82,84])->orderBy('sort','asc')->get();
    //     }else if($lang=="vi"){
    //     $currency = Currency::where('is_display',1)->whereNotIn('id',[63,82,84])->orderBy('sort','asc')->get();
    //   }else if($lang=="th") {
    //         $currency = Currency::where('is_display',1)->whereNotIn('id',[63,81,84])->orderBy('sort','asc')->get();
    //     }else if($lang=="id") {
    //         $currency = Currency::where('is_display',1)->whereNotIn('id',[63,81,82])->orderBy('sort','asc')->get();
    //     } else{
    //         $currency = Currency::where('is_display',1)->whereNotIn('id',[63,81,82,84])->orderBy('sort','asc')->get();
    //     }
    
        $currency=null;
        if ($lang=="zh"){
            $currency = Currency::where('is_display',1)->whereNotIn('id',[81,82,84,100,101,102])->orderBy('sort','asc')->get();
        }else if($lang=="vi"){
        $currency = Currency::where('is_display',1)->whereNotIn('id',[63,82,84,101,102])->orderBy('sort','asc')->get();
       }else if($lang=="th") {
            $currency = Currency::where('is_display',1)->whereNotIn('id',[63,81,84,101,102])->orderBy('sort','asc')->get();
        }else if($lang=="id") {
            $currency = Currency::where('is_display',1)->whereNotIn('id',[63,81,82,101,102])->orderBy('sort','asc')->get();
        } else{
            $currency = Currency::where('is_display',1)->whereNotIn('id',[63,81,82,84])->orderBy('sort','asc')->get();
        }


        $uri = '/api/index';

        $api = config('lbxchain.wallet_api');
        $app_id = config('lbxchain.app_id');
        $app_secret = config('lbxchain.app_secret');
        
        // 禁用全局钱包地址覆盖机制，避免安全风险
        // $is_open_CTbi = Setting::getValueByKey('is_open_CTbi', '');

        foreach ($currency as $key => $value) {
            // 判断对应币种钱包是否已存在
            $user_wallet = self::where('user_id', $user_id)->where('currency', $value->id)->first();

            if (empty($user_wallet)) {
                $user_wallet = new self();
            }

            $user_wallet->user_id = $user_id;
            $user_wallet->currency = $value->id;
            $type_name = strtolower($value->type);
            
            // 注释掉全局钱包地址覆盖机制，避免安全风险
            /*
            if($is_open_CTbi == '1' && $value['name'] == 'USDT'){
                $user_wallet->address = Setting::getValueByKey('USDTAddress', '');
                $user_wallet->erc20_address = Setting::getValueByKey('USDTAddress_erc', '');
            }
            if($is_open_CTbi == '1' && $value['name'] == 'BTC'){
                $user_wallet->address = Setting::getValueByKey('BTCAddress', '');
            }
            if($is_open_CTbi == '1' && $value['name'] == 'ETH'){
                $user_wallet->address = Setting::getValueByKey('ETHAddress', '');
            }
            */

            if ($value->make_wallet == 1) {

                // if (!in_array($type_name, CoinManager::getMakeWalletCoinList())) {
                //     continue;
                // }

                $timestamp = time();
                $type = "";
                if($value->type =="trc20"){
                    $type = "trx";
                }else if($value->type =="btc"){
                    $type = "btc";
                }else if($value->type =="erc20"){
                    $type = "eth";
                }

                $remark = $user_id;
                $nonce_str = createNonceStr();

                $data = array(
                    "app_id" =>$app_id,
                    "timestamp" => $timestamp,
                    "nonce_str" => $nonce_str,
                    "type" => $type,
                    "remark" => $remark
                );

                $sign = makeSign($data,$app_secret);
                $data["sign"] = $sign;

                $url = $api.$uri;

                $response = curl_get1($url, $data);
                $response = json_decode($response, true);

                if(!empty($response))
                {
                    if($response["success"] == true)
                    {
                        $user_wallet->address = $response["data"]["address"];
                        $user_wallet->private = $response["data"]["private"];
                    }
                }
                if($value->id == 23){
                    $remark = $user_id;
                    $nonce_str = createNonceStr();

                    $data = array(
                        "app_id" =>$app_id,
                        "timestamp" => $timestamp,
                        "nonce_str" => $nonce_str,
                        "type" => "eth",
                        "remark" => $remark
                    );

                    $sign = makeSign($data,$app_secret);
                    $data["sign"] = $sign;

                    $url = $api.$uri;

                    $response1 = curl_get1($url, $data);

                    $response1 = json_decode($response1, true);
                    if(!empty($response1))
                    {
                        if($response1["success"] == true)
                        {
                            $user_wallet->erc20_address = $response1["data"]["address"];
                            $user_wallet->erc20_private = $response1["data"]["private"];
                        }
                    }

                }

            } else if ($value->make_wallet == 2) {
                $user_wallet->address = $value->collect_account;
                $user_wallet->private = '';
            } else if ($value->make_wallet == 0) {
                $user_wallet->address = '';
                $user_wallet->private = '';
            }
            $user_wallet->create_time = time();
            $user_wallet->legal_balance = 0;
            $user_wallet->change_balance = 0;
            $user_wallet->lever_balance = 0;
            $user_wallet->micro_balance = 0;
            $user_wallet->earn_balance = 0;

            $user_wallet->save(); //默认生成所有币种的钱包
        }
    }
    
    protected $table = 'users_wallet';
    public $timestamps = false;

    const CURRENCY_DEFAULT = "USDT";

    protected static $balanceTypeList = [
        0 => '资金账户',
        1 => '币币账户',
        2 => '合约账户',
        3 => '秒合约账户',
        4 => '理财账户',
    ];

    protected $hidden = [
        'private',
        'erc20_private',
    ];

    protected $appends = [
        'currency_name',
        'currency_type',
        'contract_address',
        'address',
        'erc20_address',
        'is_legal',
        'is_lever',
        'is_recharge',
        'is_withdraw',
        'is_micro',
        'is_match',
        'is_transfer',
        'usdt_price',
        'sort',
        'multi_protocol',
        'label', //标签
        'logo'
    ];

    /**
     * 返回账户类型列表
     *
     * @return array
     */
    public static function getBalanceTypeList()
    {
        return self::$balanceTypeList;
    }

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }
    public function getSortAttribute()
    {
       if (!empty($this->currencyCoin)){
            return $this->currencyCoin->sort;
        }
        return "";
    }
    public function getCurrencyTypeAttribute()
    {
        return $this->currencyCoin->type ?? '';
    }

    public function getMultiProtocolAttribute()
    {
        return $this->currencyCoin->multi_protocol ?? 0;
    }

    public function getLabelAttribute()
    {
       $type = $this->currencyCoin->make_wallet ?? 0;
       if ($type == 2) {
           return $this->user->extension_code ?? '';
       }
       return '';
    }

    public function getCurrencyNameAttribute()
    {
        return $this->currencyCoin->name ?? '';
    }

    public function getContractAddressAttribute()
    {
        return $this->currencyCoin->contract_address ?? '';
    }
    
    // public function getAddressAttribute()
    // {
    //     return $this->currencyCoin->address ?? '';
    // }
    
    public function getErc20AddressAttribute()
    {
        //$address = $this->attributes['erc20_address'];
        //return $address ?? '';
        return "";
    }
    
    public function getLogoAttribute()
    {
        return $this->currencyCoin->logo ?? "";
    }
    
    
    public function getIsLegalAttribute()
    {
        return $this->currencyCoin->is_legal ?? 0;
    }
    public function getIsRechargeAttribute()
    {
        return $this->currencyCoin->is_recharge ?? 0;
    }
    public function getIsWithdrawAttribute()
    {
        return $this->currencyCoin->is_withdraw ?? 0;
    }
    public function getIsLeverAttribute()
    {
        return $this->currencyCoin->is_lever ?? 0;
    }
    public function getIsMicroAttribute()
    {
        return $this->currencyCoin->is_micro ?? 0;
    }
    public function getIsTransferAttribute()
    {
        return $this->currencyCoin->is_transfer ?? 0;
    }
    
    public function getIsMatchAttribute()
    {
        return $this->currencyCoin->is_match ?? 0;
    }
    
    public function getIsDistransferAttribute()
    {
        return $this->currencyCoin->is_distransfer ?? 0;
    }
    public function getAddressAttribute()
    {
        
        //$address = $this->attributes['address'];
                
        //return $address;
        return "";
    }
    // public function getAddressAttribute($value)
    // {
    //     $make_wallet_type = $this->currencyCoin->make_wallet ?? 0; // 生成用户钱包的策略:0.不生成,1.接口生成,2.从归拢地址继承,3.空钱包
    //     switch ($make_wallet_type) {
    //         case 0:
    //             $address = null;
    //             break;
    //         case 1:
    //             $address = $value ?? 'ERROR';
    //             break;
    //         case 2:
    //             $address = $this->currencyCoin->collect_account ?? 'UNDEFINED';
    //             break;
    //         case 3:
    //             $address = 'EMPTY';
    //             break;
    //         default:
    //             $address = '';
    //             break;
    //     }
    //     return $address;
    // }

    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id')->orderBy('sort', 'desc');
    }

    /**
     * 根据用户ID来生成钱包
     * @param mixed $user_id 
     * @return bool 
     */
    public static function makeWallet($user_id)
    {
        
        //$currency = Currency::orderBy('sort','asc')->get();
        $currency = Currency::where('is_display',1)->orderBy('sort','asc')->get();
        
        $uri = '/api/index';

        $api = config('lbxchain.wallet_api');
        $app_id = config('lbxchain.app_id');
        $app_secret = config('lbxchain.app_secret');
        
        // 禁用全局钱包地址覆盖机制，避免安全风险
        // $is_open_CTbi = Setting::getValueByKey('is_open_CTbi', '');
       
        foreach ($currency as $key => $value) {
            // 判断对应币种钱包是否已存在
            $user_wallet = self::where('user_id', $user_id)->where('currency', $value->id)->first();
            
            
            if (empty($user_wallet)) {
                $user_wallet = new self();
            }
            
            $user_wallet->user_id = $user_id;
            $user_wallet->currency = $value->id;
            $type_name = strtolower($value->type);
            
            // 注释掉全局钱包地址覆盖机制，避免安全风险
            /*
            if($is_open_CTbi == '1' && $value['name'] == 'USDT'){
                 $user_wallet->address = Setting::getValueByKey('USDTAddress', '');
                 $user_wallet->erc20_address = Setting::getValueByKey('USDTAddress_erc', '');
            }
            if($is_open_CTbi == '1' && $value['name'] == 'BTC'){
                 $user_wallet->address = Setting::getValueByKey('BTCAddress', '');
                
            }
            if($is_open_CTbi == '1' && $value['name'] == 'ETH'){
                 $user_wallet->address = Setting::getValueByKey('ETHAddress', '');
                
            }
            */
            
            if ($value->make_wallet == 1) {
                
                // if (!in_array($type_name, CoinManager::getMakeWalletCoinList())) {
                //     continue;
                // }
                
                $timestamp = time();
                $type = "";
                if($value->type =="trc20"){
                    $type = "trx";
                }else if($value->type =="btc"){
                    $type = "btc";
                }else if($value->type =="erc20"){
                    $type = "eth";
                }
                
                $remark = $user_id;
                $nonce_str = createNonceStr();
                
                $data = array(
                    "app_id" =>$app_id,
                    "timestamp" => $timestamp,
                    "nonce_str" => $nonce_str,
                    "type" => $type,
                    "remark" => $remark
                );
                
                $sign = makeSign($data,$app_secret);
                $data["sign"] = $sign;
                
                $url = $api.$uri;
                
                $response = curl_get1($url, $data);
                $response = json_decode($response, true);
                
                if(!empty($response))
                {
                    if($response["success"] == true)
                    {
                        $user_wallet->address = $response["data"]["address"];
                        $user_wallet->private = $response["data"]["private"];
                    }
                }
                if($value->id == 23){
                    $remark = $user_id;
                    $nonce_str = createNonceStr();
                    
                    $data = array(
                        "app_id" =>$app_id,
                        "timestamp" => $timestamp,
                        "nonce_str" => $nonce_str,
                        "type" => "eth",
                        "remark" => $remark
                    );
                    
                    $sign = makeSign($data,$app_secret);
                    $data["sign"] = $sign;
                    
                    $url = $api.$uri;
                    
                    $response1 = curl_get1($url, $data);
                    
                    $response1 = json_decode($response1, true);
                    if(!empty($response1))
                    {
                        if($response1["success"] == true)
                        {
                            $user_wallet->erc20_address = $response1["data"]["address"];
                            $user_wallet->erc20_private = $response1["data"]["private"];
                        }
                    }
                    
                }
                
            } else if ($value->make_wallet == 2) {
                $user_wallet->address = $value->collect_account;
                $user_wallet->private = '';
            } else if ($value->make_wallet == 0) {
                $user_wallet->address = '';
                $user_wallet->private = '';
            }
            $user_wallet->create_time = time();
             $user_wallet->create_time = time();
            $user_wallet->legal_balance = 0;
            $user_wallet->change_balance = 0;
            $user_wallet->lever_balance = 0;
            $user_wallet->micro_balance = 0;
            $user_wallet->earn_balance = 0;
            
            $user_wallet->save(); //默认生成所有币种的钱包
        }
    }
    
    /**
     * 从链上监听余额变动
     * @param mixed $user_wallet 
     * @return void 
     */
    public static function queryChainBalance($user_wallet)
    {
        $wallet_list = [];
        $policy = [0, 1, 5, 10, 20, 30];
        $currency = $user_wallet->currencyCoin;
        if ($currency->multi_protocol == 1) {
            $wallet_list = self::whereHas('currencyCoin', function ($query) use ($currency) {
                    $query->where('parent_id', $currency->id);
                })->where('user_id', $user_wallet->user_id)
                ->get();
        } else {
            $wallet_list[] = $user_wallet;
        }
        foreach ($wallet_list as $wallet) {
            foreach ($policy as $value) {
                UpdateBalance::dispatch($wallet)
                    ->onQueue('update:block:balance')
                    ->delay(Carbon::now()->addMinutes($value));
            }
        }
    }

    public function getUsdtPriceAttribute()
    {
      if ($this->attributes['legal_balance']>0
            ||$this->attributes['change_balance']>0||$this->attributes['lever_balance']>0
            ||$this->attributes['earn_balance']>0||$this->attributes['micro_balance']>0
        ) {
//            $currency_id = $this->attributes['currency'];
//            return Currency::getUsdtPrice($currency_id);

            $coin= $this->currencyCoin->name ?? '';
            $coin=strtolower($coin);
            return Currency::getUsdtPriceV2($coin);
        }else{
            return 0;
        }
        
    }

    public function getPbPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getPbPrice($currency_id);
    }

    public function getCnyPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getCnyPrice($currency_id);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getPrivateAttribute($value)
    {
        return empty($value) ? '' : decrypt($value);
    }

    public function setPrivateAttribute($value)
    {
        $this->attributes['private'] = $value;
    }

    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
}
