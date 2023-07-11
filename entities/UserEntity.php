<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * A example for learning how you can write your own entity class
 * and set column types.
 */
#[TABLE("user")]
class UserEntity extends BaseEntity {
    
    #[NOT_NULL]
    #[Types\String\T_VARCHAR(255)]
    public string $nickname;

    #[NOT_NULL]
    #[Types\String\T_VARCHAR(255)]
    public string $password;

    #[Types\String\T_VARCHAR(255)]
    public string $avatar = "default.png";

    #[Types\String\T_ENUM("Admin", "User", "Payed")]
    public string $type = "User";

    #[Types\Numeric\T_TINYINT(1)]
    public bool $is_baned = false;

    #[Types\Numeric\T_INT(11)]
    public int $room_count = 0;

    #[Types\Datetime\T_TIMESTAMP]
    public string|null $creation_time;

    #[Types\Datetime\T_TIMESTAMP]
    public string|null $expiration_time;

    #[Types\Datetime\T_TIMESTAMP]
    public string|null $last_request_time;

    #[Types\Datetime\T_TIMESTAMP]
    public string|null $session_time;

    #[Types\String\T_VARCHAR(255)]
    public string|null $session_token;

    #[Types\Numeric\T_TINYINT(1)]
    public bool $is_logged_in = false;
}