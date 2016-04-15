<?php

$this->load('container1.php', $container);

$container->loadFromExtension('acl', array(
    'provider' => 'foo',
));
