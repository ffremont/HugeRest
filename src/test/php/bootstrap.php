<?php
    $loader = require(__DIR__.'/../../../vendor/autoload.php');
    
    $loader->add('Huge\Rest\\', 'src/test/php/');
    \Huge\IoC\Container\SuperIoC::registerLoader(array($loader, 'loadClass'));
    
    $GLOBALS['resourcesDir'] = __DIR__.'/../resources';
    $GLOBALS['variables'] = parse_ini_file($GLOBALS['resourcesDir'].'/variables.ini');
    
    // LOGGER
$configurator = new \LoggerConfiguratorDefault();
\Logger::configure($configurator->parse($GLOBALS['resourcesDir'].'/log4php.xml'));

    