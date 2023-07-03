<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * EntityManager class
 * 
 * simple entity handling for
 * save object in database
 * without need to write any SQL command
 * by yourself
 */
abstract class EntityManager {
    private array $entities = [];
    private SQL|null $sql;
    private string $dbname;

    /**
     * EntityManager entry point
     * initializes a new instance
     */
    public function __construct () {
        $context_config_path = Scan::directory('.')->for('.json', true)[0]['path'];
        if (Context::is_valid($context_config_path)) {
            $context = new Context($context_config_path);
            $this->dbname = $context->db_name;
            $this->sql = SQL::set_context($context);
            
            if (!empty($context_config_path)) {
                $this->entities = EntityScanner::scan_entities_from_directories(".");
                $this->remove_not_existing_tables();
            }
        }
    }


    /**
     * Returns sql connection
     *
     * @return SQL
     */
    protected function get_context () : SQL {
        $this->sql->exec('USE `'.$this->dbname.'`');
        return $this->sql;
    }

    /**
     * Returns existing entities
     * as an array with class_name
     * and class_path
     *
     * @return array
     */
    protected function get_entities () : array {
        return $this->entities;
    }

    /**
     * Parse the given array to entity object
     *
     * @param string $entity_name
     * @param array $peoperties
     * @return BaseEntity|null
     */
    static public function array_to_entity (string $entity_name, array $peoperties) : BaseEntity | null {
        if (!empty($entity_name) && !empty($peoperties) && count($peoperties) > 0) {
            $entity = new $entity_name();
            $peoperties = gettype($peoperties[0]) === 'array' ? $peoperties[0] : $peoperties;
            foreach($peoperties as $property => $value) {
                $entity->{$property} = $value;
            }
            return $entity;
        }
        return null;
    }

    /**
     * Converts entity object to array
     *
     * @param BaseEntity $entity
     * @return array
     */
    static public function entity_to_array (BaseEntity $entity) : array {
        return (array) $entity;
    }

    /**
     * Removes tables that not exists
     * as defiend @entity in framework
     *
     * @return void
     */
    private function remove_not_existing_tables () {
        $entity_names = array_map(fn($entity) => $this->get_table_name($entity['class_name']), $this->entities);
        $content = $this->sql->exec('SHOW TABLES');
        if (!empty($content)) {
            $content = array_map(fn($item) => array_values($item), $content);
            $content = array_map(fn($item) => $item[0], $content);
        }
        $diff_names = array_diff($content, $entity_names) ?? array_diff($entity_names, $content);

        foreach($diff_names as $to_remove_table_name) {
            $this->sql->exec('DROP TABLE IF EXISTS `'.$to_remove_table_name.'`');
        }
    }

    /**
     * Returns Attributes definition, that
     * exists on properties of entity class
     *
     * @param string $entity_name
     * @return array
     */
    private function get_columns_def_from_attributes (string $entity_name) : array {
        $properties = array_keys(get_class_vars($entity_name));

        $definition = [
            'def' => [],
            'type' => []
        ];
        foreach($properties as $property) {
            $reflection = new ReflectionProperty($entity_name, $property);
            $definition['def'][$property] = [
                'name' => $property,
                'nullable' => $reflection->getType()->allowsNull(),
                'type' => $reflection->getType()->getName(),
            ];
            AttributeValidator::validate_entity_property($entity_name, $property);

            $def = '`'.$definition['def'][$property]['name'].'`';
            $attributes = array_map(fn($attribute) => (object)['name' => $attribute->getName(), 'args' => $attribute->getArguments()], $reflection->getAttributes());
            $type_attr = array_values(array_filter($attributes, fn($item) => str_contains($item->name, '\T_')));
   
            if (!empty($type_attr) && count($type_attr) > 0) {
                $type_attr = $type_attr[0];

                $args_attr = '';
                if ($type_attr->name === \Types\String\T_ENUM::class) {
                    $args_attr = !empty($type_attr->args) ? '("'.join('", "', $type_attr->args).'")' : ''; 
                } else {
                    $args_attr = !empty($type_attr->args) ? '('.join(' ', $type_attr->args).')' : ''; 
                }
                $def .= ' '.explode('\T_', $type_attr->name)[1].$args_attr;                
                $definition['type'][$property] = explode('\T_', $type_attr->name)[1].$args_attr;

                foreach($attributes as $attr) {
                    if (!empty($def) && !str_contains($attr->name, '\T_')) {       
                        $attr->name = str_replace('NOT_NULL', 'NOT NULL', $attr->name);
                        $attr->name = str_replace('PRIMARY_KEY', 'PRIMARY KEY', $attr->name);
                        $def .= ' '.$attr->name;
                    }   
                }
                $definition['def'][$property] = $def;
            }
   
        }
        return $definition;
    }

