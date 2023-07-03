<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * BaseRepository for declare
 * basic functions for handle data
 * object with database
 */
interface IRepository {
    /**
     * Trys to save object to
     * the database depend on
     * configured db_context
     *
     * @param BaseEntity $object
     * @return void
     */
    function persist(BaseEntity $object) : void;

    /**
     * Finds object from given
     * class name and id
     * 
     * @param string $class_name
     * @param int|string $id
     * @return BaseEntity|null
     */
    function find_by_id(string $class_name, int | string $id) : BaseEntity | null;

    /**
     * Returns all founded objects
     * form database of given
     * entity|class name
     *
     * @param string $class_name
     * @return BaseEntity[]
     */
    function find_all(string $class_name) : array;


    /**
     * Removes object from given
     * class name and id
     * @param string $class_name
     * @param int|string $id
     * @return bool
     */
    function remove (string $entity_name, int | string $id) : bool;

    /**
     * Updates object from given
     * class name and updated object
     *
     * @param BaseEntity $entity
     * @param int|string $id
     * @return boolean
     */
    function update(BaseEntity $entity, int | string $id) : bool;
}