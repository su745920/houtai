<?php

namespace App\Models;

class UserCashInfo extends Model
{
    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("user_id", $id)->first();
    }
    protected $table = 'user_cash_info';
    public $timestamps = false;
    protected $appends = ['account_number'];



    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }

    public function getCreateTimeAttribute()
    {
        return date('Y-m-d H:i:s', $this->attributes['create_time']);
    }
}
