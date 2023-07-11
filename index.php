<?php
include_once './RESTful/index.php';

RESTful::with_authorization(
    session_key_for_token: 'token', 
    session_key_for_expiration: 'expiration_time'
);
new RESTful(document_root: '/PHP-Chat-API/', ignore_routes: ['security']);