<?php


namespace App\Logic;


use App\Models\AccountLog;
use App\Models\CoinTrade;
use App\Models\CurrencyQuotation;
use App\Models\Setting;
use App\Models\UsersWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TransactionIn;
use App\Models\TransactionOut;
use App\Models\TransactionComplete;

class CoinTradeLogic
{
    public static function userSellCoin($userId,$sellCurrencyId,$wantCurrencyId,$amount,$price){
        //第一步  找出钱包
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $sellCurrencyId)
            ->lockForUpdate()
            ->first();

        //锁钱包该币种数量
        //获取当前价格
        $qut = CurrencyQuotation::getInstance($wantCurrencyId,$sellCurrencyId);
        DB::beginTransaction();
        try{
//            $price = bc_mul($amount,$price,8);
            $result = change_wallet_balance($wallet,2, -$amount, AccountLog::COIN_TRADE_FROZEN, '币币交易下单，资金冻结','Coin trading orders, funds frozen');
            if ($result !== true) {
                throw new \Exception($result);
            }

            change_wallet_balance(
                $wallet,
                2,
                $amount,
                AccountLog::COIN_TRADE_FROZEN,
                '币币交易下单，冻结资金增加',
                'Coin trading orders, frozen funds increase',
                true,
                0,
                0,
                serialize([])
            );
            //生成
            CoinTrade::newTrade($userId,CoinTrade::TRADE_TYPE_SELL,$sellCurrencyId,$wantCurrencyId,$amount,$qut->now_price,$price);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }


    }

    public static function userBuyCoint($userId,$buyCurrencyId,$payCurrencyId,$amount,$price){
        //第一步  找出钱包
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $payCurrencyId)
            ->lockForUpdate()
            ->first();
        //锁钱包该币种数量
        $qut = CurrencyQuotation::getInstance($payCurrencyId,$buyCurrencyId);
        $costPrice = bc_mul($price,$amount);
        DB::beginTransaction();
        try{
//            $price = bc_mul($amount,$price,8);
            $result = change_wallet_balance($wallet,2, -$costPrice, AccountLog::COIN_TRADE_FROZEN, '币币交易下单，资金冻结','Coin trading orders, funds frozen');
            if ($result !== true) {
                throw new \Exception($result);
            }

            change_wallet_balance(
                $wallet,
                2,
                $costPrice,
                AccountLog::COIN_TRADE_FROZEN,
                '币币交易下单，冻结资金增加',
                'Coin trading orders, frozen funds increase',
                true,
                0,
                0,
                serialize([])
            );
            //生成
            CoinTrade::newTrade($userId,CoinTrade::TRADE_TYPE_BUY,$buyCurrencyId,$payCurrencyId,$amount,$qut->now_price,$price);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
        // 生成订单
    }

    public static function matchSellTrade($currencyId,$legalId,$nowPrice){
        $tradeList = CoinTrade::where([
            'currency_id' => $currencyId,
            'legal_id' => $legalId,
            'status' => 1,
            'type' => 2
        ])->where('target_price','<=',$nowPrice)->limit(1)->get();

        foreach($tradeList as $trade){
            DB::beginTransaction();
            try{

                $wallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->legal_id)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }

                $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                change_wallet_balance(
                    $targetWallet,
                    2,
                    -$trade->trade_amount,
                    AccountLog::COIN_TRADE_FROZEN,
                    '币币交易冻结减少',
                    'Freezing and reduction of cryptocurrency transactions',
                    true,
                    0,
                    0,
                    serialize([])
                );
                $chargeFee = $trade->charge_fee;
                if($chargeFee >0){
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    //手续费
                    $costPrice = bc_mul($costPrice,$chargeFee,8);
                    change_wallet_balance($wallet,
                        2,
                        $costPrice,
                        AccountLog::COIN_TRADE,
                        '币币交易成功',
                        'Coin transaction successful');
                }
                
                $trade->status = 2;
                $trade->save();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }
    public static function matchSellTrades($currencyId,$legalId,$nowPrice){
        $tradeList = TransactionOut::where([
            'currency' => $currencyId,
            'legal' => $legalId,
            'status' => 0,
            'is_active' => 1,
            //'type' => 1
        ])->where('price','<=',$nowPrice)->limit(2)->get();
        echo "卖单数量".count($tradeList).PHP_EOL;
        foreach($tradeList as $trade){
            DB::beginTransaction();
            try{

                $wallet = UsersWallet::where("user_id", $trade->user_id)
                    ->where("currency", $trade->legal)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->user_id)
                    ->where("currency", $trade->currency)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }

                $costPrice = bc_mul($trade->price,$trade->number,8);
                
                change_wallet_balance(
                    $targetWallet,
                    2,
                    -$trade->number,
                    AccountLog::COIN_TRADE_FROZEN,
                     '货币交易成功，冻结资金减少',
                    'Currency transaction succeeded, and the frozen funds decreased',
                    true,
                    0,
                    0,
                    serialize([])
                );
                change_wallet_balance(
                    $wallet,
                    1,
                    $costPrice,
                    AccountLog::COIN_TRADE,
                    '货币交易成功',
                    'Currency transaction succeeded',
                    false,
                    0,
                    0,
                    serialize([])
                );
                
                $chargeFee = $trade->rate;
                if($chargeFee >0){
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    //手续费
                    $costPrice = bc_mul($costPrice,$chargeFee,8);
                    change_wallet_balance($wallet,
                        1,
                        $costPrice,
                        AccountLog::COIN_TRADE,
                        '币币交易成功',
                        'Coin transaction successful');
                }
                //插入完成记录
                $complete = new TransactionComplete();
                $complete->way = 2;//挂卖
                $complete->type = $trade->type;
                $complete->user_id = 0;
                $complete->from_user_id = $trade->user_id;
                $complete->price = $nowPrice;
                $complete->number = $trade->number;
                $complete->is_active = 1;
                $complete->currency = $currencyId;
                $complete->legal = $legalId;
                $complete->in_fee = 0; //写入手续费
                $complete->create_time = time();
                $complete->save();
                
                // $trade->status = 1;
                // $trade->save();
                 // 删除该挂单
                throw_unless($trade->delete(), new \Exception('交易失败:清除交易失败'));
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }
    public static function matchBuyTrades($currencyId,$legalId,$nowPrice){
        $tradeList = TransactionIn::where([
            'currency' => $currencyId,
            'legal' => $legalId,
            'status' => 0,
            'is_active' => 1,
            //'type' => 1
        ])->where('price','>=',$nowPrice)->limit(2)->get();//
        if($currencyId == 32){
            echo "币种id".$currencyId." 价格 :".$nowPrice;
            echo "买单数量".count($tradeList).PHP_EOL;
        }
        echo "币种id".$currencyId." 价格 :".$nowPrice;
        echo "买单数量".count($tradeList).PHP_EOL;
        foreach($tradeList as $trade){

            DB::beginTransaction();
            try{
                //1 扣掉冻结资金
                $wallet = UsersWallet::where("user_id", $trade->user_id)
                    ->where("currency", $trade->legal)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->user_id)
                    ->where("currency", $trade->currency)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }
                $costPrice = bc_mul($trade->price,$trade->number,8);
                
                change_wallet_balance(
                    $wallet,
                    1,
                    -$costPrice,
                    AccountLog::COIN_TRADE_FROZEN,
                    '货币交易成功，冻结资金减少',
                    'Currency transaction succeeded, and the frozen funds decreased',
                    true,
                    0,
                    0,
                    serialize([])
                );
                change_wallet_balance(
                    $targetWallet,
                    1,
                    $trade->number,
                    AccountLog::COIN_TRADE,
                    '货币交易成功',
                    'Currency transaction succeeded',
                    false,
                    0,
                    0,
                    serialize([])
                );
                //手续费
                $chargeFee = $trade->charge_fee;
                if($chargeFee >0){
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                    change_wallet_balance($targetWallet,
                        2,
                        $amount,
                        AccountLog::COIN_TRADE,
                        '币币交易成功',
                        'Coin transaction successful');
                    
                }
                //插入完成记录
                $complete = new TransactionComplete();
                $complete->way = 1; //挂买
                $complete->type = $trade->type;
                $complete->user_id = $trade->user_id; //买方
                $complete->from_user_id = 0; //卖方
                $complete->price = $nowPrice;
                $complete->is_active = 1;
                $complete->number = $trade->number;
                $complete->currency = $currencyId;
                $complete->legal = $legalId;
                $complete->in_fee = 0; //写入手续费
                $complete->create_time = time();
                $complete->save();
                
                // $trade->status = 1;
                // $trade->save();
                // 删除该挂单
                throw_unless($trade->delete(), new \Exception('交易失败:清除交易失败'));
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }
    public static function matchBuyTrade($currencyId,$legalId,$nowPrice){
        $tradeList = CoinTrade::where([
            'currency_id' => $currencyId,
            'legal_id' => $legalId,
            'status' => 1,
            'type' => 1
        ])->where('target_price','>=',$nowPrice)->limit(1)->get();
        echo "币种id".$currencyId." 价格 :".$nowPrice;
        echo "买单数量".count($tradeList).PHP_EOL;
        foreach($tradeList as $trade){

            DB::beginTransaction();
            try{
                //1 扣掉冻结资金
                $wallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->legal_id)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }
                $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                change_wallet_balance(
                    $wallet,
                    2,
                    -$costPrice,
                    AccountLog::COIN_TRADE_FROZEN,
                    '币币交易成功，冻结资金减少',
                    'Coin transaction successful, frozen funds reduced',
                    true,
                    0,
                    0,
                    serialize([])
                );

                //手续费
                $chargeFee = $trade->charge_fee;
                $chargeFee = bc_sub(1,$chargeFee,8);
                $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                change_wallet_balance($targetWallet,
                    2,
                    $amount,
                    AccountLog::COIN_TRADE,
                    '币币交易成功',
                    'Coin transaction successful');
                $trade->status = 2;
                $trade->save();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }


    public static function forceMatchTrade($tradeId){
        $trade = CoinTrade::find($tradeId);
        if($trade->status != 1){
            throw new \Exception('状态异常');
        }
        DB::beginTransaction();
        try{
            switch ($trade->type){
                case 1:
                    //1 扣掉冻结资金
                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->legal_id)
                        ->lockForUpdate()
                        ->first();

                    $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    if(!$wallet || !$targetWallet){
                        throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                    }
                    $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                    change_wallet_balance(
                        $wallet,
                        2,
                        -$costPrice,
                        AccountLog::COIN_TRADE_FROZEN,
                        '币币交易成功，冻结资金减少',
                        'Coin transaction successful, frozen funds reduced',
                        true,
                        0,
                        0,
                        serialize([])
                    );

                    //手续费
                    $chargeFee = $trade->charge_fee;
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                    change_wallet_balance($targetWallet,
                        2,
                        $amount,
                        AccountLog::COIN_TRADE,
                        '币币交易成功',
                        'Coin transaction successful');
                    $trade->status = 2;
                    $trade->save();
                    break;
                case  2:

                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->legal_id)
                        ->lockForUpdate()
                        ->first();

                    $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    if(!$wallet || !$targetWallet){
                        throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                    }

                    $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                    change_wallet_balance(
                        $targetWallet,
                        2,
                        -$trade->trade_amount,
                        AccountLog::COIN_TRADE_FROZEN,
                        '币币交易冻结减少',
                        'Freezing and reduction of cryptocurrency transactions',
                        true,
                        0,
                        0,
                        serialize([])
                    );
                    $chargeFee = $trade->charge_fee;
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    //手续费
                    $costPrice = bc_mul($costPrice,$chargeFee,8);
                    change_wallet_balance($wallet,
                        2,
                        $costPrice,
                        AccountLog::COIN_TRADE,
                        '币币交易成功',
                        'Coin transaction successful');
                    $trade->status = 2;
                    $trade->save();
                    break;
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    }

    public static function cancelTrade($id){
        $trade = CoinTrade::find($id);
        if(!$trade)
            throw new \Exception('找不到订单');
        if($trade->status != 1)
            throw new \Exception('订单状态异常');
        switch ($trade->type){
            case 1:
                    DB::beginTransaction();
                    try{
                        //解除冻结  还钱
                        $wallet = UsersWallet::where("user_id", $trade->u_id)
                            ->where("currency", $trade->legal_id)
                            ->lockForUpdate()
                            ->first();
                        $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);
                        $result = change_wallet_balance($wallet,
                            2,
                            $costPrice,
                            AccountLog::COIN_TRADE_FROZEN,
                            '取消币币交易，资金返还',
                            'Cancel cryptocurrency transactions and return funds');
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                        $result = change_wallet_balance(
                            $wallet,
                            2,
                            -$costPrice,
                            AccountLog::COIN_TRADE_FROZEN,
                            '取消币币交易，退换冻结资金',
                            'Cancel cryptocurrency transactions, return or exchange frozen funds',
                            true,
                            0,
                            0,
                            serialize([])
                        );
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                        $trade ->status = 3;
                        $trade->save();
                        DB::commit();
                    }catch (\Exception $e){
                        DB::rollBack();
                        throw $e;
                    }
                break;
            case 2:
                DB::beginTransaction();
                try{
                    //解除冻结  还钱
                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    $result = change_wallet_balance($wallet,2, $trade->trade_amount, AccountLog::COIN_TRADE_FROZEN, '取消币币交易','Cancel coin transactions');
                    if ($result !== true) {
                        throw new \Exception($result);
                    }

                    $result = change_wallet_balance(
                        $wallet,
                        2,
                        -$trade->trade_amount,
                        AccountLog::COIN_TRADE_FROZEN,
                        '取消币币交易,退换冻结资金',
                        'Cancel cryptocurrency transactions, return or exchange frozen funds',
                        true,
                        0,
                        0,
                        serialize([])
                    );
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                    $trade ->status = 3;
                    $trade->save();
                    DB::commit();
                }catch (\Exception $e){
                    DB::rollBack();
                    throw $e;
                }
                break;
            default:
                throw new \Exception('类型有误');
        }
        return true;
    }
}
