<?php
define ('PUBLIC_HTACCESS_RULES', [
    'RewriteEngine On',
    'ErrorDocument 403 "Access Denied"',
    'SetEnvIf HOST "^localhost" local_url',
    'Order Deny,Allow',
    'RewriteCond %{REQUEST_METHOD} (POST|GET|OPTIONS|PUT|DELETE)',
    'RewriteRule .* index.php',
    'RewriteCond %{HTTP:Authorization} .',
    'RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]',
    'Deny from all',
    'Allow from env=local_url',
    'Satisfy any',
    'Options All -Indexes',
    'IndexIgnore *',
    'IndexIgnore *.png *.zip *.jpg *.gif *.doc *.xml *.json *.md *.txt *.ttf *.php *.ico *js *.scss',
    '<FilesMatch "\.(ini|psd|log|sh|xml|txt|md)$">',
    '    Order allow,deny',
    '    Deny from all',
    '</FilesMatch>'
]);

if (!file_exists('.htaccess'))
    fopen('.htaccess', "w");

file_put_contents('.htaccess', '');
foreach(PUBLIC_HTACCESS_RULES as $rule) {
    file_put_contents('.htaccess', $rule."\n", FILE_APPEND);
}