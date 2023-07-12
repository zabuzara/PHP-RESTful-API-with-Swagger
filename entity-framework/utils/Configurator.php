<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Configurator class for creating entities, repositories folders
 * in create an ExampleEntity and ExampleRepository classes
 */
class Configurator {
    /**
     * Checks if is configurator already configed
     *
     * @return boolean
     */
    static public function configed () : bool {
        return  file_exists('framework.config.json') &&
                is_dir('entities') &&
                file_exists('entities/ExampleEntity.php') &&
                is_dir('repositories') &&
                file_exists('repositories/ExampleRepository.php');
    }

    /**
     * Runs configurator
     *
     * @return void
     */
    public function run () {
        if (!file_exists('framework.config.json')) {
            touch('framework.config.json') or die('Permission denied!');
        }

        if (!is_dir('entities')) {
            mkdir('entities') or die('Permission denied!');
            touch('entities/ExampleEntity.php') or die('Permission denied!');
        } else {
            if (!file_exists('entities/ExampleEntity.php')) {
                touch('entities/ExampleEntity.php') or die('Permission denied!');
            }
        }

        if (!is_dir('repositories')) {
            mkdir('repositories') or die('Permission denied!');
            touch('repositories/ExampleRepository.php') or die('Permission denied!');
        } else {
            if (!file_exists('repositories/ExampleRepository.php')) {
                touch('repositories/ExampleRepository.php') or die('Permission denied!');
            }
        }

        if (file_exists('framework.config.json')) {
            $this->create_context_config_json_skeleton();
        }

        if (file_exists('entities/ExampleEntity.php')) {
            $this->create_entity_class_skeleton();
        }

        if (file_exists('repositories/ExampleRepository.php')) {
            $this->create_repository_class_skeleton();
        }
    }

    /**
     * Create framework.context.json
     * database connection config file
     *
     * @return void
     */
    private function create_context_config_json_skeleton () {
        $content = '{ "context": [ { "db_driver": "mysql", "db_name": "TEST_EM_V01", "db_user": "root", "db_pass": "root", "db_host": "127.0.0.1", "db_port": "3306" } ], "folder_structure": { "security": [ "Hash" ], "types": [ "Datetime", "Generic", "Numeric", "String" ], "utils": [ "Configurator", "Validator" ], "database": [ "Context", "SQL" ], "exceptions": [ "SQLException", "EntityException", "EntityScannerException", "EntityManagerException" ], "core": [ "BaseEntity", "IRepository", "EntityManager", "BaseRepository", "EntityScanner" ], "entities": [], "repositories": [] } }';
        file_put_contents('framework.config.json', $content);
    }

    /**
     * Creates ExampleEntity class skeleton
     *
     * @return void
     */
    private function create_entity_class_skeleton () {
        $content = [
            '<?php',
            '/**',
            ' * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>',
            ' * ',
            ' * A example for learning how you can write your own entity class',
            ' * and set column types.',
            ' */',
            '#[TABLE("example")]',
            'class ExampleEntity extends BaseEntity {',
            '',
            '    #[NOT_NULL]',
            '    #[Types\String\T_VARCHAR(64)]',
            '    public string $example_title;',
            '',
            '    #[Types\String\T_VARCHAR(255)]',
            '    public string|null $example_description;',
            '',
            '    #[Types\Datetime\T_TIMESTAMP]',
            '    public string|null $example_datetime;',
            '',
            '    #[Types\Numeric\T_INT(11)]',
            '    public int|null $example_archive_number;',
            '}',
        ];

        ftruncate(fopen("entities/ExampleEntity.php", "r+"), 0);
        foreach($content as $line) {
            file_put_contents('entities/ExampleEntity.php', $line."\n", FILE_APPEND);
        }
    }

    /**
     * Creates ExampleRepository class skeleton
     *
     * @return void
     */
    private function create_repository_class_skeleton () {
        $content = [
            '<?php',
            '/**',
            ' * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>',
            ' * ',
            ' * A example for learning how you can write your own repository class',
            ' * and extends methods/function as need as well.',
            ' */',
            '#[REPOSITORY]',
            'class ExampleRepository extends BaseRepository {',
            '',
            '    public function find_example_by_title(string $entity_name, string $title) : BaseEntity | null {',
            '',
            '        // your database connection logic here',
            '',
            '        if ($this->table_exists($entity_name)) {',
            '            $table_name = $this->get_table_name($entity_name);',
            '            $primary_key_name = $this->get_primary_key($table_name);',
            '',
            '            if (!empty($primary_key_name)) {',
            '                $result = $this->get_context()->exec("SELECT * FROM `".$table_name."` WHERE `example_title` LIKE ?", ["%".$title."%"]);',
            '',
            '                if (!empty($result) && count($result) > 0) {',
            '                    if (class_exists($entity_name)) {',
            '                        return EntityManager::array_to_entity($entity_name, $result[0]);',
            '                    } else {',
            '                        return $result[0];',
            '                    }',
            '               }',
            '            }',
            '        }',
            '        return null;',
            '    }',
            '',
            '    // -- another methods here --',
            '',
            '}',
        ];

        ftruncate(fopen("repositories/ExampleRepository.php", "r+"), 0);
        foreach($content as $line) {
            file_put_contents('repositories/ExampleRepository.php', $line."\n", FILE_APPEND);
        }
    }
}