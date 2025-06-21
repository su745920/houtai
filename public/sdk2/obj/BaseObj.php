<?php

namespace TigerpaySDK{
    abstract class BaseObj
    {
        public $timestamp;

        public function __construct()
        {
            $this->timestamp = time() * 1000;
        }
    }

}
