<?php
include_once './ControllerMapper.php';

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
        foreach($servers as $server) {
            array_push($this->content, "servers:");
            array_push($this->content, '  - url: "'.$server['url'].'"');
            array_push($this->content, '  - description: "'.$server['description'].'"');
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

$mapper = new ControllerMapper();
print_r($mapper->get_controllers());

if (file_exists('./src/swagger-config2.yaml'))
    unlink('./src/swagger-config2.yaml');

if (!file_exists('./src/swagger-config2.yaml')) {

    // $url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

    $paths = [];

    foreach($mapper->get_controllers() as $class_name => $controller) {
        // $class_name
        // $controller['request']
       
        foreach($controller['mapping'] as $method => $endpoint) {
            // $method

            foreach($endpoint as $endpoint_name => $argument) {
                // $url.$controller['request']
                $path = [];
                // $url.$controller['request'].'/'.$endpoint_name.'/'.$argument['/']
                $path['name'] = $controller['request'].'/'.$endpoint_name;
                $path['method'] = $method;

                $path['params'] = [
                    'tags' => strtoupper($controller['request']),
                    'responses' => [
                        ['code' => 401, 'description' => "Unauthorized"],
                        ['code' => 200, 'description' => "OK"]
                    ]
                ];

                foreach($argument['parameters'] as $param) {
    
                    if ($param) {
                        // $param->getName()
        
                        if ($param->getType()->getName() === 'array') {
                            // $endpoint_name.$param->getName()

                        } else {
                            // $param->getType()->getName()
                        }
                    }
                }
            }
        }
    }




    $swagger = new Swagger();
    $data = $swagger
            ->openapi("3.0.0")
            ->info(
                version: "1.0.0", 
                title: "Swagger RESTful PHP API", 
                description: "PHP RESTful API with Swagger", 
                license: ['name' => "MIT", 'url' => "https://opensource.org/license/mit/"]
            )
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
            ->paths([
                [
                    'name' => "/user/get_all",
                    'method' => 'get',
                    'params' => [
                        'tags' => 'USERS',
                        'responses' => [
                            ['code' => 401, 'description' => "Unauthorized"],
                            ['code' => 200, 'description' => "OK"]
                        ]
                    ]
                ]
            ])
            ->build();


    $file = fopen('./local/swagger/src/swagger-config2.yaml','a+');
    fclose($file);

    foreach($data as $line) {
        file_put_contents('./local/swagger/src/swagger-config2.yaml', $line."\n", FILE_APPEND);
    }
}
?>