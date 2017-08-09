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

/**
 * @author Iltar van der Berg <kjarli@gmail.com>
 */
class BundleDeprecationsTest extends FunctionalTestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Both the SecurityBundle and the AclBundle are trying to configure the ACL, configure the AclBundle under "acl" only.
     */
    public function testConfiguredUnderBoth()
    {
        $kernel = self::createKernel(['test_case' => 'BothConfigured']);
        $kernel->boot();
    }

    /**
     * @group legacy
     * @expectedDeprecation As of 3.4 the "security.acl" config is deprecated and will be removed in 4.0. Install symfony/acl-bundle configure it under "acl" instead.
     */
    public function testIfDeprecationIsFired()
    {
        $kernel = self::createKernel(['test_case' => 'BundleDeprecations']);
        $kernel->boot();
    }
}
