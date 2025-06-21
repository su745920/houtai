<?php
namespace App\Utils;

use Illuminate\Support\Facades\Config;

/**
 * Created by PhpStorm.
 * Desc: Rsa类
 * Coder: Wanzhou Chen
 * Date: 2022-06-24
 * Time: 14:46
 */
class RSC {

    private $prikey;
    private $pubkey;

    public function __construct($prikey='', $pubkey='') {

        $this->prikey = $prikey;
        $this->pubkey = $pubkey;
    }

    /**
     * 获取私钥
     * @return bool|resource
     */
    private function getPrivateKey() {

        return openssl_pkey_get_private($this->prikey);
    }


    /**
     * 获取公钥
     * @return bool|resource
     */
    private function getPublicKey() {

        return openssl_pkey_get_public($this->pubkey);

    }


    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public function privateEncrypt($data = '') {

        if (!is_string($data)) {

            return null;

        }

        return openssl_private_encrypt($data, $encrypted, $this->getPrivateKey()) ? base64_encode($encrypted) : null;
    }


    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public function publicEncrypt($data = '') {

        if (!is_string($data)) {

            return null;

        }

        return openssl_public_encrypt($data, $encrypted, $this->getPublicKey()) ? base64_encode($encrypted) : null;

    }


    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public function privateDecrypt($encrypted = '') {

        if (!is_string($encrypted)) {

            return null;

        }

        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->getPrivateKey())) ? $decrypted : null;

    }


    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public function publicDecrypt($encrypted = '') {

        if (!is_string($encrypted)) {

            return null;

        }

        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, $this->getPublicKey())) ? $decrypted : null;

    }

}

