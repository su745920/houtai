<?php
namespace TigerpaySDK{

    require_once  ('BaseObj.php');

    /**
     * Class TigerpayTradeQueryObj
     * 订单查询接口
     * @package TigerpaySDK
     */
    class TigerpayTradeQueryObj extends BaseObj
    {
        public $tradeNo;        // 商户订单号
        public $outTradeNo;     // 平台交易流水号

    }
}

