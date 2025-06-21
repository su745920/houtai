<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hq30min extends Model
{
    protected $table = 'hq30min';

    protected $guarded = [];

    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }
}