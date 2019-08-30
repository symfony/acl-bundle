Access Control List Bundle
==============================================

The ACL bundle makes it possible to grant authorization based on resources.

[Read the bundle documentation](src/Resources/doc/index.rst).

Installation
------------

### Through Composer:

Install the bundle:

```
$ composer require symfony/acl-bundle
```

### Register the bundle in app/AppKernel.php :

``` php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new Symfony\Bundle\AclBundle\AclBundle(),
    );
}
```

Tests
-----

You can run the unit tests with the following command:

    $ cd path/to/acl-bundle/
    $ composer.phar install --dev
    $ ./vendor/bin/simple-phpunit
