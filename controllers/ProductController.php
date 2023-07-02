<?php

#[Controller]
#[RequestMapping('product')]
class ProductController {

    #[GetMapping('/{id}')]
    public function get_by_id(int $id) {

    }
}