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

use Symfony\Bundle\AclBundle\Tests\Functional\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class FunctionalTestCase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        $class = self::getKernelClass();

        if (!isset($options['test_case'])) {
            throw new \InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            $options['test_case'],
            $options['root_config'] ?? 'config.yml',
            $options['environment'] ?? 'Aclbundletest'.strtolower($options['test_case']),
            $options['debug'] ?? true
        );
    }
}
