<?php

$this->load('container1.php');

$container->loadFromExtension('acl', [
    'cache' => [
        'pool' => 'cache.app',
    ],
]);
