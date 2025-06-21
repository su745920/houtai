<?php
namespace TigerpaySDK{


    use Exception;

    require_once 'utils/SignUtils.php';
    require_once 'obj/TigerpayTradeWapObj.php';
    require_once 'obj/TigerpayTradeWappayObj.php';
    require_once 'obj/TigerpayTradeQueryObj.php';
    require_once 'request/TigerpayTradeWapReq.php';
    require_once 'request/TigerpayTradeWappayReq.php';
    require_once 'request/TigerpayTradeQueryReq.php';

    class TigerpayClient
    {
        private $serverUrl;
        private $appId;
        private $appPrivateKey;
        private $serverPublicKey;

        /**
         * TigerpayClient constructor.
         * @param $serverUrl        服务器地址
         * @param $appId            在支付商获取的应用ID
         * @param $appPrivateKey    在支付商获取的应用私钥
         * @param $serverPublicKey  在支付商获取的服务器公钥
         */
        public function __construct($serverUrl, $appId, $appPrivateKey, $serverPublicKey)
        {
            $this->serverUrl = $serverUrl;
            $this->appId = $appId;

            $appPrivateKey = str_replace(array("\r", "\n", "\r\n", " "), "", $appPrivateKey);
            // 每隔64个字节换行
            $appPrivateKeyArr = str_split($appPrivateKey, 64);
            $appPrivateKey = "";
            foreach ($appPrivateKeyArr as $value) {
                $appPrivateKey = $appPrivateKey . $value ."\n";
            }


            $serverPublicKey = str_replace(array("\r", "\n", "\r\n", " "), "", $serverPublicKey);
            // 每隔64个字节换行
            $serverPublicKeyArr = str_split($serverPublicKey, 64);
            $serverPublicKey = "";
            foreach ($serverPublicKeyArr as $value) {
                $serverPublicKey = $serverPublicKey . $value ."\n";
            }

            $this->appPrivateKey =  "-----BEGIN PRIVATE KEY-----\n" . $appPrivateKey . "-----END PRIVATE KEY-----";
            $this->serverPublicKey = "-----BEGIN PUBLIC KEY-----\n" . $serverPublicKey . "-----END PUBLIC KEY-----";

        }

        /**
         * 执行生成请求URL
         * @param Request $request
         * @return string       请求URL
         * @throws Exception
         */
        public function sdkExecute(Request $request)
        {
            $request->appId = ($this->appId);
            $encrypted_data = encrypt(json_encode($request->data), $this->serverPublicKey);
            $request->sign = (sign($encrypted_data, $this->appPrivateKey));
            $data = $request->getData();
            $request->data = $encrypted_data;

            $params = '';
            foreach ($request as $key => $value){
                if(empty($params)){
                    $params = $key . '=' . $value;
                }else{
                    $params = $params . '&' . $key . '=' . $value;
                }
//                $params = empty($params) ? ($key . '=' . $value) : ($params . '&' . $key . '=' . $value);
            }
//            foreach ($data->getExtParams() as $key => $value) {
//                $params =  $params . '&' . $key . '=' . $value;
//            }
            return $this->serverUrl . '?' . $params;
        }

        /**
         * 响应体的验签和解密
         * @param string $responseBodyStr   回调或者响应的body字段
         * @return mixed                    解密之后的数据体
         * @throws Exception
         */
        public function checkResponse($responseBody)
        {
            if (empty($responseBody)) {
                throw new Exception("response null");
            }
            $data = $responseBody -> data;
            if (empty($responseBody)) {
                throw new Exception("response decode error");
            }
            $sign = $responseBody -> sign;
            if (verifySign($data, $sign, $this->serverPublicKey)) {
                $data = decrypt($data, $this->appPrivateKey);
                if ($data) {
                    return json_decode($data);
                }
                throw new Exception("数据解密失败！");
            } else {
                throw new Exception("验证签名失败！");
            }
        }
    }
}

