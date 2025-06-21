<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hq1day extends Model
{
    protected $table = 'hq1day';

    protected $guarded = [];

    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }
}