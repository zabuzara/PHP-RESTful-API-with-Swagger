<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * Datetimes data types Attributes for MySQL
 */
namespace Types\Datetime {
    use Attribute;

    #[Attribute()]
    class T_DATE {}

    #[Attribute()]
    class T_DATETIME {}

    #[Attribute()]
    class T_TIMESTAMP {}

    #[Attribute()]
    class T_TIME {}

    #[Attribute()]
    class T_YEAR {}
}