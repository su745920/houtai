<?php
namespace TigerpaySDK{

    require_once  ('Request.php');

    class TigerpayTradeWappayReq extends Request
    {
        public function __construct(TigerpayTradeWappayObj $obj)
        {
            parent::__construct();
            $this->setData($obj);
        }



        function getChildMethod()
        {
            //return "tigerpay.trade.wappay";
            return "payq.trade.wapurl";
        }
    }
}

