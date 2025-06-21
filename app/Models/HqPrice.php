<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HqPrice extends Model
{
    protected $table = 'hq_price';

    protected $guarded = [];
    protected $appends = [
        'currency_name'
    ];


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
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id')->withDefault();
    }
}