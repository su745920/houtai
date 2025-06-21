<?php

namespace App\Models;

class RebateRules extends Model
{
    protected $table = 'rebate_rules';
    //自动时间戳
    protected $dateFormat = 'U';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected static $langList = [
        'zh' => '中文简体',
        'en' => '英文',
        'hk' => '中文繁体',
        'jp' => '日语',
        'es' => '西班牙',
        'id' => '印尼语',
        'th' => '泰语',
        'vi' => '越南语',
        'kor' => '韩语',
    ];

    public static function getLangeList()
    {
        return self::$langList;
    }

    /**
     * 定义新闻和分类的一对多相对关联
     */




    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d', $value ) : '';
    }


    public function getUpdateTimeAttribute()
    {
        $value = $this->attributes['update_time'];
        return $value ? date('Y-m-d H:i:s', $value ) : '';
    }

    /**
     * 获取当前时间
     *
     * @return int
     */

    public function freshTimestamp()
    {
        return time();
    }

    /**
     * 避免转换时间戳为时间字符串
     *
     * @param DateTime|int $value
     * @return DateTime|int
     */

    public function fromDateTime($value)
    {
        return $value;
    }


}
