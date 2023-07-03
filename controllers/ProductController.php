<?php

#[Controller]
#[RequestMapping('product')]
class ProductController {
    #[GetMapping('/get_all')]
    public function get_all() {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__.'()';
    }

    #[GetMapping('/get_by_id/{id}')]
    public function get_by_id(int $id) {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__.'(id)';
    }
}