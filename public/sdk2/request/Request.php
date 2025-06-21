<?php

namespace TigerpaySDK{

    abstract class Request{
        public $appId;
        public $charset = "utf-8";
        public $data;
        public $method;
        public $sign;
        public $version = "1.0.0";

        function __construct()
        {
            $this->method = $this->getChildMethod();
        }

        abstract function getChildMethod();

        /**
         * @return mixed
         */
        public function getData()
        {
            return $this->data;
        }

        /**
         * @param mixed $data
         */
        public function setData($data)
        {
            $this->data = $data;
        }


    }
}
