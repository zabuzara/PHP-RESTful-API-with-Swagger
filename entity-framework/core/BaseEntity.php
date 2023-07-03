<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Base entity class with internal id as primary key
 * for all sub classes
 */
abstract class BaseEntity {

    #[UNIQUE]
    #[NOT_NULL]
    #[PRIMARY_KEY]
    #[AUTO_INCREMENT]
    #[Types\Numeric\T_INT(11)]
     public int $id;

    /**
     * @inheritDoc
     */
    public function __toString() : string {
        $output = '{<br>';
        $items = [];
        foreach((array) $this as $key => $value) {
            array_push($items, '&nbsp;&nbsp;&nbsp;&nbsp;"'.$key.'" : '.(is_numeric($value) ? $value : (is_null($value) ? 'NULL' : '"'.$value.'"')));
        }
        $output .= join(',<br>', $items).'<br>}';
        return $output;
    }

    /**
     * @inheritDoc
     */
    public function __set(string $name, mixed $value) : void {
        throw new EntityException("Cannot add new property \$$name to instance of " . __CLASS__);
    }

    /**
     * @inheritDoc
     */
    public function __get(string $name) : mixed {
        throw new EntityException("Cannot access to property \$$name from instance of " . __CLASS__);
    }

    /**
     * @inheritDoc
     */
    public function __unset(string $name) : void {
        throw new EntityException("Unset property '$name' is not possible, you can remove it from your class '".__CLASS__."' instead.");
    }
}