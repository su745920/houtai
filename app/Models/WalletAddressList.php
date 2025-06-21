<?php

namespace App\Models;

class WalletAddressList extends Model
{
    protected $table = 'wallet_address_list';
    protected $appends = [
        'currency_name',
        'currency_logo'
    ];
    protected $hidden = ['currency'];
    
    public static function getByCurrencyId($currency_id)
    {
        if (empty($currency_id)) {
            return "";
        }
        return self::where("currency_id", $currency_id)->first();
    }
    public function getCurrencyNameAttribute()
    {
        return $this->currency->name ?? '';
    }
    
    public function getCurrencyLogoAttribute()
    {
        return $this->currency->logo ?? '';
    }
    
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id')->withDefault();
    }
    
}
