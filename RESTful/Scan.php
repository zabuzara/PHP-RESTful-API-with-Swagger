<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Scan class for scanning directories and files
 */
final class Scan {
    const RECURSION_COUNT = 500;
    
    private function __construct(private string $path) {}

    /**
     * Returns new intsnce of Scan class and gives
     * the given parameter to the constructor
     *
     * @param string $path
     * @return Scan
     */
    static public function directory(string $path) : Scan {
        return new Scan($path);
    }

    /**
     * Searchs the folder for given $name 
     *
     * @param string $name
     * @param boolean $recursive_search
     * @param boolean $ignore_case
     * @param boolean $show_hiddens
     * @return array
     */
    public function for(
        string $name, 
        bool $recursive_search = false,
        bool $ignore_case = false, 
        bool $show_hiddens = false
    ) : array {
        $entry_names = [];
        $security_counter = 0;
        $file_name = $name ?? '.'.$name;
        if ($handle = opendir($this->path)) {
            while (false !== ($entry = readdir($handle))) {
                if($security_counter++ > Scan::RECURSION_COUNT)
                    break;

              
    
                if ($entry != "." && $entry != ".." && 
                    $entry !== Scan::class.'.php' &&
                    $entry != 'swagger.config.php') {
                    
                    if ( $entry == 'swagger.config.php') {
                        echo json_encode($this->path.'/'.$entry);
                    }
                    
                    if (is_file($this->path.'/'.$entry)) {
                        if (($ignore_case && str_contains(strtolower($entry), strtolower($file_name))) || 
                            (!$ignore_case && str_contains($entry, $file_name))) {

                            if ((!$show_hiddens && !str_starts_with($entry, '.')) ||
                                ($show_hiddens))
                                array_push($entry_names, [
                                    'name' => $entry, 
                                    'path' => $this->path.'/'.$entry
                                ]);
                        }
                    }
                    if ($recursive_search && is_dir($this->path.'/'.$entry)) {
                        if (!str_starts_with($entry, '.'))
                            $entry_names = array_merge(
                                $entry_names, 
                                Scan::directory($this->path.'/'.$entry)
                                ->for($file_name, $ignore_case, $recursive_search)
                            );
                    }
                }
            }
        }
        return $entry_names;
    }
}