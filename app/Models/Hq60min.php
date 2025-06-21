<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hq60min extends Model
{
    protected $table = 'hq60min';

    protected $guarded = [];

    public static function getByIdAndSymbol($id,$symbol)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->where("symbol",$symbol)->first();
    }
}