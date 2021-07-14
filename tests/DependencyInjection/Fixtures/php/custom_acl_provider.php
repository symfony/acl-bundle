<?php

$this->load('container1.php');

$container->loadFromExtension('acl', [
    'provider' => 'foo',
]);
