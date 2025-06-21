<?php
/**
 * create by vscode
 * @author lion
 */
namespace App\Models;

class Admin extends Model
{
    protected $table = 'admin';
    public $timestamps = false;
    protected $appends = ['role_name'];

    public function getRoleNameAttribute(){
        return $this->hasOne(AdminRole::class,'id','role_id')->value('name');
    }
    public static function getById($id)
    {
        if (empty($id)) {
            return "";
        }
        return self::where("id", $id)->first();
    }
}
