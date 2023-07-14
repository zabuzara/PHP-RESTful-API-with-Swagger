<?php

/**
 * Size class for convert size to byte
 */
abstract class SIZE {
    static function TiB (int $size) {
        return $size * pow(1024, 4); 
    }

    static function TB (int $size) {
        return $size * pow(1000, 4); 
    }

    static function GiB (int $size) {
        return $size * pow(1024, 3); 
    }

    static function GB (int $size) {
        return $size * pow(1000, 3); 
    }

    static function MiB (int $size) {
        return $size * pow(1024, 2);
    }

    static function MB (int $size) {
        return $size * pow(1000, 2);
    }

    static function kiB (int $size) {
        return $size * 1024; 
    }

    static function kB (int $size) {
        return $size * 1000; 
    }
}
