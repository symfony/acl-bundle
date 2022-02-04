<?php

$this->load('container1.php', 'php');

$container->loadFromExtension('acl', [
    'cache' => [
        'pool' => 'cache.app',
    ],
]);
