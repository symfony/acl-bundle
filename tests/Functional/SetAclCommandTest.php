<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AclBundle\Tests\Functional;

use Symfony\Bundle\AclBundle\Command\SetAclCommand;
use Symfony\Bundle\AclBundle\Tests\Functional\Bundle\TestBundle\Entity\Car;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\NoAceFoundException;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;
use Symfony\Component\Security\Core\User\User;

/**
 * Tests SetAclCommand.
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 * @requires extension pdo_sqlite
 */
class SetAclCommandTest extends FunctionalTestCase
{
    const OBJECT_CLASS = Car::class;
    const SECURITY_CLASS = User::class;

    public function testSetAclRole()
    {
        $objectId = 1;
        $securityUsername = 'kevin';
        $grantedPermission = 'VIEW';
        $role = 'ROLE_ADMIN';

        $application = $this->getApplication();
        $application->add(new SetAclCommand($application->getKernel()->getContainer()->get('security.acl.provider')));

        $setAclCommand = $application->find('acl:set');
        $setAclCommandTester = new CommandTester($setAclCommand);
        $setAclCommandTester->execute(array(
            'command' => 'acl:set',
            'arguments' => array($grantedPermission, sprintf('%s:%s', str_replace('\\', '/', self::OBJECT_CLASS), $objectId)),
            '--role' => array($role),
        ));

        $objectIdentity = new ObjectIdentity($objectId, self::OBJECT_CLASS);
        $userSecurityIdentity = new UserSecurityIdentity($securityUsername, self::SECURITY_CLASS);
        $roleSecurityIdentity = new RoleSecurityIdentity($role);
        $permissionMap = new BasicPermissionMap();

        /** @var \Symfony\Component\Security\Acl\Model\AclProviderInterface $aclProvider */
        $aclProvider = $application->getKernel()->getContainer()->get('security.acl.provider');
        $acl = $aclProvider->findAcl($objectIdentity, array($roleSecurityIdentity, $userSecurityIdentity));

        $this->assertTrue($acl->isGranted($permissionMap->getMasks($grantedPermission, null), array($roleSecurityIdentity)));
        $this->assertTrue($acl->isGranted($permissionMap->getMasks($grantedPermission, null), array($roleSecurityIdentity)));

        try {
            $acl->isGranted($permissionMap->getMasks('VIEW', null), array($userSecurityIdentity));
            $this->fail('NoAceFoundException not throwed');
        } catch (NoAceFoundException $e) {
        }

        try {
            $acl->isGranted($permissionMap->getMasks('OPERATOR', null), array($userSecurityIdentity));
            $this->fail('NoAceFoundException not throwed');
        } catch (NoAceFoundException $e) {
        }
    }

    public function testSetAclClassScope()
    {
        $objectId = 1;
        $grantedPermission = 'VIEW';
        $role = 'ROLE_USER';

        $application = $this->getApplication();
        $application->add(new SetAclCommand($application->getKernel()->getContainer()->get('security.acl.provider')));

        $setAclCommand = $application->find('acl:set');
        $setAclCommandTester = new CommandTester($setAclCommand);
        $setAclCommandTester->execute(array(
            'command' => 'acl:set',
            'arguments' => array($grantedPermission, sprintf('%s:%s', self::OBJECT_CLASS, $objectId)),
            '--class-scope' => true,
            '--role' => array($role),
        ));

        $objectIdentity1 = new ObjectIdentity($objectId, self::OBJECT_CLASS);
        $objectIdentity2 = new ObjectIdentity(2, self::OBJECT_CLASS);
        $roleSecurityIdentity = new RoleSecurityIdentity($role);
        $permissionMap = new BasicPermissionMap();

        /** @var \Symfony\Component\Security\Acl\Model\AclProviderInterface $aclProvider */
        $aclProvider = $application->getKernel()->getContainer()->get('security.acl.provider');

        $acl1 = $aclProvider->findAcl($objectIdentity1, array($roleSecurityIdentity));
        $this->assertTrue($acl1->isGranted($permissionMap->getMasks($grantedPermission, null), array($roleSecurityIdentity)));

        $acl2 = $aclProvider->createAcl($objectIdentity2);
        $this->assertTrue($acl2->isGranted($permissionMap->getMasks($grantedPermission, null), array($roleSecurityIdentity)));
    }

    private function getApplication()
    {
        $kernel = static::bootKernel(array('test_case' => 'Acl'));

        $application = new Application($kernel);

        $initAclCommand = $application->find('acl:init');
        $initAclCommandTester = new CommandTester($initAclCommand);
        $initAclCommandTester->execute(array('command' => 'acl:init'));

        return $application;
    }
}
