<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * A example for learning how you can write your own repository class
 * and extends methods/function as need as well.
 */
#[REPOSITORY]
class ExampleRepository extends BaseRepository {

    public function find_example_by_title(string $entity_name, string $title) : BaseEntity | null {

        // your database connection logic here

        if ($this->table_exists($entity_name)) {
            $table_name = $this->get_table_name($entity_name);
            $primary_key_name = $this->get_primary_key($table_name);

            if (!empty($primary_key_name)) {
                $result = $this->get_context()->exec("SELECT * FROM `".$table_name."` WHERE `example_title` LIKE ?", ["%".$title."%"]);

                if (!empty($result) && count($result) > 0) {
                    if (class_exists($entity_name)) {
                        return EntityManager::array_to_entity($entity_name, $result[0]);
                    } else {
                        return $result[0];
                    }
               }
            }
        }
        return null;
    }

    // -- another methods here --

}
