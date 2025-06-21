<?php


/**
 * 生成签名
 * @param $data
 * @param $appPrivateKey
 * @throws Exception
 */
function sign($data, $appPrivateKey) {
    if(empty($appPrivateKey)){
        throw new Exception("appPrivateKey empty!");
    }
    $pi_key=openssl_pkey_get_private($appPrivateKey);
    if(empty($pi_key)){
        throw new Exception("Private key resource identifier false!");
    }
    $encrypted_sign='';
    if(openssl_sign($data, $encrypted_sign, $pi_key, OPENSSL_ALGO_MD5)){
        return urlencode(base64_encode($encrypted_sign));
    }else{
        throw new Exception("Sign failed!");
    }
}

/**
 * 验证签名
 * @param $data
 * @param $sign
 * @param $serverPublicKey
 * @throws Exception
 */
function verifySign($data, $sign, $serverPublicKey) {
    if(empty($serverPublicKey)){
        throw new Exception("serverPublicKey empty!");
    }
    $pu_key=openssl_pkey_get_public($serverPublicKey);
    if(empty($pu_key)){
        throw new Exception("Public key resource identifier false!");
    }

    $res=openssl_verify($data, base64_decode(urldecode($sign)), $pu_key, OPENSSL_ALGO_MD5);
    if($res == 1){
        return true;
    }
    return false;
}

/**
 * 加密数据
 * @param $data
 * @param $serverPublicKey
 * @throws Exception
 */
function encrypt2($data, $serverPublicKey) {
    if(empty($serverPublicKey)){
        throw new Exception("serverPublicKey empty!");
    }
    $encrypted_data="";
    $pu_key=openssl_pkey_get_public($serverPublicKey);
    if(empty($pu_key)){
        throw new Exception("Public key resource identifier false!");
    }
    //obj public key encrypt.
    $dataArray=str_split($data, 117);
    foreach ($dataArray as $value) {
        $encryptedTemp="";
        openssl_public_encrypt($value,$encryptedTemp,$pu_key);
        $encrypted_data .= $encryptedTemp;
    }
    return urlencode(base64_encode($encrypted_data));
}

/**
 * 解密数据
 * @param $data
 * @param $appPrivateKey
 * @throws Exception
 */
function decrypt2($data, $appPrivateKey) {
    if(empty($appPrivateKey)){
        throw new Exception("appPrivateKey empty!");
    }
    $pi_key=openssl_pkey_get_private($appPrivateKey);
    if(empty($pi_key)){
        throw new Exception("Private key resource identifier false!");
    }
    $decrypted='';
    $enArray=str_split(base64_decode(urldecode($data)), 128);
    foreach ($enArray as $val) {
        $decryptedTemp = '';
        openssl_private_decrypt($val, $decryptedTemp, $pi_key);
        $decrypted .= $decryptedTemp;
    }
    return $decrypted;
}
