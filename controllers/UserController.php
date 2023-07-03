<?php
#[Controller]
#[RequestMapping('user')]
class UserController {
    #[GetMapping('/get_all')]
    public function get_all() {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__.'()';
    }

    #[GetMapping('/get_by_id/{id}')]
    public function get_by_id(int $id) {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__."(id)";
    }

    #[GetMapping('/get_by_name/{name}')]
    public function get_by_name(string $name) {
        echo 'Method: GET => '.__CLASS__.'->'.__FUNCTION__."(name)";
    }

    #[PostMapping('/save_user')]
    public function save_user(array $user) {
        echo 'Method: POST => '.__CLASS__.'->'.__FUNCTION__."(user)";
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(int $id, array $user) {
        echo 'Method: PUT => '.__CLASS__.'->'.__FUNCTION__.'(id, user)';
    }

    #[DeleteMapping('/delete_user/{id}')]
    public function delete_user(int $id) {
        echo 'Method: DELETE => '.__CLASS__.'->'.__FUNCTION__.'(id)';
    }
}