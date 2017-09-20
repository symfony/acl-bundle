<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\AclBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class AclBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        // disable convention based registration as commands are registered as service
    }
}
