<?php

$this->load('container1.php', $container);

$container->loadFromExtension('acl', [
    'cache' => [
        'pool' => 'cache.app',
    ],
]);
