<?php
// include_once './Scan.php';

// class ControllerMapper {
//     private $controllers = [];
//     private $classes = [];
//     private $endpoint = '';
//     private mixed $parameters = [];

//     public function __construct() {
//         $pwd = explode('/', getcwd());
//         array_pop($pwd);
//         $pwd = join('/', $pwd);
//         // echo $pwd;

//         foreach (Scan::directory($pwd/*'.'*/)->for('', true, true, false) as $file) {
//             if ($file['name'] !== 'RESTful.php' && str_contains($file['name'], '.php')) {
//                 if (str_contains(file_get_contents($file['path']), '#[Controller]')) {
//                     include_once $file['path'];
        
//                     if (class_exists(explode('.php', $file['name'])[0])) { 
                
//                         $class_name = explode('.php', $file['name'])[0];
//                         $reflection = new ReflectionClass($class_name);
                
//                         foreach ($reflection->getAttributes() as $attribute) {
//                             if ($attribute->getName() === Controller::class) {
//                                 $this->controllers[$class_name]['request'] = '';
//                                 $this->controllers[$class_name]['class_path'] = $file['path'];
//                                 $this->classes[$class_name] = [];
//                             } 
                
//                             if ($attribute->getName() !== Controller::class) {
//                                 if (!array_key_exists('request', $this->controllers[$class_name]))
//                                     break;
                
//                                 $this->controllers[$class_name]['request'] = $attribute->getArguments()[0];
//                             }
//                         }
                
//                         if (array_key_exists($class_name, $this->controllers)) {
//                             $this->controllers[$class_name]['mapping'] = [];
//                             foreach ($reflection->getMethods() as $method) {
//                                 $this->classes[$class_name][$method->getName()] = [];
                                
//                                 foreach ($method->getAttributes() as $attribute) {
//                                     if (!key_exists($attribute->getName(), $this->controllers[$class_name]['mapping'])) {
//                                         $method_name = strtoupper(explode('Mapping', $attribute->getName())[0]);
                                        
//                                         if (!key_exists($method_name, $this->controllers[$class_name]['mapping']))
//                                             $this->controllers[$class_name]['mapping'][$method_name] = [];
//                                     }
//                                     if ($method->getName() === $this->endpoint) {
//                                         foreach ($method->getParameters() as $param) {
//                                             foreach($param->getAttributes() as $param_attr) {
//                                                 if ($param_attr->getName() === PathVariable::class) {
//                                                     foreach($param_attr->getArguments() as $arg_name => $arg_val) {
//                                                         if ($arg_name === 'require' && $arg_val && empty($request_parts[2]))
//                                                             RESTful::response('Not given Path varibale');

//                                                         if (!empty($request_parts[2])) {
//                                                             if ($arg_name === 'validate') {
//                                                                 // if (!$this->validate_param_type($arg_val, $request_parts[2]))
//                                                                 //     RESTful::response('Invalid data type');
//                                                             }
//                                                             if ($arg_name === 'name' && $arg_val !== $param->getName())
//                                                                 RESTful::response('Invalid parameter name in function controller');
//                                                         }
//                                                         $this->parameters = $request_parts[2];
//                                                     }
//                                                 }
//                                             }
//                                         }
//                                     }
                                 
//                                     $this->controllers[$class_name]['mapping'][$method_name][$method->getName()]['parameters'] = $method->getParameters();
//                                     $arg_parts =  explode('/'.$method->getName().'/', $attribute->getArguments()[0]);
//                                     $this->controllers[$class_name]['mapping'][$method_name][$method->getName()]['/'] = count($arg_parts) > 1 ? $arg_parts[1] : '';
//                                 }
//                             }
//                         }
//                     }
//                 }
//             }
//         }
//     }

//     public function get_controllers () {
//         return $this->controllers;
//     }
// }