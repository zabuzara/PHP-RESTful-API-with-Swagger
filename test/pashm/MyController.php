<?php
#[Controller]
#[RequestMapping('pashm')]
class MyController {
    #[GetMapping('/get_all')]
    public function get_all () {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__.'()';
    }
}