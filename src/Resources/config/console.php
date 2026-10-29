<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\AclBundle\Command\InitAclCommand;
use Symfony\Bundle\AclBundle\Command\SetAclCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
            ->private()

        ->set(InitAclCommand::class)
            ->args([
                new Reference('security.acl.dbal.connection'),
                new Reference('security.acl.dbal.schema'),
            ])
            ->tag('console.command', ['command' => 'acl:init'])

        ->set(SetAclCommand::class)
            ->args([
                new Reference('security.acl.provider'),
            ])
            ->tag('console.command', ['command' => 'acl:set'])
    ;
};
