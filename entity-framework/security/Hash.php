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
        $token = bin2hex(openssl_random_pseudo_bytes(64));
        return $token;
    }
}