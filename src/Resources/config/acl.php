<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Acl\Domain\ObjectIdentityRetrievalStrategy;
use Symfony\Component\Security\Acl\Domain\PermissionGrantingStrategy;
use Symfony\Component\Security\Acl\Domain\SecurityIdentityRetrievalStrategy;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Acl\Voter\AclVoter;

return static function (ContainerConfigurator $container) {
    $container->services()
        ->defaults()
            ->private()

        ->set('security.acl.object_identity_retrieval_strategy', ObjectIdentityRetrievalStrategy::class)

        ->set('security.acl.security_identity_retrieval_strategy', SecurityIdentityRetrievalStrategy::class)
            ->args([
                new Reference('security.role_hierarchy'),
                new Reference('security.authentication.trust_resolver'),
            ])

        ->set('security.acl.permission_granting_strategy', PermissionGrantingStrategy::class)
            ->call('setAuditLogger', [
                new Reference('security.acl.audit_logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ])

        ->set('security.acl.permission.map', BasicPermissionMap::class)

        ->set('security.acl.voter.basic_permissions', AclVoter::class)
            ->args([
                new Reference('security.acl.provider'),
                new Reference('security.acl.object_identity_retrieval_strategy'),
                new Reference('security.acl.security_identity_retrieval_strategy'),
                new Reference('security.acl.permission.map'),
                new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ])
            ->tag('monolog.logger', ['channel' => 'security'])
            ->tag('security.voter', ['priority' => 255])
    ;
};
