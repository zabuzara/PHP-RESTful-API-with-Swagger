<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * A example for learning how you can write your own entity class
 * and set column types.
 */
#[TABLE("user")]
class UserEntity extends BaseEntity {

    #[Types\String\T_VARCHAR(255)]
    public string $forename;

    #[Types\String\T_VARCHAR(255)]
    public string $surname;

    #[Types\String\T_VARCHAR(255)]
    public string $email;
}
