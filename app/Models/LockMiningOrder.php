<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users;
class LockMiningOrder extends Model
{
    protected $table = 'lock_mining_order';
    protected $dateFormat = 'U';
    protected $fillable = ['user_id','lock_id','from_name','to_name','day','money','rate','rate_max','level'];
    protected $hidden = ['status_name'];
    public function user()
    {
        return $this->belongsTo(Users::class)->withDefault();
    }
     public function getToNameAttribute($value)
    {
       $name = Currency::where('id',$value)->value('name');
        return $name;
    }
    public function getFromNameAttribute($value)
    {
       $name = Currency::where('id',$value)->value('name');
        return $name;
    }
    public function getCreatedAtAttribute($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
    public function getCreatedAt1Attribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return '';
    }
    public function getUpdatedAtAttribute($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
    public function getCompleteAtAttribute($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
     public function getStatusAttribute($value)
    {
        if($value == '1'){
            return "已结束";
        }else if($value == '7'){
            return "提前赎回";
        }else{
            return "进行中";
        }
    }
    
    
     public function getStatusNameAttribute()
    {
        return $this->attributes['status'];
       
    }
   
    // public function getUserIdAttribute($value)
    // {
    //     return Users::where('id',$value)->value('id');
        
    // }
}
