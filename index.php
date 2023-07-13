<?php
include_once './entity-framework/index.php';
include_once './RESTful/RESTful.php';

RESTful::with_authorization(
    session_key_for_token: 'token', 
    session_key_for_expiration: 'expiration_time'
);

$document_root = '/PHP-API-Template/';
$domain_name = 'api.toolchain.tech';

if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === $domain_name) {
    $document_root = explode('/var/www/html/api', __DIR__);
    if (count($document_root) > 0)
        $document_root = $document_root[1].'/';
}
echo "Test AGAIN WOW.    <br><br>";
new RESTful(document_root: $document_root , ignore_routes: ['security']);
?>
