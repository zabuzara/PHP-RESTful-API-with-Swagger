<?php
include_once './../Scan.php';
include_once './../Attributes.php';

$yaml_path = './src/swagger-config2.yaml';

final class Swagger {
    private $content = [];
    public function openapi(string $version) {
        array_push($this->content, 'openapi: "'.$version.'"');
        return $this;
    }

    public function info(string $version, string $title, string $description, array $license = ['name' => '', 'url' => '']) {
        array_push($this->content, "info:");
        array_push($this->content, '  version: "'.$version.'"');
        array_push($this->content, '  title: "'.$title.'"');
        array_push($this->content, '  description: "'.$description.'"');
        array_push($this->content, "  license:");
        array_push($this->content, '    name: '.$license['name']);
        array_push($this->content, '    url: '.$license['url']);
        return $this;
    }

    public function servers(array $servers = [['url' => '', 'description' => '']]) {
        array_push($this->content, "servers:");
        foreach($servers as $server) {
            array_push($this->content, '  - url: "'.$server['url'].'"');
            array_push($this->content, '    description: "'.$server['description'].'"');
        }
        return $this;
    }

    public function components(array $components = [[]]) {
        array_push($this->content, "components:");
        array_push($this->content, "  schemas:");
        foreach($components as $component) {
            array_push($this->content, '    '.$component['name'].':');
            array_push($this->content, '      type: object');
            array_push($this->content, '      properties:');
            
            foreach($component['properties'] as $porperty) {
                array_push($this->content, '        '.$porperty['name'].':');
                array_push($this->content, '          type: '.$porperty['type']);
            }
        }
        return $this;
    }

    public function paths(array $paths = [[]]) {
        array_push($this->content, "paths:");
        foreach($paths as $path) {
            array_push($this->content, '  '.$path['name'].':');
            array_push($this->content, '    '.strtolower($path['method']).':');
            // array_push($this->content, '      summary: "'.$path['params']['summary'].'"');
            array_push($this->content, '      tags:');
            array_push($this->content, '        - '.$path['args']['tags']);
            array_push($this->content, '      responses:');
            foreach($path['args']['responses'] as $response) {
                array_push($this->content, '        '.$response['code'].':');
                array_push($this->content, '          description: "'.$response['description'].'"');
            }

            if (array_key_exists('parameters', $path)) {
                array_push($this->content, '      parameters:');
                foreach($path['parameters'] as $parameter) {
                    array_push($this->content, '        - name: '.$parameter['name']);
                    if (array_key_exists('in', $parameter)) {
                        array_push($this->content, '          in: path');
                    }
                    if (array_key_exists('required', $parameter)) {
                        array_push($this->content, '          required: '.$parameter['required']);
                    }
                    if (array_key_exists('schema', $parameter)) {
                        array_push($this->content, '          schema:');
                        foreach($parameter['schema'] as $key => $param_schema_arg) {
                            array_push($this->content, '            '.$key.': '.$param_schema_arg);
                        }
                    }
                    // array_push($this->content, '          description: "'.$parameter['description'].'"');
                }
            }

            if (array_key_exists('requestBody', $path)) {
                array_push($this->content, '      requestBody:');
                array_push($this->content, '        content:');
                array_push($this->content, '          application/json:');
                array_push($this->content, '            schema:');
                array_push($this->content, '              $ref: '.$path['requestBody']['schema']);
                array_push($this->content, '          application/xml:');
                array_push($this->content, '            schema:');
                array_push($this->content, '              $ref: '.$path['requestBody']['schema']);
                array_push($this->content, '          application/x-www-form-urlencoded:');
                array_push($this->content, '            schema:');
                array_push($this->content, '              $ref: '.$path['requestBody']['schema']);
                array_push($this->content, '        required: true');
            }
        }
        return $this;
    }

    public function build () {
        return $this->content;
    }
}

abstract class ControllerMapper {
    static private $controllers = [];
    static private $classes = [];
    static private $endpoint = '';
    static private mixed $parameters = [];
    static private array $includes = [];

    static public function get_controllers() {
        $pwd = explode('/', getcwd());
        array_pop($pwd);
        array_pop($pwd);
        $pwd = join('/', $pwd);
    
        echo $pwd;
        foreach (Scan::directory($pwd/*'.'*/)->for('', true, true, false) as $file) {
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
                                    self::$controllers[$class_name]['request'] = '';
                                    self::$controllers[$class_name]['class_path'] = $file['path'];
                                    self::$classes[$class_name] = [];
                                } 
                    
                                if ($attribute->getName() !== Controller::class) {
                                    if (!array_key_exists('request', self::$controllers[$class_name]))
                                        break;
                    
                                    self::$controllers[$class_name]['request'] = $attribute->getArguments()[0];
                                }
                            }
                    
