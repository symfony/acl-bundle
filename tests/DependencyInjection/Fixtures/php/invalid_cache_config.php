<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $container->extension('acl', [
        'cache' => [
            'id' => 'security.acl.cache.doctrine',
            'pool' => 'cache.app',
        ],
    ]);
};
