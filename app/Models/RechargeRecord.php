<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class RechargeRecord extends Model
{
    //protected $appends = ['user'];

    protected $table = "recharge_record";

    public $timestamps = false;

    /**
     * 模型日期的存储格式
     *
     * @var string
     */
    protected $dateFormat = 'U';
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
    // public function getUserAttribute()
    // {
    //     $value = $this->attributes['user_id'] ?? 0;

    //     $u = DB::table('users')->where('id', $value)->first();

    //     return $u;

    // }
}
