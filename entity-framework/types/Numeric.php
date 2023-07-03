<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * Numeric data types Attributes for MySQL
 */
namespace Types\Numeric {
    use Attribute;

    #[Attribute()]
    class T_BIT {}

    #[Attribute()]
    class T_TINYINT {}

    #[Attribute()]
    class T_BOOL {}

    #[Attribute()]
    class T_BOOLEAN {}

    #[Attribute()]
    class T_SMALLINT {}

    #[Attribute()]
    class T_MEDIUMINT {}

    #[Attribute()]
    class T_INT {}

    #[Attribute()]
    class T_INTEGER {}

    #[Attribute()]
    class T_BIGINT {}

    #[Attribute()]
    class T_FLOAT {}

    #[Attribute()]
    class T_DOUBLE {}

    #[Attribute()]
    class T_DECIMAL {}

    #[Attribute()]
    class T_DEC {}
}