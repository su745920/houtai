<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users;
class BlindBoxOrder extends Model
{
    protected $table = 'blind_box_order';
    protected $dateFormat = 'U';
    protected $fillable = ['user_id','blind_id','currency_id','name','num','consume'];
    public function user()
    {
        return $this->belongsTo(Users::class)->withDefault();
    }
    public function getCurrencyIdAttribute($value)
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
        }else{
            return "进行中";
        }
    }
    
    public function getUserIdAttribute($value)
    {
        return Users::where('id',$value)->value('account_number');
        
    }
}
