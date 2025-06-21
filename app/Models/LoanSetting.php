<?php
/**
 * create by vscode
 * @author lion
 */
namespace App\Models;

class LoanSetting extends Model
{

    protected $table = 'loan_settings';

    protected $dateFormat = 'U';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public function getCreatedAtAttribute()
    {
        $value = $this->attributes['created_at'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }

    public function getUpdatedAtAttribute()
    {
        $value = $this->attributes['updated_at'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }

    /**
     *定义贷款设置和贷款订单的一对多关联
     */
    public function loanOrders()
    {
        return $this->hasMany(LoanOrders::class, 'loan_setting_id');
    }
}