    /**
     * Generates Create Table SQL Command
     *
     * @param string $class_name
     * @return string
     */
    private function get_create_table_sql (string $entity_name) : string {
        $column_defs = $this->get_columns_def_from_attributes($entity_name);
        $primary_key = '';

        foreach(array_keys($column_defs['def']) as $name) {
            $is_primary_key = $this->is_primary_key($entity_name, $name);
            if ($is_primary_key) {
                $primary_key = $column_defs['def'][$name];
                unset($column_defs['def'][$name]);
            } 
        }
        return 'CREATE TABLE IF NOT EXISTS `'.
                $this->dbname.'`.`'.$this->get_table_name($entity_name).
                '` ('.join(', ', array_values(array_merge([$primary_key], $column_defs['def']))).')';
    }

    /**
     * Inserts instance of entity class
     * into table on database
     *
     * @param BaseEntity $entity
     * @param array $columns
     * @return void
     */
    protected function insert_object_into_table (BaseEntity $entity, array $columns) : void {
        if (!$this->table_exists($entity::class)){
            $this->sql->exec($this->get_create_table_sql($entity::class));
        }

        if ($this->table_exists($entity::class)){
            $tbname = $this->get_table_name($entity::class);
            $column_defs = $this->get_columns_def_from_attributes($entity::class);

            if (!$this->is_match_columns($tbname, $column_defs['type'])) {
                $not_exists_columns_in_table = $this->get_not_existing_columns_in_table($entity::class);
                $not_exists_columns_in_entity = $this->get_not_existing_columns_in_entity($entity::class);

                if (!empty($not_exists_columns_in_entity)) {
                    // echo '<br>Column removed from table<br>';
                    foreach($not_exists_columns_in_entity as $col) {
                        if (!$col['primary']) {
                            $sql = 'ALTER TABLE `'.$tbname.'` DROP COLUMN '.'`'.$col['name'].'`';
                            $this->sql->exec($sql);
                        } else {
                            echo ("<br>Primary key '".$col['name']."' can't be deleted.<br>");
                        }
                    }
                } 

                if (!empty($not_exists_columns_in_table)) {
                    // echo '<br>Column add to table<br>';
                    foreach($not_exists_columns_in_table as $col) {
                        if ($this->is_primary_key($entity::class, $col['name'])) {
                            $sql = 'ALTER TABLE `'.$tbname.'` ADD COLUMN '.'`'.$col['name'].'` '. $column_defs['type'][$col['name']].' FIRST';
                            $this->sql->exec($sql);
                        } else {
                            $sql = 'ALTER TABLE `'.$tbname.'` ADD COLUMN '.'`'.$col['name'].'` '. $column_defs['type'][$col['name']];
                            $this->sql->exec($sql);
                        }
                    }
                }

                if (empty($not_exists_columns_in_entity) &&
                    empty($not_exists_columns_in_table)) {
                    // echo '<br>Column type changed<br>';
                    foreach($columns as $col) {
                        if (!$this->is_primary_key($entity::class, $col['name'])) {
                            $new_col = $this->get_type_from_attr($column_defs['type'][$col['name']]);
                            $old_col = $this->get_type_from_attr($this->get_column_def_in_table($tbname, $col['name']));
                            
                            $new_value = $this->get_value_from_attr($column_defs['type'][$col['name']]);
                            $old_value = $this->get_value_from_attr($this->get_column_def_in_table($tbname, $col['name']));


                            if ($new_col === $old_col) {
                                if ($new_col === 'ENUM') {
                                    $new_value = str_replace(' ', '', str_replace('"', "'", strtoupper($new_value)));
                                    $old_value = str_replace(' ', '', str_replace('"', "'", strtoupper($old_value)));
                                }

                                if ($new_value !== $old_value) {               
                                    $sql = 'UPDATE `'.$tbname.'` SET `'.$col['name'].'` = SUBSTRING(`'.$col['name'].'`,1,'.$new_value.')';
                                    $this->sql->exec($sql);
                                }
                            }

                            $sql = 'ALTER TABLE `'.$this->get_table_name($entity::class).'` MODIFY COLUMN '.'`'.$col['name'].'` '. $column_defs['type'][$col['name']];       
                            $this->sql->exec($sql);
                        }
                    }
                }
            }

            if ($this->is_match_columns($tbname, $column_defs['type'])) {
                // echo '<br>object insert to table<br>';
                $sql = 'INSERT INTO `'.$tbname.'` ( ';
                $cols = [];
                $vals = [];
                foreach(array_keys($column_defs['type']) as $col_name) {
                    $val = gettype($entity->{$col_name} ?? null) === 'string'
                            ? '"'.$entity->{$col_name}.'"'
                            : $entity->{$col_name} ?? null;
               
                    AttributeValidator::validate_value_depend_on_attribute($entity::class, $col_name, $val);
                    
                    array_push($cols,  $col_name);

                    if (gettype($val) === 'boolean') {
                        $val = $val ? 1 : 0;
                    }

                    array_push($vals, $val ?? 'NULL');
                }
                $sql .= join(', ', $cols).') VALUES ('.join(', ', $vals).(empty($vals)).')';
                $this->sql->exec($sql);
            }
        }
    }

