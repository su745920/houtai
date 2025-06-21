<?php
namespace TigerpaySDK{

    require_once  ('Request.php');

    class TigerpayTradeWapReq extends Request
    {
        public function __construct(TigerpayTradeWapObj $obj)
        {
            parent::__construct();
            $this->setData($obj);
        }



        function getChildMethod()
        {
            return "payq.trade.wapurl";//payq.trade.wapurl
        }
    }
}

