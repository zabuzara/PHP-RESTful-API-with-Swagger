<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Context class for database configuration
 */
class Context {
    private string $db_name;
    private string $db_user;
    private string $db_pass;
    private string $db_driver;
    private string $db_host;
    private int|string $db_port;

    /**
     * Allows to read properties
     *
     * @param [type] $name
     * @return void
     */
    public function __get ($name) {
        return $this->{$name};
    }

    /**
     * Initializes a new instance
     * after checking validity of
     * file on given file path
     *
     * @param string $context_path
     */
    public function __construct(string $context_path) {
        if (Context::is_valid($context_path)) {
            $context_object = json_decode(file_get_contents($context_path))->context[0];
            foreach ($context_object as $property => $value) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Check the validity of context
     * configuration json file of
     * given file path
     *
     * @param string $context_path
     * @return boolean
     */
    static public function is_valid(string $context_path) {
        if (file_exists($context_path)) {
            $context_object = json_decode(file_get_contents($context_path));
            if (property_exists($context_object, 'context'))
                foreach(array_keys(get_class_vars(__CLASS__)) as $property)
                    if (!property_exists($context_object->context[0], $property))
                        return false;
        }
        return true;
    }

    /**
     * Loads automaticly the existing context
     * configuration json file and trys to load
     * it and use the configuration properties
     * for creating a new Context instance
     *
     * @return Context|null
     */
    static public function auto_load() {
        foreach(Scan::directory('.')->for('.json', true) as $json_file) {
            if (Context::is_valid($json_file['path'])) {
                return new Context($json_file['path']);
            }
        }
        echo('ContextError: a valid context configuration json file not found');
        return null;
    }
}