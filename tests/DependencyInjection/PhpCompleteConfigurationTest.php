<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AclBundle\Tests\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class PhpCompleteConfigurationTest extends CompleteConfigurationTest
{
    protected function loadFromFile(ContainerBuilder $container, $file)
    {
        $loadXml = new PhpFileLoader($container, new FileLocator(__DIR__.'/Fixtures/php'));
        $loadXml->load($file.'.php');
    }
}
