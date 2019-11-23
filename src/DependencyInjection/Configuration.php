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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('acl');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('connection')
                    ->defaultNull()
                    ->info('any name configured in doctrine.dbal section')
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('id')->end()
                        ->scalarNode('prefix')->defaultValue('sf_acl_')->end()
                    ->end()
                ->end()
                ->scalarNode('provider')->end()
                ->arrayNode('tables')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('acl_classes')->end()
                        ->scalarNode('entry')->defaultValue('acl_entries')->end()
                        ->scalarNode('object_identity')->defaultValue('acl_object_identities')->end()
                        ->scalarNode('object_identity_ancestors')->defaultValue('acl_object_identity_ancestors')->end()
                        ->scalarNode('security_identity')->defaultValue('acl_security_identities')->end()
                    ->end()
                ->end()
                ->arrayNode('voter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('allow_if_object_identity_unavailable')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
