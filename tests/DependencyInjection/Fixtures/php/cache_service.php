<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $container->import('container1.php');

    $container->extension('acl', [
        'cache' => [
            'id' => 'security.acl.cache.doctrine',
        ],
    ]);
};
