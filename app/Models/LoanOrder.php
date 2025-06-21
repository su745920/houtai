<?php
/**
 * create by vscode
 * @author lion
 */
namespace App\Models;

class LoanOrder extends Model
{

    protected $table = 'loan_orders';

    protected $dateFormat = 'U';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
     protected $appends = [
        'account_number'
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
    
    /**
     * 定义与用户表的一对一关系
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id')->withDefault();
    }
    
}
