<?php

#[Controller]
#[RequestMapping('user')]
class UserController {

    #[GetMapping('/{id}')]
    public function get_by_id(int $id) {
        echo 'called';
    }

    #[GetMapping('/{name}')]
    public function get_by_name(string $name) {

    }
}