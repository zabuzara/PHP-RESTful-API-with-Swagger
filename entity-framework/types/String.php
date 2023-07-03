<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * String data types Attributes for MySQL
 */
namespace Types\String {
    use Attribute;

    #[Attribute()]
    class T_CHAR {}

    #[Attribute()]
    class T_VARCHAR {}

    #[Attribute()]
    class T_BINARY {}

    #[Attribute()]
    class T_VARBINARY {}

    #[Attribute()]
    class T_TINYBLOB {}

    #[Attribute()]
    class T_TINYTEXT {}

    #[Attribute()]
    class T_TEXT {}

    #[Attribute()]
    class T_BLOB {}

    #[Attribute()]
    class T_MEDIUMTEXT {}

    #[Attribute()]
    class T_MEDIUMBLOB {}

    #[Attribute()]
    class T_LONGTEXT {}

    #[Attribute()]
    class T_ENUM {}

    #[Attribute()]
    class T_SET {}
}