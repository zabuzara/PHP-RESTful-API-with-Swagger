<?php
include_once 'Scan.php';
include_once 'Attributes.php';

/**
 * @author Omid Malekzadeh Eshtajarani <zabuzara@yahoo.com>
 * Simple RESTful class for control the 
 * METHODS: GET, POST, PUT, DELETE, OPTIONS
 * and handle with Controller classes and call automatically
 * the endpoint functions in Controller classes
 */
final class RESTful {
    private $controllers = [];
    private $classes = [];

    public function __construct() {
        $search_result = Scan::directory('.')->for('.htaccess', true, true, true);
        if (count($search_result) > 0) {
            $htaccess_file = $search_result[0];
            $rules = [
                'RewriteEngine On',
                'RewriteCond %{REQUEST_METHOD} (POST|GET|OPTIONS|PUT|DELETE)',
                'RewriteRule .* index.php',
            ];

            if (!empty(file_get_contents($htaccess_file['path']))) {
                $lines = explode("\n", file_get_contents($htaccess_file['path']));
            
                foreach($rules as $rule) {
                    if (!in_array($rule, $lines)) {
                        die('the .htaccess file must include followed rules: <br><br>'.join("<br>", $rules));                    
                    }
                }
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        $request_parts =  explode('/', explode('/PHP-API-Template/', $_SERVER['REQUEST_URI'])[1]);
        
        foreach (Scan::directory('.')->for('', true, true, false) as $file) {
            if ($file['name'] !== 'RESTful.php') {
                if (str_contains(file_get_contents($file['path']), '#[Controller]')) {
                    include_once $file['path'];
          
                    if (class_exists(explode('.php', $file['name'])[0])) { 
                
                        $class_name = explode('.php', $file['name'])[0];
                        $reflection = new ReflectionClass($class_name);
                
                        foreach ($reflection->getAttributes() as $attribute) {
                            if ($attribute->getName() === Controller::class) {
                                $this->controllers[$class_name]['request'] = '';
                                $this->controllers[$class_name]['class_path'] = $file['path'];
                                $this->classes[$class_name] = [];
                            } 
                
                            if ($attribute->getName() !== Controller::class) {
                                if (!array_key_exists('request', $this->controllers[$class_name]))
                                    break;
                
                                $this->controllers[$class_name]['request'] = $attribute->getArguments()[0];
                            }
                        }
                
                        if (array_key_exists($class_name, $this->controllers)) {
                            $this->controllers[$class_name]['mapping'] = [];
                            foreach ($reflection->getMethods() as $method) {
                                $this->classes[$class_name][$method->getName()] = [];
                
                                $args = [];
                                foreach ($method->getAttributes() as $attribute) {
                                    if (!key_exists($attribute->getName(), $this->controllers[$class_name]['mapping'])) {
                                        $method_name = strtoupper(explode('Mapping', $attribute->getName())[0]);
                                        
                                        if (!key_exists($method_name, $this->controllers[$class_name]['mapping']))
                                            $this->controllers[$class_name]['mapping'][$method_name] = [];
                                    }
                                    $this->controllers[$class_name]['mapping'][$method_name][$method->getName()]['/'] = explode('/'.$method->getName().'/', $attribute->getArguments()[0])[1];
                                }
                            }
                        }
                    }
                }
            }
        }
        
        foreach($this->controllers as $class_name => $controller) {
            $this->controllers[$controller['request']] = $controller;
            $this->controllers[$controller['request']]['class_name'] = $class_name;
            unset($this->controllers[$controller['request']]['request']);
            unset($this->controllers[$class_name]);
        }
        
        if (count($request_parts) > 3 || 
            count($request_parts) === 1 || 
            !array_key_exists(strtolower($request_parts[0]), $this->controllers))
            $this->forbidden(__LINE__);
        
        if (empty($this->controllers[$request_parts[0]]['mapping'][$_SERVER['REQUEST_METHOD']]))
            $this->forbidden(__LINE__);
        
        $controller = $this->controllers[$request_parts[0]];
        $controller_endpoints = $controller['mapping'][$_SERVER['REQUEST_METHOD']];
        
        if (empty($controller_endpoints[$request_parts[1]]))
            $this->forbidden(__LINE__);
        
        if (!empty($controller_endpoints[$request_parts[1]]['/']) && 
            (count($request_parts) < 3 || empty($request_parts[2])))
            $this->forbidden(__LINE__);
        
        if (empty($controller_endpoints[$request_parts[1]]['/']) &&
            count($request_parts) > 2)
            $this->forbidden(__LINE__);

        $class = $this->controllers[$request_parts[0]]['class_name'];
        $method = $request_parts[1];
        $argument = $request_parts[2];
        $post = json_decode(file_get_contents("php://input"), true);
        
        $class = new $class();
        
        if (!empty($argument))
            $class->{$method}($argument, $post);
        else
            $class->{$method}($post);
    }

    /**
     * Returns error message if endpoint not exists
     *
     * @param [type] $line
     * @return void
     */
    private function forbidden ($line) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 not found');
        echo json_encode(['error' => ['code' => 404, 'message' => 'not found']]);
        exit;
    }

    /**
     * Prints Controller classes
     *
     * @return void
     */
    public function show_controllers_structure () {
        print_r($this->controllers);
    }
}