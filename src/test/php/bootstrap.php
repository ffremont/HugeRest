<?php
    $loader = require(__DIR__.'/../../../vendor/autoload.php');
    
    $loader->add('Huge\Rest\\', 'src/test/php/');
    \Huge\IoC\Container\SuperIoC::registerLoader(array($loader, 'loadClass'));
    
    $GLOBALS['resourcesDir'] = __DIR__.'/../resources';
    