<?php
include_once './RESTful/local/classes/ControllerMapper.php';

$app_name = 'PHP RESTful API';
$mapper = new ControllerMapper();

echo '<!DOCTYPE html>';
echo '<html lang="en">';
echo '<head>'; 
echo '<title>'.$app_name.'</title>';
echo '<noscript>You need to activate JavaScript to use this page</noscript>';
echo '<meta charset="UTF-8">';
echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">';
echo '<meta http-equiv="Accept-CH" content="Viewport-Width, Width"/>';
echo '<link rel="shortcut icon" type="image/png" href="./favicon-32x32.png"/>';
echo '<meta name="description" content="PHP / API / OpenAPI / RESTful / REST API"/>';
echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">';
echo '<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>';
// echo '<link media="all" rel="stylesheet" href="./styles/index.css"/>';
echo '</head>'; 
echo "<body id='app' class='container-fuild row m-auto justify-content-between'>";

$url = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
?>

<div class="container">
    <div class="row">
        <div class="column">
            <h1 class="p-2">PHP RESTful API (php >= 8.0)</h1>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="accordion shadow-sm border-light" id="accordionPanelsStayOpenController">
            <?php
                foreach($mapper->get_controllers() as $class_name => $controller) {
                ?>
                    <div class="accordion-item border-light">
                        <h2 class="accordion-header" id="panelsStayOpen-heading-<?=$class_name?>"'>
                            <button class="accordion-button bg-secondary text-light border-light" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse-<?=$class_name?>" aria-expanded="true" aria-controls="panelsStayOpen-collapse-<?=$class_name?>">
                                <span><?=$class_name?></span>
                                <span>( request = /<?=$controller['request']?> )</span>
                            </button>
                        </h2>
                        <div id="panelsStayOpen-collapse-<?=$class_name?>" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-heading-<?=$class_name?>">
                            <div class="accordion-body border-light bg-light">
                                <div class="accordion border-light" id="accordionPanelsStayOpenEndpoints">
                                <?php
                                foreach($controller['mapping'] as $method => $endpoint) {
                                    $color = [
                                        'POST' => 'warning',
                                        'PUT' => 'primary',
                                        'GET' => 'success',
                                        'DELETE' => 'danger'
                                    ];
                                ?>
                                    <div class="accordion-item border-light shadow-sm mb-2">
                                        <h2 class="accordion-header border-light" id="panelsStayOpen-heading-<?=$class_name.$method?>"'>
                                            <button class="accordion-button shadow-none border-light bg-transparent <?=('border-outline-'.$color[$method])?>" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse-<?=$class_name.$method?>" aria-expanded="true" aria-controls="panelsStayOpen-collapse-<?=$class_name.$method?>">
                                                <span class="fw-bold <?=('text-'.$color[$method])?>"><?=$method?></span>
                                            </button>
                                        </h2>
                                        <div id="panelsStayOpen-collapse-<?=$class_name.$method?>" class="p-2 accordion-collapse collapse show" aria-labelledby="panelsStayOpen-heading-<?=$class_name.$method?>">
                                            <div class="accordion border-light" id="accordionPanelsStayOpenEndpointsFunc">
                                            <?php
                                                foreach($endpoint as $endpoint_name => $argument) {
                                            ?>
                                                    <div class="accordion-item border-light shadow-sm mb-2 bg-light">
                                                        <h2 class="accordion-header border-bottom" id="panelsStayOpen-heading-<?=$class_name.$method.$endpoint_name?>"'>
                                                            <button class="accordion-button bg-light shadow-none <?=('border-outline-'.$color[$method])?>" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse-<?=$class_name.$method.$endpoint_name?>" aria-expanded="true" aria-controls="panelsStayOpen-collapse-<?=$class_name.$method.$endpoint_name?>">
                                                                <span class="me-2">URL: </span>
                                                                <span class="text-light rounded px-3 py-2 bg-dark" style="font-size:0.8rem;"><?=$url.$controller['request']?>/<?=$endpoint_name.'/'.$argument['/']?></span>
                                                            </button>
                                                        </h2>
                                                        <div id="panelsStayOpen-collapse-<?=$class_name.$method.$endpoint_name?>" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-heading-<?=$class_name.$method.$endpoint_name?>">
                                                            <div class="row gap-3 p-4">
                                                                <?php                                                                
                                                                    foreach($argument['parameters'] as $param) {
                                                                        if ($param) {
                                                                            ?>
                                                                                <div class="row p-2 bg-white m-auto shadow-sm">
                                                                                    <h5 class="p-2">Params</h5>
                                                                                    <div>
                                                                                        <hr class="mx-0 mt-0 mb-3 p-0"/>
                                                                                    </div>
                                                                                    <div class="row">
                                                                                        <div class="col">
                                                                                            <span class="fw-bold">Name: </span>
                                                                                            <span class="text-dark mb-4">$<?=$param->getName()?></span>
                                                                                        </div>
                                                                                        <div class="col">
                                                                                            <span class="fw-bold">Value:</span>
                                                                                            <?php
                                                                                                if ($param->getType()->getName() === 'array') {
                                                                                            ?>
                                                                                                <!-- <textarea class="text-light mx-2 p-2 bg-black border-dark border-1"></textarea> -->
                                                                                                <div class="form-floating">
                                                                                                    <textarea class="form-control" placeholder="JSON Object" id="floatingTextarea2<?=$endpoint_name.$param->getName()?>" style="height: 300px"></textarea>
                                                                                                    <label for="floatingTextarea2<?=$endpoint_name.$param->getName()?>">JSON</label>
                                                                                                </div>
                                                                                            <?php
                                                                                                } else {
                                                                                            ?>
                                                                                                <div class="form-floating mb-3">
                                                                                                    <input type="email" class="form-control" id="floatingInput<?=$endpoint_name.$param->getName()?>" placeholder="name@example.com">
                                                                                                    <label for="floatingInput<?=$endpoint_name.$param->getName()?>"><?=strtoupper($param->getType()->getName())?></label>
                                                                                                </div>
                                                                                            <?php
                                                                                                }
                                                                                            ?>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            <?php
                                                                        }
                                                                    }
                                                                ?>
                                                            </div>
                                                            <div class="row px-3 py-2 justify-content-end">
                                                                <button class="text-light fw-bold w-auto mw-auto btn px-5 py-1 btn-sm btn-<?=$color[$method]?>">Try</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                            <?php
                                            }
                                            ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            ?>
            </div>
        </div>
    </div>
</div>

<?php
echo '</body>';
echo '</html>';
?>