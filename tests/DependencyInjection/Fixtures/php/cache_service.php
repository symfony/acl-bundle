<?php

$this->load('container1.php');

$container->loadFromExtension('acl', [
    'cache' => [
        'id' => 'security.acl.cache.doctrine',
    ],
]);
