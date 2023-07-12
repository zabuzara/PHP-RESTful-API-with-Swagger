<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * A example for learning how you can write your own entity class
 * and set column types.
 */
#[TABLE("example")]
class ExampleEntity extends BaseEntity {

    #[NOT_NULL]
    #[Types\String\T_VARCHAR(64)]
    public string $example_title;

    #[Types\String\T_VARCHAR(255)]
    public string|null $example_description;

    #[Types\Datetime\T_TIMESTAMP]
    public string|null $example_datetime;

    #[Types\Numeric\T_INT]
    public int|null $example_archive_number;
}
