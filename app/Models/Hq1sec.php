<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hq1sec extends Model
{
    protected $table = 'hq1sec';

    protected $guarded = [];

    public static function getByIdAndSymbol($id,$symbol)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->where("symbol",$symbol)->first();
    }
}