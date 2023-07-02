<?php

#[Controller]
#[RequestMapping('user')]
class UserController {

    #[GetMapping('/get_all')]
    public function get_all() {
        echo 'GET used';
    }

    #[GetMapping('/get_by_id/{id}')]
    public function get_by_id(int $id) {
        echo 'GET used';
    }

    #[GetMapping('/get_by_name/{name}')]
    public function get_by_name(string $name) {
        echo 'GET used';
    }

    #[PostMapping('/save_user')]
    public function save_user(array $user) {
        echo 'POST used';
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(int $id, array $user) {
        echo 'PUT used';
    }


    #[DeleteMapping('/delete_user/{id}')]
    public function delete_user(int $id) {
        echo 'DELETE used';
    }
}