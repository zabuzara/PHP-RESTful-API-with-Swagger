<?php
#[REPOSITORY]
class UserRepository extends BaseRepository {
    public function find_by_email(string $entity_name, string $email) : array {
        return $this->find($entity_name, $email, __FUNCTION__);
    }

    public function find_by_forename(string $entity_name, string $forename) : array {
        return $this->find($entity_name, $forename, __FUNCTION__);
    }

    public function find_by_surname(string $entity_name, string $surname) : array {
        return $this->find($entity_name, $surname, __FUNCTION__);
    }

    private function find (string $entity_name, string $subject, $func_name) : array {
        if ($this->table_exists($entity_name)) {
            $table_name = $this->get_table_name($entity_name);
            $primary_key_name = $this->get_primary_key($table_name);
            $column_name = explode('find_by_', $func_name)[1];

            if (!empty($primary_key_name)) {
                return $this->get_context()->exec("SELECT * FROM `".$table_name."` WHERE `".$column_name."` LIKE ?", ["%".$subject."%"]);
            }
        }
        return [];
    }
}