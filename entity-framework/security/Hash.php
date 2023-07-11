<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * Custom Password class for php
 * using hash/verify from security api 
 */
abstract class Password {
    private function __construct() {}

    static public function hash (string $value) : string {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    static public function verify (string $value, string $hash) : bool {
        return password_verify($value, $hash);
    }
}

abstract class Token {
    private function __construct() {}

    static public function generate_token () {
        $secure = new \Random\Engine\Secure();
        $time = time();
        $token = (base64_encode(bin2hex(
            $secure->generate() . 
            substr($time, strlen($time) - 1) .
            openssl_random_pseudo_bytes(12) .
            substr($time, strlen($time) - 1) .
            openssl_random_pseudo_bytes(2) .
            $secure->generate() . 
            substr($time, strlen($time) - 1) . 
            openssl_random_pseudo_bytes(12) .
            substr($time, strlen($time) - 1) . 
            openssl_random_pseudo_bytes(2)
        )));
        return $token;
    }
}