<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * EntityScanner
 * 
 * simple directory scanner
 * for finding self defined
 * entity classes
 */
abstract class EntityScanner {
    /**
     * Checks if the given path and
     * class name are a correct and
     * possible entity
     *
     * @param string $class_path
     * @param string $class_name
     * @return boolean
     */
    static function is_entity (string $class_path, string $class_name) : bool {
        if (file_exists($class_path)) {
            if (preg_match('/\n#\[TABLE\(".+"\)\]\nclass/', file_get_contents($class_path))) {
                include_once $class_path;
    
                if (class_exists($class_name)) {
                    $reflection = new ReflectionClass($class_name);
                    $is_table_attr = array_filter($reflection->getAttributes(), function($attr) {
                        return str_contains($attr->getName(), 'TABLE');
                    });
    
                    if (!empty($is_table_attr) && count($is_table_attr) > 0) {
                        if ($is_table_attr[0]) {
                            if (is_subclass_of($class_name, BaseEntity::class)) {
                                return true;
                            } else {
                                throw new EntityScannerException('The entity class should extends the BaseEntity class.');
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * Scans root directory of
     * your project recursive
     * and trys to find defined
     * entities
     *
     * @param string $directory
     * @return array
     */
    static function scan_entities_from_directories (string $directory) : array {
        $entite_names = [];
        $security_counter = 0;
        if ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                if($security_counter++ > 1000)
                    break;
                if ($entry != "." && $entry != "..") {
                    if (is_file($directory.'/'.$entry)) {
                        if (str_ends_with($entry, '.php')) {
                            if (file_exists($directory.'/'.$entry)) {
                                if ($file = fopen($directory.'/'.$entry, "r")) {
                                    $found_possible_classes = [];
                                    while (($line = fgets($file)) !== false) {
                                        if (preg_match("/^class /", $line)) {
                                            $class_name = preg_split("/^class /", $line);
                                            $class_name = explode(" ", trim($class_name[1]))[0];
                                            if (preg_match("/[a-zA-Z]+/",  $class_name)) {
                                                array_push($found_possible_classes, ['path' => trim($directory.'/'.$entry), 'name' => $class_name]);
                                            }
                                        }
                                    }

                                    fclose($file);
                                    foreach($found_possible_classes as $possible_class) {       
                                        if (EntityScanner::is_entity($possible_class['path'], $possible_class['name'])) {
                                            array_push($entite_names, ['class_name' => $possible_class['name'], 'class_path' => $possible_class['path']]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (is_dir($directory.'/'.$entry)) {
                        if (!str_starts_with($entry, '.'))
                            $entite_names = array_merge($entite_names, EntityScanner::scan_entities_from_directories($directory.'/'.$entry));
                    }
                }
            }
            closedir($handle);
        }
        return $entite_names;
    }
}