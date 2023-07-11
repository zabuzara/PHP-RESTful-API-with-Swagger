<?php
include_once './entity-framework/index.php';
include_once './RESTful/RESTful.php';

// RESTful::with_authorization(
//     session_key_for_token: 'token', 
//     session_key_for_expiration: 'expiration_time'
// );
new RESTful(document_root: '/PHP-API-Template/', ignore_routes: ['security']);

?>