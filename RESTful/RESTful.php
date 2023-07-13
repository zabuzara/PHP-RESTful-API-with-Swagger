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
    static public $session_key_for_token = null;
    static public $session_key_for_expiration = null;
    private $controllers = [];
    private $classes = [];
    private $endpoint = '';
    private mixed $parameters = [];
    static private array $includes = [];

    const HTACCESS_RULES = [
        'RewriteEngine On',
        'SetEnvIf HOST "^localhost" local_url',
        'Order Deny,Allow',
        'RewriteCond %{REQUEST_METHOD} (POST|GET|OPTIONS|PUT|DELETE)',
        'RewriteRule .* index.php',
        'RewriteCond %{HTTP:Authorization} .',
        'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]',
        'Deny from all',
        'Allow from env=local_url',
        'Satisfy any',
        'Options All -Indexes',
        'IndexIgnore *',
        'IndexIgnore *.png *.zip *.jpg *.gif *.doc *.xml *.json *.md *.txt *.ttf *.php *.ico *js *.scss',
        '<FilesMatch "\.(ini|psd|log|sh|xml|txt|md)$">',
        '    Order allow,deny',
        '    Deny from all',
        '</FilesMatch>'
    ];

    public function __construct(string $document_root, array $ignore_routes = []) {
        $search_result = Scan::directory('.')->for('.htaccess', true, true, true);
        if (count($search_result) > 0) {
            $htaccess_file = $search_result[0];
            if (!empty(file_get_contents($htaccess_file['path']))) {
                $lines = explode("\n", file_get_contents($htaccess_file['path']));
            
                foreach(self::HTACCESS_RULES as $rule) {
                    if (!in_array($rule, $lines)) {
                        die('the .htaccess file must include followed rules: <br><br>'.join("<br>", self::HTACCESS_RULES));                    
                    }
                }
            } else {
                foreach(self::HTACCESS_RULES as $rule) {
                    $file = fopen('.htaccess', 'a+');
                    fclose($file);
                    file_put_contents('.htaccess', $rule."\n", FILE_APPEND);
                }
            }
        }
        if (!file_exists(".htaccess"))
            if (fopen(".htaccess", "w")) {
                foreach(self::HTACCESS_RULES as $rule) {
                    file_put_contents('.htaccess', $rule."\n", FILE_APPEND);
                }
            } else {
                echo('Permission denied (.htaccess creation failed!)');
            }
        if (file_exists(".htaccess"))
            chown('.htaccess', 'root');
       
        $request_parts =  explode('/', explode($document_root, $_SERVER['REQUEST_URI'])[1]);

        header("Access-Control-Allow-Origin: http://localhost:8080");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if (($this->is_with_authorization() && $this->is_valid_token() && !in_array($request_parts[0], $ignore_routes)) ||
            (in_array($request_parts[0], $ignore_routes))) {

            if (count($request_parts) > 0) {
                if (count(explode('?', $request_parts[1])) > 1) {
                    $this->endpoint = explode('?', $request_parts[1])[0];
                    $get_parameters = explode('?', $request_parts[1])[1];
                    $get_parameters = explode('&', $get_parameters);
                
                    foreach ($get_parameters as $param) {
                        $name = explode('=', $param)[0];
                        $value = explode('=', $param)[1];
                        $this->parameters[$name] = $value;
                    }

                    if (count($this->parameters) === 1)
                        $this->parameters = array_values($this->parameters)[0];
                } else {
                    $this->endpoint = $request_parts[1];
                }
            }

            foreach (Scan::directory('.')->for('', true, true, false) as $file) {
                 
                if ($file['name'] !== 'RESTful.php' && 
                    str_contains($file['name'], '.php')) {

                        
                    if (preg_match('/\n#\[Controller\]\n/', file_get_contents($file['path']))) {
                            if (!in_array($file['name'], self::$includes)) {
                            include_once $file['path'];
                            array_push(self::$includes, $file['name']);
                            
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
                        
                                        foreach ($method->getAttributes() as $attribute) {
                                            if (!key_exists($attribute->getName(), $this->controllers[$class_name]['mapping'])) {
                                                $method_name = strtoupper(explode('Mapping', $attribute->getName())[0]);
                                                
                                                if (!key_exists($method_name, $this->controllers[$class_name]['mapping']))
                                                    $this->controllers[$class_name]['mapping'][$method_name] = [];
                                            }
                                            if ($method->getName() === $this->endpoint) {
                                                foreach ($method->getParameters() as $param) {
                                                    foreach($param->getAttributes() as $param_attr) {
                                                        if ($param_attr->getName() === PathVariable::class) {
                                                            foreach($param_attr->getArguments() as $arg_name => $arg_val) {
                                                                if ($arg_name === 'require' && $arg_val && empty($request_parts[2]))
                                                                    RESTful::response('Not given Path varibale');

                                                                if (!empty($request_parts[2])) {
                                                                    if ($arg_name === 'validate') {
                                                                        if (!$this->validate_param_type($arg_val, $request_parts[2]))
                                                                            RESTful::response('Invalid data type');
                                                                    }
                                                                    if ($arg_name === 'name' && $arg_val !== $param->getName())
                                                                        RESTful::response('Invalid parameter name in function controller');
                                                                }
                                                                $this->parameters = $request_parts[2];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            $this->controllers[$class_name]['mapping'][$method_name][$method->getName()]['/'] = explode('/'.$method->getName().'/', $attribute->getArguments()[0])[1];
                                        }
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
      
            if (empty($controller_endpoints[$this->endpoint]))
            $this->forbidden(__LINE__);
         
            $class = $this->controllers[$request_parts[0]]['class_name'];
            $method = $this->endpoint;
            $post = json_decode(file_get_contents("php://input"));
            
            $class = new $class();
            if (!empty($this->parameters))
                $class->{$method}($this->parameters, $post);
            else
                $class->{$method}($post);
        } else {
            RESTful::response('Unauthorized', 401);
        }
    }

    /**
     * Activates RESTful with authorization
     *
     * @param string $session_key_for_token
     * @param string $session_key_for_expiration
     * @return void
     */
    static public function with_authorization(
        string $session_key_for_token, 
        string $session_key_for_expiration
    ) {
        self::$session_key_for_token = $session_key_for_token;
        self::$session_key_for_expiration =  $session_key_for_expiration;
    }

    /**
     * Returns response error
     *
     * @param integer $code
     * @param string $message
     * @return void
     */
    static public function response(string $message, int $code = 200) {
        header($_SERVER['SERVER_PROTOCOL'] . ' '.$code);
        echo json_encode(['error' => ['code' => $code, 'message' => $message]]);
        exit;
    }

    /**
     * Checks exists of parameter
     * of given data to controller functions
     * with given parameter name as array
     *
     * @param array $params
     * @param array $with
     * @return void
     */
    static public function exists(array $params, array $with) {
        if (empty($params))
            RESTful::response('needs parameter');

        foreach($params as $param_name => $param_value) {
            if (!in_array($param_name, $with) || count($params) != count($with))
                RESTful::response('invalid given parameters');

            if (empty($param_value))
                RESTful::response('invalid given value');
        }
    }

    /**
     * Returns error message if endpoint not exists
     *
     * @param [type] $line
     * @return void
     */
    private function forbidden ($line) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
        echo json_encode(['error' => ['code' => 401, 'message' => 'Unauthorized']]);
        exit;
    }

    /**
     * Check validation of given PathVariable
     *
     * @param [type] $type
     * @param [type] $value
     * @return bool
     */
    private function validate_param_type (Validate $validation_type, $value) : bool {
        switch($validation_type) {
            case Validate::INT: return preg_match('/^[0-9]+$/', $value);
            case Validate::FLOAT: return preg_match('/^[0-9]+.[0-9]+$/', $value);
            case Validate::STRING: return preg_match('/^[0-9a-zA-Z]+$/', $value);
            default: return false;
        }
    }

    /**
     * Returns token object with parsed value of given token string
     *
     * @param string $token_string
     * @return string|null
     */
    private function get_beaer_token () : string|null {
        $headers = apache_request_headers();
        if (!empty($headers) && array_key_exists('Authorization', $headers)) {
            $token_value = str_contains($headers['Authorization'], 'Bearer ') ? explode('Bearer ', $headers['Authorization'])[1] : $headers['Authorization'];
            return $token_value;
        } 
        return null;
    }

    /**
     * Checks if RESTful startet 
     * with authorization
     *
     * @return boolean
     */
    private function is_with_authorization () : bool {
        return  !is_null(self::$session_key_for_token) &&
                !is_null(self::$session_key_for_expiration);
    }

    /**
     * Checks token validation
     *
     * @return boolean
     */
    private function is_valid_token () : bool {
        $token = $this->get_beaer_token();
        if (session_status() !== PHP_SESSION_ACTIVE)
            session_start();
        $session_token = $_SESSION[self::$session_key_for_token];
        if (new DateTime() > new DateTime($_SESSION[self::$session_key_for_expiration])) {
            $session_token = '';
            session_destroy();
        }
        return ($this->is_with_authorization() && !is_null($token) && $token === $session_token);
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
