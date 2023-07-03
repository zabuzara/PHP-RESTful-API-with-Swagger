<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Custom class 'SQLException' for exception handling
 * in 'SQL' class
 */
class SQLException extends Exception {
    public function __construct(string $msg = 'Default' , int $val = 0, Exception $old = null) {
        parent::__construct("SQL syntax error: ".$msg, $val, $old);
    }
}