<?php
namespace TigerpaySDK{

    require_once ('BaseObj.php');

    /**
     * Class TigerpayTradeWapObj
     * wap支付接口
     * @package TigerpaySDK
     */
    class TigerpayTradeWapObj extends BaseObj
    {

        public $tradeNo;        //  商户订单号
        public $price = 0;      //  订单金额（当大于0时支付时无法手动输入金额）
        public $symbol = 0;
        public $userId;         //  用户ID
        public $userName;       //  用户姓名
        public $userPhone;      //  用户手机号
        public $userCardNo;     //  用户银行卡号
        public $subject;        //  商品标题
        public $body;           //  商品描述
        public $returnUrl;
        public $notifyUrl;
        public $payType = 0;        //支付方式
    }
}

