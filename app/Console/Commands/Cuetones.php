<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\BlockChain\Coin\CoinManager;
use App\Models\{Users, UsersWallet,LockMining,LockMiningOrder,AccountLog,Cuetone,RechargeRecord};
use Illuminate\Support\Facades\DB;
class Cuetones extends Command
{
    protected $signature = 'do_cuetone';
    protected $description = '进行充值提醒';


    public function handle()
    {
        $this->doCuetone();

    }
    /*
        每分钟执行一次
    */

    public function doCuetone()
    {
    
        $cuetone = Cuetone::find(1);
      
        if (!empty($cuetone)) {
            $recharge = RechargeRecord::where('id', '>=', $cuetone['chrage_id'])->where('state', '1')->orderBy('id', 'desc')->first();
            if (!empty($recharge)) {
                //进行更新，并且进行播放
                $cuetone->chrage_id = $recharge->id;
                $cuetone->save();
                $this->curl_get('https://www.iex-pro.com/admin/cuetone');
            }
        } else {
            $cuetone = new Cuetone();
            $cuetone->id = 1;
            $cuetone->save();
            $this->curl_get('https://www.iex-pro.com/admin/cuetone');
        }

    }

    /**
     * get curl 请求
     * @param $api
     * @param array $data
     * @param bool $debug
     * @return mixed
     */
    public function curl_get($api, $data = [], $debug = false)
    {

        $url =  $api . '?' . http_build_query($data);

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //不验证 证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $res = curl_exec($ch);

        if (curl_errno($ch) && $debug) {
            curl_close($ch);
            die;
        }
        curl_close($ch);

        return $res;
    }

}
