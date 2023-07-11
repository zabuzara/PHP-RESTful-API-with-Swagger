# PHP-API-Template
## Project
RESTful API template for creating a API fast as possible in PHP. The ".haccess" file helps to activate CRUD operations on Apache web server. This project includes "Simple-Entity-Framework" for
communicate with database. You can use Bearer Authorization with in your API. For this functionality you should give two key names, that will be include in $_SESSION for handle access to endpoints.

## Usage
```php
<?php
// instead "." at the start the include_once path
// you can use your own path
include_once './entity-framework/index.php';
include_once './RESTful/RESTful.php';

// Set RESTful with authorization
RESTful::with_authorization(
    session_key_for_token: 'token', // name of key for saving token in $_SESSION
    session_key_for_expiration: 'expiration_time' // name of key for saving expiration time in $_SESSION
);

// create a new instance of RESTful class
// if your application or API existing in 
// root folder von apache "/var/www/html/"
// you should set change it to "/"
// in this example we have a folder as root
new RESTful(document_root: '/your document root/', ignore_routes: ['security']);
```
After doing this part you can create your Controller Classes
and define your endpoints somthing like following Example:
```php
<?php
#[Controller]
#[RequestMapping('user')]
class UserController {
    private $repository;

    public function __construct() {
        // here i used predefined UserRepository that useing 
        // EntityManager from EntityFramework 
        $this->repository = new UserRepository();
    }

    #[GetMapping('/get_all')]
    public function get_all() {
        // get all users, using your repository to communicate with database
    }

    #[GetMapping('/get_by_id/{id}')]
    public function get_by_id(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id) {
        // get user by given id, using your repository to communicate with database
    }

    #[PostMapping('/save_user')]
    public function save_user(array $user_data) {
        RESTful::validate(params: $user_data, with: ['nickname', 'password']);
        // add user, using your repository to communicate with database
    }

    #[PutMapping('/update_user/{id}')]
    public function update_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id, array $user_object) {
        // update user, using your repository to communicate with database
    }

    #[DeleteMapping('/delete_user/{id}')]
    public function delete_user(#[PathVariable(name: 'id', require: true, validate: Validate::INT)] int $id) {
        // delete user, using your repository to communicate with database
    }
}
```
