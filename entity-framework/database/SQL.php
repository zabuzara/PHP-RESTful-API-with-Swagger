<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * SQL class to handle sql command
 * object oriented, very easy to use
 * with method chaining and exception
 * handling, that can be happend form
 * unordered sql keywords in sql command
 */
final class SQL {
    private static bool $DEBUG = false;
    const JSON = "JSON";
    const ASSOC_ARRAY = "ASSOC_ARRAY";
    static private $connection = null;
    private $output_type = self::ASSOC_ARRAY;
    private string $db_name;


    private function __construct($db_name) {
        $this->db_name = $db_name;
    }

    /**
     * Sets context and cretes instance of
     * PDO from configuration injson file
     * "framework.config.json".
     *
     * @param Context|null $context
     * @return SQL|null
     */
    static public function set_context(Context $context = null) : SQL|null {
        if(!is_null($context)) {
            $dns = $context->db_driver.':host='.$context->db_host.';port='.$context->db_port;
            try {
                SQL::$connection = new \PDO($dns, $context->db_user, $context->db_pass);
                $stm = SQL::$connection->prepare("CREATE DATABASE IF NOT EXISTS `".$context->db_name."`; USE `".$context->db_name."` ;");
                $stm->execute();
            } catch (PDOException $e) {
                SQL::$connection = null;
                echo('Connection not possible. Check the context configuration in "framework.config.json" and try again.');
            }
            return new SQL($context->db_name);
        }
        return null;
    }

    /**
     * Displayes SQL command history on browser
     *
     * @param string $title
     * @param string $content
     * @return void
     */
    private function print(string $title, string $content) {
        $content = str_replace('"', '<span style="color:gray;">"</span>', $content);        
        $content = str_replace(' : ', '<span style="color:darkred">: </span>', $content);
        $content = str_replace('*', '<span style="color:orangered">*</span>', $content);
        $content = str_replace('`', '<span style="color:lightblue">`</span>', $content);
        $content = str_replace('; ', '<span style="color:red">;</span>', $content);
        $content = str_replace('{', '<span style="color:lightgreen">{</span>', $content);
        $content = str_replace('}', '<span style="color:lightgreen">}</span>', $content);
        $content = str_replace(' = ', '<span style="color:violet"> = </span>', $content);
        print_r(
            '<span style="'.
            'background-color: #555;'.
            'padding: 5px;'.
            'color:white;'.
            'margin:5px;'.
            'font-family: monospace;'.
            'border-radius: 0.3rem 0.3rem 0 0;'.
            '">'.
            $title.
            '</span>'.
            '<br>'.
            '<div style="'.
            'border-radius: 0 0.3rem 0.3rem 0.3rem;'.
            'font-family: monospace;'.
            'background-color:black;'.
            'color:white;'.
            'margin:5px;'.
            'padding:5px;'.
            '">'.
            $content.
            '</div>'.
            '<br><br>'
        );
    }


    /**
     * Undocumented function
     *
     * @param [type] $sql
     * @param array|null $params
     * @return array
     */
    public function exec(string $sql, array $params = []) : array {
        if (SQL::$connection !== null) {
            if (SQL::$DEBUG) {
                $command = $sql;
                foreach($params as $value) {
                    $before = substr($command, 0, strpos($command, '?'));
                    $after = substr($command, strpos($command, '?') + 1, strlen($command));
                    $command = $before.(is_numeric($value) ? $value : '"'.$value.'"').$after;
                }
                $this->print('SQL Command', trim($command).(!empty(trim($command)) ? '; ' : ''));
            }

            if (!empty($sql)) {
                $rows = [];
                $statement = SQL::$connection->prepare($sql);
                $statement->execute($params);
                $content = $statement->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($content) && count($content) > 0) {
                    foreach($content as $row) {
                        array_push($rows, $row);
                    }
                    return $rows;
                }
            }
        }
        return [];
    }

    function __destruct() {

    }

    /**
     * Turns the debug mode on
     *
     * @return void
     */
    static function debug() {
        echo '<h1 style="padding:5px;font-family:monospace;color:#333;">PHP Entity-Framework</h1>';
        SQL::$DEBUG = true;
    }
}