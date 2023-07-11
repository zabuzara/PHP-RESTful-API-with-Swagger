<?php
$yaml_path = './swagger/src/swagger-config.yaml';

class Swagger {
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

    public function paths(array $paths = [[]]) {
        array_push($this->content, "paths:");
        foreach($paths as $path) {
            array_push($this->content, '  '.$path['name'].':');
            array_push($this->content, '    '.strtolower($path['method']).':');
            // array_push($this->content, '      summary: "'.$path['params']['summary'].'"');
            array_push($this->content, '      tags:');
            array_push($this->content, '        - '.$path['params']['tags']);
            array_push($this->content, '      responses:');
            foreach($path['params']['responses'] as $response) {
                array_push($this->content, '        '.$response['code'].':');
                array_push($this->content, '          description: "'.$response['description'].'"');
            }
        }
        return $this;
    }

    public function build () {
        return $this->content;
    }
}



// if (file_exists($yaml_path))
//     unlink($yaml_path);

// include_once './ControllerMapper.php';
// $mapper = ControllerMapper::get_controllers();
// print_r($mapper);

if (!file_exists($yaml_path)) {
  
    $paths = [];
//     foreach($mapper->get_controllers() as $class_name => $controller) {
//         foreach($controller['mapping'] as $method => $endpoint) {
//             foreach($endpoint as $endpoint_name => $argument) {
//                 $path = [];
//                 $path['name'] = '/'.$controller['request'].'/'.$endpoint_name;
//                 $path['method'] = $method;

//                 $path['params'] = [
//                     'tags' => strtoupper($controller['request']),
//                     'responses' => [
//                         ['code' => 401, 'description' => "Unauthorized"],
//                         ['code' => 200, 'description' => "OK"]
//                     ]
//                 ];

//                 foreach($argument['parameters'] as $param) {
    
//                     if ($param) {
//                         if ($param->getType()->getName() === 'array') {
//                             // $endpoint_name.$param->getName()

//                         } else {
//                             // $param->getType()->getName()
//                         }
//                     }
//                 }

//                 array_push($paths, $path);
//             }
//         }
//     }


//     $swagger = new Swagger();
//     $data = $swagger
//             ->openapi("3.0.0")
//             ->info(
//                 version: "1.0.0", 
//                 title: "Swagger RESTful PHP API", 
//                 description: "PHP RESTful API with Swagger", 
//                 license: ['name' => "MIT", 'url' => "https://opensource.org/license/mit/"]
//             )
//             ->servers([
//                 [
//                     'url' => "http://localhost/PHP-API-Template/",
//                     "description" => "Chat API local"
//                 ],
//                 [
//                     'url' => "https://api.toolchain.tech/api/chat/v1/",
//                     "description" => "Chat API remote"
//                 ]
//             ])
//             ->paths($paths)
//             ->build();


//     $file = fopen($yaml_path,'a+');
//     fclose($file);

//     foreach($data as $line) {
//         file_put_contents($yaml_path, $line."\n", FILE_APPEND);
//     }

//     exec('npm start --prefix ./local/swagger');
}
?>