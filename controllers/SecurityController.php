<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 */
#[Controller]
#[RequestMapping('security')]
class SecurityController {
    private $repository;

    public function __construct() {
        $this->repository = new UserRepository();
    }

    #[PostMapping('/authenticate')]
    public function authenticate (array $object) {
        RESTful::exists(params: $object, with: ['username', 'password']);
        $username = $object['username'];
        $password = $object['password'];

        echo json_encode(preg_match('/\n#\[TABLE\(".+"\)\]\nclass/', file_get_contents('./entities/UserEntity.php')));

        // $response = SQL::set_context(Context::auto_load())->exec('SELECT `nickname`, `password` FROM `user` WHERE `nickname` = ?', [$username]);
        // if (!empty($response) && count($response) > 0 && array_key_exists('password', $response[0])) {
        //     if (Password::verify($password, $response[0]['password'])) {
        //         $session_token = Token::generate_token();

        //         $expiration_time = SQL::get_valid_sql_datetime_string((new DateTime('now'))->modify('+86400 seconds'));
        //         $last_request_time = SQL::get_valid_sql_datetime_string(new DateTime('now'));
        //         $session_time = SQL::get_valid_sql_datetime_string((new DateTime('now'))->modify('+86400 seconds'));
        //         $params = [
        //             $expiration_time,
        //             $last_request_time,
        //             $session_time,
        //             $session_token,
        //             true,
        //             $username,
        //             $response[0]['password']
        //         ];
        //         SQL::set_context(Context::auto_load())->exec('UPDATE `user` SET `expiration_time` = ?, `last_request_time` = ?, `session_time` = ?, `session_token` = ?, `is_logged_in` = ? WHERE `nickname` = ? AND `password` = ?', $params);
        //         if (session_status() !== PHP_SESSION_ACTIVE) {
        //             session_start();
        //         }
        //         $_SESSION[RESTful::$session_key_for_token] = $session_token;
        //         $_SESSION[RESTful::$session_key_for_expiration] = $session_time;
        //         echo json_encode([
        //             'token' => $session_token,
        //             'expiration_time' => $session_time
        //         ]);
        //     }
        // }
    }
}