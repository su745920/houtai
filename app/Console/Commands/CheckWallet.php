<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\{Currency, Setting, Users, UsersWallet};
use App\BlockChain\Coin\CoinManager;

class CheckWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CheckWallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查钱包并生成';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    
        try {
            $this->info('开始执行按币种生成钱包脚本--' . Carbon::now()->toDateTimeString());
            
            $list = currency::where("is_display",1)->get();
            if(!empty($list)){
                foreach($list as $key =>$value){
                    $user_list = Users::where("status",0)->get();
                    foreach($user_list as $k=>$v){
                        $wallet = UsersWallet::where("user_id",$v["id"])->where("currency",$value["id"])->first();
                        if(empty($wallet))
                        {
                            $user_wallet = new UsersWallet();
                            $user_wallet->user_id = $v["id"];
                            $user_wallet->currency = $value["id"];
                            $user_wallet->currency = $value["id"];
                            $user_wallet->save();
                        }
                    }
                }
            }
            
            // 查询没有该币种钱包的用户
            $user_query = Users::whereNotExists(function ($query) use ($id) {
                $query->select(DB::raw(1))
                    ->from('users_wallet')
                    ->where('currency', $id)
                    ->whereRaw('users_wallet.user_id = users.id');
            });
            $count = $user_query->count();
            $this->info('共有 ' . $count . ' 个用户需要添加新的钱包地址');
            $i = 1;
            foreach ($user_query->cursor() as $user) {
                if (UsersWallet::where('user_id', $user->id)->where('currency', $id)->exists()) {
                    $this->error('第 ' . $i . '/' . $count . ' 个用户有此币种钱包,用户 id 为：' . $user->id);
                    continue;
                }
                $this->info('开始生成第 ' . $i . '/' . $count . ' 个用户的钱包地址,用户 id 为：' . $user->id);
                if ($currency->make_wallet == 1) {
                    $response = $http_client->post($address_url, [
                        'form_params' => [
                            'userid' => $user->id,
                            'projectname' => $project_name,
                        ]
                    ]);
                    $result = json_decode($response->getBody()->getContents());
                    if ($result->code != 0) {
                        return false;
                    }
                    $walllet_data = $result->data;
                    if ($currency->multi_protocol == 0) {
                        if (!in_array($currency->type, CoinManager::getMakeWalletCoinList())) {
                            $this->error("暂不支持生成{$type_name}协议的钱包");
                            continue;
                        }
                        $address = $walllet_data->{"{$type_name}_address"};
                        $private = $walllet_data->{"{$type_name}_private"};
                    }
                } elseif ($currency->make_wallet == 2) {
                    $address = $currency->collect_account;
                    $private = '';
                } elseif ($currency->make_wallet == 3) {
                    $address = '';
                    $private = '';
                }
                $wallet = UsersWallet::unguarded(function () use ($address, $private, $user, $currency) {
                    return UsersWallet::create([
                        'user_id' => $user->id,
                        'currency' => $currency->id,
                        'address' => $address,
                        'private' => $private,
                        'create_time' => time(),
                    ]);
                });
                if (!isset($wallet->id)) {
                    $this->error('用户id:' . $user->id . ',币种' . $currency->name . '钱包生成错误');
                }
                
            }
            
            $this->info('执行成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
}
