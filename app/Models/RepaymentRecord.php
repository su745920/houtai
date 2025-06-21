<?php
/**
 * create by vscode
 * @author lion
 */
namespace App\Models;

class RepaymentRecord extends Model
{

    protected $table = 'repayment_records';

    protected $dateFormat = 'U';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
     protected $appends = [
        'account_number',
        'order_no',
        'name'
    ];

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
    
    

    public function getAccountNumberAttribute()
    {
        return $this->hasOne(Users::class, 'id', 'user_id')->value('account_number');
    }
    
    public function getOrderNoAttribute()
    {
        return $this->hasOne(LoanOrder::class, 'id', 'loan_order_id')->value('order_no');
    }
    
    public function getNameAttribute()
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id')->value('name');
    }
    
    /**
     * 定义与用户表的一对一关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }
    
     /**
     * 定义与订单表的一对一关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function loanOrder()
    {
        return $this->belongsTo(LoanOrder::class, 'loan_order_id', 'id')->withDefault();
    }
    
     /**
     * 定义与币种表的一对一关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id')->withDefault();
    }
}