    /**
     * Returns column name of defined
     * primary key of given table name
     * from a existing table in database
     *
     * @param string $entity_name
     * @param array $columns
     * @return string
     */
    protected function get_primary_key (string $table_name) : string {
        $res = $this->sql->exec('DESCRIBE `'.$table_name.'`');
        if (!empty($res) && count($res) > 0) {
            foreach($res as $row) {
                if ($row['Key'] === 'PRI')
                    return $row['Field'];
            }
        }
        return '';
    }

    /**
     * Returns the column definition 
     * of given column name from hoven
     * table name
     *
     * @param string $table_name
     * @param string $column_name
     * @return string
     */
    protected function get_column_def_in_table (string $table_name, string $column_name) : string {
        $res = $this->sql->exec('DESCRIBE `'.$table_name.'`');
        if (!empty($res) && count($res) > 0) {
            foreach($res as $row) {
                if ($row['Field'] === $column_name)
                    return strtoupper($row['Type']);
            }
        }
        return '';
    }

    /**
     * Extracts the data type name from
     * given name of defined attribute
     *
     * @param string $attr
     * @return string
     */
    protected function get_type_from_attr(string $attr) : string {
        if (!empty($attr))
            return explode('(', $attr)[0];
        return '';
    }

    /**
     * Extracts the data type value from
     * given name of defined attribute
     *
     * @param string $attr
     * @return string
     */
    protected function get_value_from_attr(string $attr) : string {
        if (!empty($attr))
            return explode(')', explode('(', $attr)[1] ?? '')[0];
        return '';
    }

    /**
     * Checks the existing
     * #[AUTO_INCREMENT] attribute from
     * exitsting property on your 
     * entity class
     *
     * @param string $entity_name
     * @param string $column_name
     * @return boolean
     */
    private function is_auto_increment (string $entity_name, string $column_name) : bool {
        return $this->attibute_exists($entity_name, $column_name, AUTO_INCREMENT::class);
    }

