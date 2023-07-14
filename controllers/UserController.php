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
    public function save_user(object $object) {
        RESTful::exists(params: get_object_vars($object), with: ['nickname', 'password']);

        $user = new UserEntity();
        $user->nickname = $object->nickname;
        $user->password = Password::hash($object->password);
        $user->creation_time = Date('Y-m-d h:m:s', time());
        $user->expiration_time = Date('Y-m-d h:m:s', time() + 86400);
        $user->last_request_time = Date('Y-m-d h:m:s', time());
        $user->session_time = Date('Y-m-d h:m:s', time() + 1800);
        $user->session_token = Token::generate_token();
        $user->is_logged_in = true; 
        $this->repository->persist($user);
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id, object $object) {
        $to_update_user = $this->repository->find_by_id(UserEntity::class, $id);
        if (!is_null($to_update_user)) {
            foreach($object as $prop => $value) {
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

    #[GetMapping('/get_data')]
    public function get_data() {
        $request_url = './RESTful/swagger/favicon.ico';
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"" . basename($request_url) . ".ico\""); 
        readfile($request_url);
        exit();
    }


    //#[PostMapping('/post_data')]
    public function post_data(mixed $content) {
        include_once './RESTful/classes/ENCType.php';
        include_once './RESTful/classes/Size.php';
        include_once './RESTful/classes/MIME.php';

        $file_extension = MIME::get_extension($_SERVER['CONTENT_TYPE']);
        $file_name = "post_file".$file_extension;

        fopen($file_name, "w");
        file_put_contents($file_name, $content);
    }
}