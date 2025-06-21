<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\{AccountLog, CurrencyMatch, LeverTransaction, LeverMultiple, Setting, TransactionComplete, TransactionIn, TransactionOut, Users, UsersWallet,WalletAddress,DzpConfigCount};
use App\Events\LeverSubmitOrderEvent;
use App\Jobs\LeverClose;
use App;
use Illuminate\Support\Facades\Redis;
use App\Utils\RSC;

class PayController extends Controller
{
    private $api_key = '5000017'; // CashPay给的 API-KEY
    private $api_secret = '419747ffed009c9443579F5454bcAe0b'; // CashPay给的 API-SECRET
    public function createTransaction(Request $request){
        // 判断是否开启了实名认证校验
        if(Setting::getValueByKey("is_open_transaction",0) === 1) {
            // 判断是否高级实名认证
            $advanced_review_status = 0;
            $user_id = Users::getUserId();
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
         DB::beginTransaction();
        try {
             $rsa_pubkey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAq3Jtsr9ZTuudaCvtoIQ/
pDOnjOAKptEI4ICukmcBnaPeA2YThuOZARVsFIkU364aevT77hjVeyvZlV5jaVOG
zvF99+3bd9fyIx7MWFOIJV6wGbKmNv6+5YfQKwsaPDDqwghriH+MWDwb4HDyeZZj
VTiz8ZkENwPTe5IwH1mR4lTWGYf+UGJm4plVZ7X0b9EfTjcrLEj2kAyTR2o5d3ZT
20Hqt6MXGj58+87ItERJux0s/QCUahbL/uZpCvVyqRZhVWh5+qSQ1SvtHC4TkJoY
JRIVwGoyPBQsvfd4q1oLgGuFqiNNeMaSR5JpMSpsQwEFggQY/jvm0tNNpf8PDzgd
VwIDAQAB
-----END PUBLIC KEY-----'; // 这里是CashPay给的rsa_pubkey
        
        $rsa_pubkey = str_replace("\r", "", $rsa_pubkey);
        
        $rsa = new RSC('', $rsa_pubkey);
        
        $url = 'https://api.cashpay.top/index.php/u/create_transaction/';
        $addr = 'TCLngPq5dq9wkdKdhBgHVYAC2BjDNZ5555';
        $amount = $request->money; // USDT
        $currency = 'usd'; // USDT
        $order_no= date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $user_id = Users::getUserId();
        // 校验
        if(empty($amount) || empty($user_id)) {
            $this->error(trans('common.cscw'));
        }
        $post = array(
            'addr' => $addr,
            'amount' => $amount, // 表示100USDT
            'currency' => $currency,
            'coin_type' => 'trc20',
            'notify_url' => 'https://admin.coinbmex.com/payNotify',
            'redirect_url' => 'https://coinbm.com',
            'order_sn' => $order_no,
            'customer_id' => $user_id,
            'product_name' => 'TRC20',
            'descript' => $rsa->publicEncrypt(json_encode(['addr'=>$addr, 'amount'=>$amount, 'currency'=>$currency])),
        );
        
        $ret = $this->curl_api($url, $post, $this->api_key, $this->api_secret);
        $ret = json_decode($ret, true);
        if (empty($ret) || $ret['code'] !== 0) {
            return $this->error('System error, please try again');
        }
        $user = Users::getAuthUser();
        $usdt_type = 'ERC20';
        $info = [
            'order_no'   =>  $order_no,
            'truename'     => '',
            'user_id'       => $user->id,
            'phone'         => $user->account_number,
            'address'       => $ret['data']['addr'],
            'money'         => $amount,
            'cashtype'      => '',
            'currency'      => 23,
            'voucher'       => '',
            'dianhui'       => 0,
            'created_at'    => date('Y-m-d H:i:s',time()),
            'updated_at'    => date('Y-m-d H:i:s',time()),
            'usdt_type'     =>$usdt_type
        ];

        $res = DB::table('recharge_record')->insert($info);
        
        // 机器人推送消息
        robotSendMessage($user->id,'申请充值'.$amount.'（'.$usdt_type.'）');
        Redis::set('recharge_tip_count', 1);
        DB::commit();
        return $this->success(array(
            'code' => 200,
            'data' => $ret['data']
        ));
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }
    function curl_api($url, $post=[], $api_key, $api_secret, $timeout=5) {
        ksort($post);
        $json_data = json_encode($post, JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $message = $timestamp . '&' . $json_data;
        $signature = hash_hmac('sha256', $message, $api_secret);
        $header = [
            "Content-Type: application/json",
            "API-KEY: {$api_key}",
            "TIMESTAMP: {$timestamp}",
            "SIGNATURE: {$signature}"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $res = curl_exec($ch);
        if ($error=curl_error($ch)) {
            // die($error);
            return false;
        }
        curl_close($ch);
        return $res;
    }
    // 回调通知
    function payNotify() {
        $data = \request()->all();
        if($data['status'] == 3) {
            $md5_str = md5($data['sys_sn'].$data['order_sn'].$data['parent_order_sn'].$data['amount'].$data['status'].$data['coin_type'].$data['coin_amount'].$data['real_amount'] .$data['txID'].$this->api_key.$this->api_secret);
            if($md5_str == $data['signature']) {
                 DB::beginTransaction();
               try {
                   $amount = floatval($data['real_amount']);
                    $po = RechargeRecord::where('order_no',$data['order_sn'])->where('status',0)->first();
                    if(!$po) {
                        throw new Exception("该订单不存在");
                    }
                    $user_id = $po->user_id;
                    $po->update(['status' => 1]);
                    DB::update('update users set cz_count = cz_count+1 where id = ' . $user_id);
                    $currency = $po->currency;
                    $sql = 'update users_wallet set cz_amount = cz_amount+' . $amount . ' where user_id = ' . $user_id . ' and currency=' . $currency;
                    DB::update($sql);
                    
                    //计算抽奖次数
                    $cj_count = 0;
                    $configCountPO = DzpConfigCount::find(1);
                    $czAmountConfig = $configCountPO->cz_amount;
                    $czAmountConfig = doubleval($czAmountConfig);
                    if (!empty($czAmountConfig)&&$czAmountConfig>0){
                        if ($amount>$czAmountConfig){
                            $cj_count=bc_div($amount,$czAmountConfig);
                        }
                    }
                    $cj_count=floor($cj_count);
    
                    $czAmountSql = "update users set cz_amount = cz_amount+" . $amount ." , cj_count= cj_count+".$cj_count.   " where id = " . $user_id;
                    DB::update($czAmountSql);
                    
                    $wallet = UsersWallet::where('currency', $currency)->where('user_id', $user_id)->first();
                    
                    $result = change_wallet_balance($wallet,
                        0,//充值充到 资金账户2023-08-26
                        +$amount,
                        AccountLog::WALLET_CURRENCY_IN,
                        '充币',
                        'Deposit coins');
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                    DB::commit();
                    return [
                        code => 0,
                        msg => 'success'
                    ];
               } catch (\Exception $e) {
                   DB::rollBack();
                    writeLog('receive_log',$e->getMessage());
                   return [
                        code => 400,
                        msg => 'error'
                    ];
               }
                
            }
        }
        writeLog('receive',$data);
    }
}