<?php

namespace TigerpaySDK{

    require_once  ('Request.php');

    class TigerpayTradeQueryReq extends Request
    {
        public function __construct(TigerpayTradeQueryObj $obj)
        {
            parent::__construct();
            $this->setData($obj);
        }



        function getChildMethod()
        {
            return "tradepay.trade.query";
        }

    }

}
