<?php

class Cryptor {
    private String $ciphering = "AES-128-CTR";
    public function encript(String $string, String $key) {
        $iv_length = openssl_cipher_iv_length($this->ciphering);
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption = openssl_encrypt($string, $this->ciphering, $key, $options, $encryption_iv);
        return $encryption;
    }
    public function desencript(String $string, String $key) {
        $iv_length = openssl_cipher_iv_length($this->ciphering);
        $options = 0;
        $decryption_iv = '1234567891011121';
        $decryption = openssl_decrypt($string, $this->ciphering, $key, $options, $decryption_iv);
        return $decryption;
    }
    public function createKey() {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $key = random_int(0, 9).$chars[random_int(0, strlen($chars))].random_int(0, 9).$chars[random_int(0, strlen($chars))].random_int(0, 9).$chars[random_int(0, strlen($chars))].random_int(0, 9).$chars[random_int(0, strlen($chars))].random_int(0, 9).$chars[random_int(0, strlen($chars))].random_int(0, 9).$chars[random_int(0, strlen($chars))].random_int(0, 9).$chars[random_int(0, strlen($chars))];
        return $key;
    }
    public function password(String $pass) {
        return password_hash($pass, PASSWORD_DEFAULT);
    }
}


?>
