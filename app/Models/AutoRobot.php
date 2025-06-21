<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoRobot extends Model
{
    protected $table = 'auto_robot';
    public $timestamps = false;

    protected $appends = ['buy_account','sell_account','currency_name','legal_name'];

    public function getBuyAccountAttribute(){
        return $this->hasOne('App\Models\Users','id','buy_user_id')->value('account_number') ?? '';
    }

    public function getSellAccountAttribute(){
        return $this->hasOne('App\Models\Users','id','sell_user_id')->value('account_number') ?? '';
    }

    public function getCurrencyNameAttribute(){
        return $this->hasOne('App\Models\Currency','id','currency_id')->value('name');
    }

    public function getLegalNameAttribute(){
        return $this->hasOne('App\Models\Currency','id','legal_id')->value('name');
    }

    public function getCreateTimeAttribute(){
        return date('Y-m-d H:i:s',$this->attributes['create_time']);
    }
    public static function getPriceArea($auto,$rand_price){

        $new_price=0;

        $up_weight=$auto->up_weight;
        $down_weight=$auto->down_weight;

        $min_price=$auto->min_price;
        $max_price=$auto->max_price;
        //控制涨跌
        $up_or_down = 0;
        $w = $up_weight+$down_weight;
        
        $rand_w = mt_rand(1,$w);
        
        if($rand_w < $up_weight){
            $up_or_down = 1;
        }else{
            $up_or_down = 0;
        }
		
        $init_price=0;
        
        $buy_user_id = $auto->buy_user_id;
        
        //成交最高价
        $transaction = TransactionComplete::where('currency',$auto->currency_id)->where("user_id",$buy_user_id)->where('legal',$auto->legal_id)->orderBy('create_time','desc')->first();
        $usemarket = false;
        if(!empty($transaction)){
        	$init_price=$transaction['price'];
        	$usemarket=true;
        	//if($init_price < $auto->init_price){
        	//	$init_price=$auto->init_price;
        	//}
        }else{
        	
        	$init_price=$auto->init_price;
        }
        
        if($init_price>0){
            if($up_or_down == 1){
                $new_price = bc_add($init_price,$rand_price,6);
            }else{
                $new_price = bc_sub($init_price,$rand_price,6);
            }
            
            if($new_price > $max_price){
                $new_price = bc_sub($init_price,$rand_price,6);
            }
            if($new_price < $min_price){
                $new_price = bc_add($init_price,$rand_price,6);
            }
        }
        $data=array(
            'new_price'=>$new_price,  
            'up_or_down'=>$up_or_down, 
            'use_market'=>$usemarket,
            'init_price'=>$init_price,
        );
        return $data;
    }
}
