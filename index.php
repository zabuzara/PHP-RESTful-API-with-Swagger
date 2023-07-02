<?php
include_once 'Scan.php';
include_once 'Attributes.php';

$request = explode('/PHP-API-Template/', $_SERVER['REQUEST_URI'])[1];
$endpoints = ['user', 'product'];


$controllers = [];
$classes = [];
foreach (Scan::directory('.')->for('', true, true, false) as $file) {
    include_once $file['path'];

    if (class_exists(explode('.php', $file['name'])[0])) {
        $class_name = explode('.php', $file['name'])[0];
        $reflection = new ReflectionClass($class_name);
        foreach ($reflection->getAttributes() as $attribute) {
            if ($attribute->getName() === Controller::class) {
                $controllers[$class_name]['attributes'] = [];
                $controllers[$class_name]['class_path'] = $file['path'];
                $classes[$class_name] = [];
            }

            if (!array_key_exists('attributes', $controllers[$class_name]))
                break;

            if ($attribute->getName() !== Controller::class) {
                array_push(
                    $controllers[$class_name]['attributes'], 
                    [
                        'name' => $attribute->getName(), 
                        'args' => $attribute->getArguments() 
                    ]
                );
            }
        }

        if (array_key_exists($class_name, $controllers)) {
            $controllers[$class_name]['mapping'] = [];
            foreach ($reflection->getMethods() as $method) {
                $classes[$class_name][$method->getName()] = [];

                $args = [];
                foreach ($method->getAttributes() as $attribute) {
                    if (!key_exists($attribute->getName(), $controllers[$class_name]['mapping']))
                        $controllers[$class_name]['mapping'][$attribute->getName()] = [];
                        
                    array_push(
                        $controllers[$class_name]['mapping'][$attribute->getName()], 
                        [
                            'name' => $method->getName(),
                            'args' => $attribute->getArguments()
                        ]
                    );

                    if (count($attribute->getArguments()) > 0) {
                        array_push($args, $attribute->getArguments()[0]);
                    }
                }

                foreach ($method->getParameters() as $param) {
                    if (in_array('/{'.$param->getName().'}', $args)) {
                        $param_type = $param->getType()->getName();

                        switch($param_type) {
                            case 'int':
                                $classes[$class_name][$method->getName()]['regexp'] = $controllers[$class_name]['attributes'][0]['args'][0].'\/[0-9]+';
                                break;
                            case 'string':
                                $classes[$class_name][$method->getName()]['regexp'] = $controllers[$class_name]['attributes'][0]['args'][0].'\/.+';
                                break;
                            default:
                                break;
                        }
                    }
                }
            }
        }
    }
}

echo '<pre>';
echo $request;
echo "<br>";
echo "<br>";
print_r($classes);
echo '</pre>';

foreach($classes as $class_name => $class) {
    foreach($class as $method_name => $method) {
        echo $method[0];
        if (preg_match('/'.$method['reqexp'].'/', $request)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200');

            break;
            // $param = explode('/', $request)[1];
            // include_once $controllers[$class_name]['class_path'];
            // $controller = new $class_name();
            // $controller->{$method_name}($param);
            // break 2;
            // goto controller_called;
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 forbidden');
            exit;
        }
    }
}
// controller_called: