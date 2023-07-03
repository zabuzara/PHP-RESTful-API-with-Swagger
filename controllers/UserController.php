<?php
include_once './../repositories/UserRepository.php';

#[Controller]
#[RequestMapping('user')]
class UserController {

    private $repository;

    public function __construct() {
        $this->repository = new UserRepository();
    }


    #[GetMapping('/get_all')]
    public function get_all() {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__.'()';
        echo json_encode($this->repository->find_all(UserEntity::class));
    }

    #[GetMapping('/get_by_id/{id}')]
    public function get_by_id(int $id) {
        echo 'Method GET => '.__CLASS__.'->'.__FUNCTION__."(id)";
        echo json_encode($this->repository->find_by_id(UserEntity::class, $id));
    }

    #[GetMapping('/get_by_name/{name}')]
    public function get_by_name(string $name) {
        echo 'Method: GET => '.__CLASS__.'->'.__FUNCTION__."(name)";
    }

    #[PostMapping('/save_user')]
    public function save_user(array $user) {
        echo 'Method: POST => '.__CLASS__.'->'.__FUNCTION__."(user)";
        $entity = EntityManager::array_to_entity(UserEntity::class, $user);
        $this->repository->persist($entity);
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(int $id, array $user) {
        echo 'Method: PUT => '.__CLASS__.'->'.__FUNCTION__.'(id, user)';
        $entity = EntityManager::array_to_entity(UserEntity::class, $user);
        $this->repository->update($entity, $id);
    }

    #[DeleteMapping('/delete_user/{id}')]
    public function delete_user(int $id) {
        echo 'Method: DELETE => '.__CLASS__.'->'.__FUNCTION__.'(id)';
        $this->repository->remove(UserEntity::class, $id);
    }
}