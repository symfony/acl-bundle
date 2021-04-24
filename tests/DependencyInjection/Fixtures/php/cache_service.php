<?php

$this->load('container1.php', $container);

$container->loadFromExtension('acl', [
    'cache' => [
        'id' => 'security.acl.cache.doctrine',
    ],
]);
