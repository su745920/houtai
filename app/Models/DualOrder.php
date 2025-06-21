<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DualOrder extends Model
{
    protected $table = 'dual_order';
    public $timestamps = false;

    protected $appends = ['currency_name','dual_name'];


    public function getCurrencyNameAttribute()
    {
        return $this->hasOne('App\Models\Currency', 'id', 'currency_id')->value('name');
    }

    public function getDualNameAttribute()
    {
        return $this->hasOne('App\Models\DualCurrency', 'id', 'dual_id')->value('name');
    }
}
