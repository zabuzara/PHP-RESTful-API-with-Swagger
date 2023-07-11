<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 */
#[Controller]
#[RequestMapping('user')]
class UserController {
    private $repository;

    public function __construct() {
        $this->repository = new UserRepository();
    }

    #[GetMapping('/get_all')]
    public function get_all() {
        echo json_encode($this->repository->find_all(UserEntity::class));
    }

    #[GetMapping('/get_by_id/{id}')]
    public function get_by_id(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id) {
        echo json_encode($this->repository->find_by_id(UserEntity::class, $id));
    }

    #[PostMapping('/save_user')]
    public function save_user(array $user_data) {
        RESTful::exists(params: $user_data, with: ['nickname', 'password']);

        $user = new UserEntity();
        $user->nickname = $user_data['nickname'];
        $user->password = Password::hash($user_data['password']);
        $user->creation_time = Date('Y-m-d h:m:s', time());
        $user->expiration_time = Date('Y-m-d h:m:s', time() + 86400);
        $user->last_request_time = Date('Y-m-d h:m:s', time());
        $user->session_time = Date('Y-m-d h:m:s', time() + 1800);
        $user->session_token = Token::generate_token();
        $user->is_logged_in = true; 
        $this->repository->persist($user);
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id, array $user_object) {
        $to_update_user = $this->repository->find_by_id(UserEntity::class, $id);
        if (!is_null($to_update_user)) {
            foreach($user_object as $prop => $value) {
                if ($prop === 'password')
                    $value = Password::hash($value);
                $to_update_user->{$prop} = $value;
            }
            $this->repository->update($to_update_user, $id);
        } else {
            RESTful::response('User not found');
        }
    }

    #[DeleteMapping('/delete_user/{id}')]
    public function delete_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id) {
        $this->repository->remove(UserEntity::class, $id);
    }
}