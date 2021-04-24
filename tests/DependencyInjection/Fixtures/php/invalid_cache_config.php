<?php

$container->loadFromExtension('acl', [
    'cache' => [
        'id' => 'security.acl.cache.doctrine',
        'pool' => 'cache.app',
    ],
]);