    /**
     * Checks if given column is a primary_key
     * from set #[PRIMARY_KEY] attribiute on
     * BaseEntity class
     *
     * @param string $entity_name
     * @param string $column_name
     * @return boolean
     */
    private function is_primary_key (string $entity_name, string $column_name) : bool {
        return $this->attibute_exists($entity_name, $column_name, PRIMARY_KEY::class);
    }

    /**
     * Checks nullable possiblity 
     * on given column name from
     * #[NOT_NULL] attribute on
     * exitsting property on your 
     * entity class
     *
     * @param string $entity_name
     * @param string $column_name
     * @return boolean
     */
    private function is_not_null (string $entity_name, string $column_name) : bool {
        return $this->attibute_exists($entity_name, $column_name, NOT_NULL::class);
    }


    /**
     * Checks availability of given attribute
     * in definition of given entity class and
     * defined property
     *
     * @param string $entity_name
     * @param string $column_name
     * @param string $attribute_name
     * @return boolean
     */
    private function attibute_exists (string $entity_name, string $column_name, string $attribute_name) : bool {
        $reflection = new ReflectionProperty($entity_name, $column_name);
        foreach($reflection->getAttributes() as $attr) {
            if($attr->getName() === $attribute_name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Matchs the type of
     * given attributes
     *
     * @param string $attr_1
     * @param string $attr_2
     * @return boolean
     */
    private function is_match_type (string $attr_1, string $attr_2) : bool {
        $attr_1 = strtolower(trim($attr_1));
        $attr_1 = explode('(', $attr_1)[0];
        $attr_2 = strtolower(trim($attr_2));
        $attr_2 = explode('(', $attr_2)[0];
        return ($attr_1 == $attr_2);
    }

    /**
     * Matches the value of
     * given attributes
     *
     * @param string $attr_1
     * @param string $attr_2
     * @return boolean
     */
    private function is_match_value (string $attr_1, string $attr_2) : bool {
        if (empty($attr_1) && empty($attr_2)) 
            return true;
        if (!empty($attr_1) && !empty($attr_2)) {
            $attr_1 = strtolower(trim($attr_1));
            $attr_2 = strtolower(trim($attr_2));
            $val_1 = trim(explode(')', explode('(', $attr_1)[1] ?? '')[0]);
            $val_2 = trim(explode(')', explode('(', $attr_2)[1] ?? '')[0]);
            $val_1 = str_replace(' ', '', str_replace('"', "'", strtoupper($val_1)));
            $val_2 = str_replace(' ', '', str_replace('"', "'", strtoupper($val_2)));
            return $val_1 == $val_2;
        }
        return false;
    }

    /**
     * Matchs the columns definitions
     * on defined entity class and existing
     * table in database
     *
     * @param string $table_name
     * @param array $columns
     * @return boolean
     */
    private function is_match_columns (string $table_name, array $columns) : bool {
        $tb_name = preg_split('/[eE]ntity/', $table_name)[0];
        $result = $this->sql->exec('DESCRIBE `'.$tb_name.'`');
        
        if (count($result) > 0) {
            $names = array_keys($columns);

            if (count($columns) !== count($result))
                return false;

            $match_counter = 0;
            foreach($result as $row) {
                $name = $row['Field'];
                $type = $row['Type'];
             
                if (in_array($name, $names)) {
                    if ($this->is_match_type($type, $columns[$name]) && 
                        $this->is_match_value($type, $columns[$name])) {        
                        $match_counter++;
                    }       
                } 
            }
            return count($columns) === $match_counter;
        }
        return false;
    }

    /**
     * Returns columns, that not exists
     * in entity class and they 
     * exists in table in database
     *
     * @param string $table_name
     * @param array $columns
     * @return array
     */
    private function get_not_existing_columns_in_entity (string $table_name) : array {
        $tb_name = strtolower(preg_split('/[eE]ntity/', $table_name)[0]);
        $tb_name = $this->get_table_name($table_name);
        $result = $this->sql->exec('DESCRIBE `'.$tb_name.'`');
        if (count($result) > 0) {
            $column_defs = $this->get_columns_def_from_attributes($table_name)['type'];
            $col_names = array_keys($column_defs);
            $result_diff_columns = array_filter(array_map(function($item) use ($col_names) {
                if (!in_array($item['Field'], $col_names))
                    return [
                        "name" => $item['Field'],
                        "type" => $item['Type'],
                        "primary" => ($item['Key'] === 'PRI'),
                    ];
            }, $result), fn($item) => !empty($item));
            return array_values(array_map(fn($item) => ['name' => $item['name'], 'type' => null, 'primary' => $item['primary']], $result_diff_columns));
        }
        return [];
    }

    /**
     * Returns columns, that not exists
     * in table in database and they 
     * exists in entity class
     *
     * @param string $table_name
     * @param array $columns
     * @return array
     */
    private function get_not_existing_columns_in_table (string $table_name) : array {
        $tb_name = strtolower(preg_split('/[eE]ntity/', $table_name)[0]);
        $tb_name = $this->get_table_name($table_name);
        $result = $this->sql->exec('DESCRIBE `'.$tb_name.'`');
        if (count($result) > 0) {
            $column_defs = $this->get_columns_def_from_attributes($table_name)['type'];
            $col_names = array_keys($column_defs);
            $res_col_names = array_map(fn($item) => $item['Field'], $result);
            $array_diff = array_values(array_diff($col_names, $res_col_names));

            return array_values(
                        array_map(
                            function($item) use ($column_defs) {
                                return ['name' => $item, 'type' => strtoupper($column_defs[$item])];
                            },
                            $array_diff
                        )
                    );
        }
        return [];
    }

    /**
     * Returns the table name from class name
     * (ignores "entity" word from name) or
     * takes the given name on #[TABLE("mytablename")]
     *
     * @param string $entity_name
     * @return string
     */
    protected function get_table_name (string $entity_name) : string {
        $reflection = new ReflectionClass($entity_name);
        foreach($reflection->getAttributes() as $attr) {
            $attr_value = $attr->getArguments();
            if (!empty($attr_value) && count($attr_value) === 1) {
                foreach($attr->getArguments() as $arg) {
                    return $arg;
                }
            } else {
                return preg_split('/[eE]ntity/', $entity_name)[0];
            }
        }
        return '';
    }

    /**
     * Return true|false and checks
     * if defined entity class exists
     * as table on your configurated
     * schema|database
     *
     * @param string $table_name
     * @return bool
     */
    protected function table_exists (string $table_name) : bool {
        $result = $this->sql->exec('SHOW TABLES');
        if (!empty($result)) {
            $result = array_map(fn($item) => array_values($item), $result);
            $result = array_map(fn($item) => $item[0], $result);
        }
        foreach ($result as $tb_name)
            if ($tb_name === $this->get_table_name($table_name))
                return true;
        return false;
    }

    /**
     * Returns fields name, type, value
     * of given entity class as an array
     *
     * @param BaseEntity $entity_object
     * @return array
     */
    protected function get_columns (BaseEntity $entity_object) : array {
        $entity_properties = array_keys(get_class_vars($entity_object::class));
        $column = [];

        if (!empty($entity_properties))
            foreach ($entity_properties as $property)
                if (property_exists($entity_object, $property)) {
                    if (($this->is_not_null($entity_object::class, $property)) &&
                        (is_null($entity_object->{$property} ?? null))) {
                        
                        if (!$this->is_primary_key($entity_object::class, $property)) {
                            echo('Property "'.$property.'" used #[NOT_NULL] attribute, but it have null value, that is not allowed check it in');
                        }
                    }
                    array_push(
                        $column,
                        [
                            'name' => $property,
                            'type' => gettype($entity_object->{$property} ?? null),
                            'value' => $entity_object->{$property} ?? null
                        ]
                    );
                }
        return $column;
    }
}