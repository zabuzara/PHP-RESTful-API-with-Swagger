<?php
/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * 
 * Auto folder structure loader
 * and import found files in to this index.php
 * file and run static method configf() on
 * Configurator class. If method returns true
 * it will creat enew instance of Configurator
 * and call run method for creating examples
 * and folders automatically.
 */
(function () : void {
    if (!file_exists('framework.config.json')) {
        include_once("entity-framework/utils/Configurator.php");
        if (!Configurator::configed()) {
            $configurator = new Configurator();
            $configurator->run();
        }

        if (!file_exists('framework.config.json')) {
            throw new Exception('json config file "framework.config.json" not found. Configuration is not possible.');
        }
    }
    
    if (file_exists('framework.config.json')) {
        $context_object = json_decode(file_get_contents('framework.config.json'));

        if (property_exists($context_object, 'folder_structure')) {
            $import_structure = (array) $context_object->folder_structure;
            foreach($import_structure as $dir => $files) {
                foreach($files as $file) {
                    include_once("entity-framework/".$dir."/".$file.".php");

                    if ($file === 'Configurator') {
                        if (!Configurator::configed()) {
                            $configurator = new Configurator();
                            $configurator->run();
                        }
                    }
                }

                if (empty($files)) {
                    foreach (glob($dir."/*.php") as $filename) {
                        include_once($filename);
                    }
                }
            }
        }
    } 
})();
