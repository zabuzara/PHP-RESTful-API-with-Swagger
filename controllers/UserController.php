<?php
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

    #[GetMapping('/get_by_forename/{forename}')]
    public function get_by_forename(#[PathVariable(name: 'forename', require: true, validate: Validate::STRING)] string $forename) {
        echo json_encode($this->repository->find_by_forename(UserEntity::class, $forename));
    }

    #[GetMapping('/get_by_surname/{surname}')]
    public function get_by_surname(#[PathVariable(name: 'surname', require: true, validate: Validate::STRING)] string $surname) {
        echo json_encode($this->repository->find_by_surname(UserEntity::class, $surname));
    }

    #[GetMapping('/get_by_email/{email}')]
    public function get_by_email(#[PathVariable(name: 'email', require: true, validate: Validate::STRING)] string $email) {
        echo json_encode($this->repository->find_by_email(UserEntity::class, $email));
    }

    #[PostMapping('/save_user')]
    public function save_user(array $user) {
        $entity = EntityManager::array_to_entity(UserEntity::class, $user);
        $this->repository->persist($entity);
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id, array $user) {
        $entity = EntityManager::array_to_entity(UserEntity::class, $user);
        $this->repository->update($entity, $id);
    }

    #[DeleteMapping('/delete_user/{id}')]
    public function delete_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id) {
        $this->repository->remove(UserEntity::class, $id);
    }
}
