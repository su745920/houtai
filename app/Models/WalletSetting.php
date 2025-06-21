<?php

namespace App\Models;

class WalletSetting extends Model
{
    protected $table = "wallet_setting";
    public $timestamps = true;

    
    /**
     * 模型日期的存储格式
     *
     * @var string
     */
    protected $dateFormat = 'U';
}