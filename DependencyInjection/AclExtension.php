<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AclBundle\DependencyInjection;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

/**
 * AclExtension.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class AclExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $mainConfig = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($mainConfig, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('acl.xml');

        if (class_exists(Application::class)) {
            $loader->load('console.xml');
        }

        if (isset($config['cache']['id'])) {
            $container->setAlias('security.acl.cache', $config['cache']['id']);
        }
        $container->getDefinition('security.acl.voter.basic_permissions')->addArgument($config['voter']['allow_if_object_identity_unavailable']);

        // custom ACL provider
        if (isset($config['provider'])) {
            $container->setAlias('security.acl.provider', $config['provider']);

            return;
        }

        // no provider configured
        $loader->load('acl_dbal.xml');

        if (null !== $config['connection']) {
            $container->setAlias('security.acl.dbal.connection', sprintf('doctrine.dbal.%s_connection', $config['connection']));
        }

        $container
            ->getDefinition('security.acl.dbal.schema_listener')
            ->addTag('doctrine.event_listener', array(
                'connection' => $config['connection'],
                'event' => 'postGenerateSchema',
                'lazy' => true,
            ))
        ;

        $container->getDefinition('security.acl.cache.doctrine')->addArgument($config['cache']['prefix']);

        $container->setParameter('security.acl.dbal.class_table_name', $config['tables']['class']);
        $container->setParameter('security.acl.dbal.entry_table_name', $config['tables']['entry']);
        $container->setParameter('security.acl.dbal.oid_table_name', $config['tables']['object_identity']);
        $container->setParameter('security.acl.dbal.oid_ancestors_table_name', $config['tables']['object_identity_ancestors']);
        $container->setParameter('security.acl.dbal.sid_table_name', $config['tables']['security_identity']);
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/acl';
    }
}
