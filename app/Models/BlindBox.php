<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Currency;
class BlindBox extends Model
{
    protected $table = 'blind_box';
    protected $dateFormat = 'U';
    
    public function getCreatedAtAttribute($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
    
     public function getCurrencyIdAttribute($value)
    {
      $name = Currency::where('id',$value)->value('name');
        return $name;
    }
    
}
