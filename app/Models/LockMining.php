<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Currency;
class LockMining extends Model
{
    protected $table = 'lock_mining';
    protected $dateFormat = 'U';
    
    public function getCreatedAtAttribute($value)
    {
        if (!empty($value)) {
            return date('Y-m-d H:i:s', $value);
        }
        return '';
    }
    
     public function getFromNameAttribute($value)
    {
      $name = Currency::where('id',$value)->value('name');
        return $name;
    }
    
    public function getToNameAttribute($value)
    {
      $name = Currency::where('id',$value)->value('name');
        return $name;
    }
}
