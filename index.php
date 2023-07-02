<?php
include_once 'Scan.php';
include_once 'Attributes.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$request = explode('/PHP-API-Template/', $_SERVER['REQUEST_URI'])[1];
$request_parts = explode('/', $request);

function forbidden ($line) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 forbidden [ line => '.$line.' ]');
    echo json_encode(['error' => ['code' => 403, 'message' => 'access denied']]);
    exit;
}

$controller_names = [];
$controllers = [];
$classes = [];
foreach (Scan::directory('.')->for('', true, true, false) as $file) {
    include_once $file['path'];

    if (class_exists(explode('.php', $file['name'])[0])) {
        $class_name = explode('.php', $file['name'])[0];
        $reflection = new ReflectionClass($class_name);
   

        foreach ($reflection->getAttributes() as $attribute) {
            if ($attribute->getName() === Controller::class) {
                $controllers[$class_name]['request'] = '';
                $controllers[$class_name]['class_path'] = $file['path'];
                $classes[$class_name] = [];
            } 

            if ($attribute->getName() !== Controller::class) {
                if (!array_key_exists('request', $controllers[$class_name]))
                    break;

                $controllers[$class_name]['request'] = $attribute->getArguments()[0];
            }
        }

        if (array_key_exists($class_name, $controllers)) {

            $controllers[$class_name]['mapping'] = [];
            foreach ($reflection->getMethods() as $method) {
                $classes[$class_name][$method->getName()] = [];

                $args = [];
                foreach ($method->getAttributes() as $attribute) {
                    if (!key_exists($attribute->getName(), $controllers[$class_name]['mapping'])) {
                        $method_name = strtoupper(explode('Mapping', $attribute->getName())[0]);
                        
                        if (!key_exists($method_name, $controllers[$class_name]['mapping']))
                            $controllers[$class_name]['mapping'][$method_name] = [];
                    }
                    $controllers[$class_name]['mapping'][$method_name][$method->getName()]['/'] = explode('/'.$method->getName().'/', $attribute->getArguments()[0])[1];
                }
            }
        }
    }
}

foreach($controllers as $class_name => $controller) {
    $controllers[$controller['request']] = $controller;
    $controllers[$controller['request']]['class_name'] = $class_name;
    unset($controllers[$controller['request']]['request']);
    unset($controllers[$class_name]);
}

if (count($request_parts) > 3 || 
    count($request_parts) === 1 || 
    !array_key_exists(strtolower($request_parts[0]), $controllers)) {
    forbidden(__LINE__);
}

$request_argument = $request_parts[1];

if (empty($controllers[$request_parts[0]]['mapping'][$_SERVER['REQUEST_METHOD']])) {
    forbidden(__LINE__);
}

$controller = $controllers[$request_parts[0]];
$controller_endpoints = $controller['mapping'][$_SERVER['REQUEST_METHOD']];

if (empty($controller_endpoints[$request_parts[1]])) {
    forbidden(__LINE__);
}

if (!empty($controller_endpoints[$request_parts[1]]['/']) && 
    (count($request_parts) < 3 || empty($request_parts[2]))) {
    forbidden(__LINE__);
}

$class = $controllers[$request_parts[0]]['class_name'];
$method = $request_parts[1];
$argument = $request_parts[2];
$post = json_decode(file_get_contents("php://input"), true);

$class = new $class();

if (!empty($argument))
    $class->{$method}($argument, $post);
else
    $class->{$method}($post);