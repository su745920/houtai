<?php

namespace App\Models;

class CreditLog extends Model
{
    //
    protected $table = "credit_log";

    public $timestamps = true;

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
}
