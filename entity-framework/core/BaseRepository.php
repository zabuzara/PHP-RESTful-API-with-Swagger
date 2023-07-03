<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * BaseRepository class include
 * standard database modifier methods
 * for insert, update, find, delete
 * data from database. It can be extended
 * form sub classes.
 */
#[REPOSITORY]
class BaseRepository extends EntityManager implements IRepository {
    /**
     * @inheritDoc
     */
    public final function persist (BaseEntity $entity) : void {
        $entity_names = array_map(fn($item) => $item['class_name'], $this->get_entities());
        $columns = [];
        foreach ($entity_names as $entity_name) {
            if ($entity::class === $entity_name) {
                $columns = $this->get_columns($entity);
                break;
            }
        }
        $this->insert_object_into_table($entity, $columns);
    }

    /**
     * @inheritDoc
     */
    public final function find_by_id (string $entity_name, int | string $id) : BaseEntity | null {
        if ($this->table_exists($entity_name)) {
            $table_name = $this->get_table_name($entity_name);
            $primary_key_name = $this->get_primary_key($table_name);
            if (!empty($primary_key_name)) {
                $result = $this->get_context()->exec('SELECT * FROM `'.$table_name.'` WHERE `'.$primary_key_name.'` = ?',[$id]);

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

    /**
     * @inheritDoc
     */
    public final function find_all (string $entity_name) : array {
        if ($this->table_exists($entity_name)) {
            $table_name = $this->get_table_name($entity_name);
            $primary_key_name = $this->get_primary_key($table_name);
            if (!empty($primary_key_name)) {
                $result = $this->get_context()->exec('SELECT * FROM `'.$table_name.'`');

                if (!empty($result) && count($result) > 0) {
                    if (class_exists($entity_name)) {
                        $entities = [];
                        foreach($result as $row) {
                            array_push($entities, EntityManager::array_to_entity($entity_name, $row));
                        }
                        return $entities;
                    } else {
                        return $result[0];
                    }
                }
            }
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public final function remove (string $entity_name, int | string $id) : bool {
        if ($this->table_exists($entity_name)) {
            $table_name = $this->get_table_name($entity_name);
            $primary_key_name = $this->get_primary_key($table_name);
            if (!empty($primary_key_name)) {
                $result = $this->get_context()->exec('DELETE FROM `'.$table_name.'` WHERE `'.$primary_key_name.'` = ?',[$id]);
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public final function update(BaseEntity $entity, int | string $id) : bool {
        if ($this->table_exists($entity::class)) {
            $table_name = $this->get_table_name($entity::class);
            $primary_key_name = $this->get_primary_key($table_name);
            $entity_names = array_map(fn($item) => $item['class_name'], $this->get_entities());

            foreach ($entity_names as $entity_name) {
                if ($entity::class === $entity_name) {
                    $entity->id = $id;
                    $set_parts = join(', ', array_map(fn($prop) => '`'.$prop.'` = ?', array_filter(array_keys((array) $entity), fn($item) => $item !== $primary_key_name)));
                    $set_values = array_filter(array_map(function($item, $key) use ($primary_key_name) {
                        if ($key !== $primary_key_name)
                            return $item;
                    }, (array) $entity, array_keys((array) $entity)), fn($item) => !is_null($item));
                    array_push($set_values, $entity->id);
                    $set_values = array_values($set_values);
                    $result = $this->get_context()->exec('UPDATE `'.$table_name.'` SET '.$set_parts.' WHERE `'.$primary_key_name.'` = ?', $set_values);
                    return true;
                }
            }
        }
        return false;
    }
}