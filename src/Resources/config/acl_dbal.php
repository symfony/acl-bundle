<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\AclBundle\EventListener\AclSchemaListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Acl\Dbal\MutableAclProvider;
use Symfony\Component\Security\Acl\Dbal\Schema;
use Symfony\Component\Security\Acl\Domain\DoctrineAclCache;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;

return static function (ContainerConfigurator $container) {
    $tables = [
        'class_table_name' => '%security.acl.dbal.class_table_name%',
        'entry_table_name' => '%security.acl.dbal.entry_table_name%',
        'oid_table_name' => '%security.acl.dbal.oid_table_name%',
        'oid_ancestors_table_name' => '%security.acl.dbal.oid_ancestors_table_name%',
        'sid_table_name' => '%security.acl.dbal.sid_table_name%',
    ];

    $container->services()
        ->defaults()
            ->private()

        ->alias('security.acl.dbal.connection', 'database_connection')
            ->public()

        ->set('security.acl.dbal.provider', MutableAclProvider::class)
            ->args([
                new Reference('security.acl.dbal.connection'),
                new Reference('security.acl.permission_granting_strategy'),
                $tables,
                new Reference('security.acl.cache', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])

        ->set('security.acl.dbal.schema', Schema::class)
            ->public()
            ->args([
                $tables,
                new Reference('security.acl.dbal.connection'),
            ])

        ->set('security.acl.dbal.schema_listener', AclSchemaListener::class)
            ->args([
                new Reference('security.acl.dbal.schema'),
            ])

        ->alias('security.acl.provider', 'security.acl.dbal.provider')
            ->public()

        ->alias(AclProviderInterface::class, 'security.acl.provider')

        ->set('security.acl.cache.doctrine', DoctrineAclCache::class)
            ->args([
                new Reference('security.acl.cache.doctrine.cache_impl'),
                new Reference('security.acl.permission_granting_strategy'),
            ])

        ->alias('security.acl.cache.doctrine.cache_impl', 'doctrine.orm.default_result_cache')
    ;
};
