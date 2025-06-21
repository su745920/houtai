<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hq1min extends Model
{
    protected $table = 'hq1min';

    protected $guarded = [];

    public static function getByIdAndSymbol($id,$symbol)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->where("symbol",$symbol)->first();
    }
}