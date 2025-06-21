<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DualCurrency extends Model
{
    protected $table = 'dual_currency';
    public $timestamps = false;
    protected $appends = ['currency_name'];


    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Models\Currency', 'id', 'currency_id')->value('name');
    }

}