                            if (array_key_exists($class_name, self::$controllers)) {
                                self::$controllers[$class_name]['mapping'] = [];
                                foreach ($reflection->getMethods() as $method) {
                                    self::$classes[$class_name][$method->getName()] = [];
                                    
                                    foreach ($method->getAttributes() as $attribute) {
                                        if (!key_exists($attribute->getName(), self::$controllers[$class_name]['mapping'])) {
                                            $method_name = strtoupper(explode('Mapping', $attribute->getName())[0]);
                                            
                                            if (!key_exists($method_name, self::$controllers[$class_name]['mapping']))
                                                self::$controllers[$class_name]['mapping'][$method_name] = [];
                                        }
                                        if ($method->getName() === self::$endpoint) {
                                            foreach ($method->getParameters() as $param) {
                                                foreach($param->getAttributes() as $param_attr) {
                                        
                                                    if ($param_attr->getName() === PathVariable::class) {
                                                        foreach($param_attr->getArguments() as $arg_name => $arg_val) {
                                                            if ($arg_name === 'require' && $arg_val && empty($request_parts[2]))
                                                                RESTful::response('Not given Path varibale');

                                                            if (!empty($request_parts[2])) {
                                                                if ($arg_name === 'validate') {
                                                                    // if (!self::$validate_param_type($arg_val, $request_parts[2]))
                                                                    //     RESTful::response('Invalid data type');
                                                                }
                                                                if ($arg_name === 'name' && $arg_val !== $param->getName())
                                                                    RESTful::response('Invalid parameter name in function controller');
                                                            }
                                                            self::$parameters = $request_parts[2];
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    
                                        self::$controllers[$class_name]['mapping'][$method_name][$method->getName()]['parameters'] = $method->getParameters();
                                        $arg_parts =  explode('/'.$method->getName().'/', $attribute->getArguments()[0]);
                                        self::$controllers[$class_name]['mapping'][$method_name][$method->getName()]['/'] = count($arg_parts) > 1 ? $arg_parts[1] : '';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return self::$controllers;
    }
}


if (file_exists($yaml_path))
    unlink($yaml_path);


if (!file_exists($yaml_path)) {
    $paths = [];
    $controllers = ControllerMapper::get_controllers();

    if (!empty($controllers)) {
        foreach($controllers as $class_name => $controller) {
            foreach($controller['mapping'] as $method => $endpoint) {
                foreach($endpoint as $endpoint_name => $argument) {
                    $path = [];
                    $path['name'] = '/'.$controller['request'].'/'.$endpoint_name;
                    $path['method'] = $method;

                    $path['args'] = [
                        'tags' => ucfirst($controller['request']),
                        'responses' => [
                            ['code' => 401, 'description' => "Unauthorized"],
                            ['code' => 200, 'description' => "OK"]
                        ]
                    ];

                    // parameters:
                    // - name: id
                    //   in: path
                    //   description: id of user to return
                    //   required: true
                    //   schema:
                    //     type: integer
                    //     format: int64

                    if (!empty($argument['parameters'])) {
                        
                        foreach($argument['parameters'] as $param) {
                            $parameter = [];
                         
                        //  requestBody:
                            // description: The Authentication
                            // content:
                            //   application/json:
                            //     schema:
                            //       $ref: '#/components/schemas/AuthenticateRequestBody'
                            //   application/xml:
                            //     schema:
                            //       $ref: '#/components/schemas/AuthenticateRequestBody'
                            //   application/x-www-form-urlencoded:
                            //     schema:
                            //       $ref: '#/components/schemas/AuthenticateRequestBody'
                            // required: true

                            if ($param) {
                                $param_type = $param->getType()->getName();

                                if ($param_type === 'object') {
                                    if (!array_key_exists('requestBody', $path))
                                        $path['requestBody'] = [
                                            'schema' => '\'#/components/schemas/'. ucfirst($controller['request'].'\'')
                                        ];
                                } else {
                                    if (!array_key_exists('parameters', $path))
                                        $path['parameters'] = [];

                                    $parameter['name'] = $param->getName();
                                    $parameter['schema'] = [
                                        'type' => $param_type === 'int' ? 'integer' : $param_type
                                    ];
                                    $parameter['required'] = false;
                    
                                    if (!empty($param->getAttributes())) {
                                        foreach ($param->getAttributes() as $param_attr) {
                                    
                                            if ($param_attr->getName() === PathVariable::class) {
                                                $parameter['in'] = 'path';

                                                foreach($param_attr->getArguments() as $arg_name => $param_attr_arg) {
                                                    if ($arg_name === 'require') {
                                                        $parameter['required'] = $param_attr_arg ? 'true' : 'false';
                                                    }
                                                }
                
                                            }
                                        }
                                    } 
                                    array_push($path['parameters'], $parameter);
                                }
                            }
                        }
                    }

                    array_push($paths, $path);
                }
            }
        }

        // echo "\n\n";
        // print_r($paths); 
        // echo "\n\n";

        $swagger = new Swagger();
        $data = $swagger
                ->openapi("3.0.0")
                ->info(
                    version: "1.0.0", 
                    title: "Swagger RESTful PHP API", 
                    description: "PHP RESTful API with Swagger", 
                    license: ['name' => "MIT", 'url' => "https://opensource.org/license/mit/"]
                )
                ->components([
                    [
                        'name' => 'User',
                        'properties' => [
                            [
                                'name' => 'id',
                                'type' => 'integer'
                            ],
                            [
                                'name' => 'nickname',
                                'type' => 'string'
                            ]
                        ]
                    ],
                    [
                        'name' => 'Security',
                        'properties' => [
                            [
                                'name' => 'nickname',
                                'type' => 'string',
                            ],
                            [
                                'name' => 'password',
                                'type' => 'string',
                            ],
                        ]
                    ]
                ])
                ->servers([
                    [
                        'url' => "http://localhost/PHP-API-Template/",
                        "description" => "Chat API local"
                    ],
                    [
                        'url' => "https://api.toolchain.tech/api/chat/v1/",
                        "description" => "Chat API remote"
                    ]
                ])
                ->paths($paths)
                ->build();

     
        $file = fopen($yaml_path,'a+');
        fclose($file);

        if (file_exists($yaml_path)) {
            foreach($data as $line) {
                file_put_contents($yaml_path, $line."\n", FILE_APPEND);
            }
            
            echo "\n\n\nYAML generated!!\n\n\n";
            // exec('npm start --prefix ./local/swagger');
            // exec('npm start');
        }
    }
}
?>