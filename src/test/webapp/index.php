<?php

$loader = require(__DIR__ . '/../../../vendor/autoload.php');
$loader->add('MyWebApi\\', __DIR__ . '/../../../src/test/php/');

// LOGGER
$configurator = new \LoggerConfiguratorDefault();
\Logger::configure($configurator->parse(__DIR__.'/../resources/log4php.xml'));

\Huge\IoC\Container\SuperIoC::registerLoader(array($loader, 'loadClass'));

$ioc = new \Huge\Rest\WebAppIoC('1.1', array(
    'maxBodySize' => 20*1024 // 20Ko max
));
$ioc->setCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
$ioc->addBodyWriters(array(
    'application/vnd.person.v1+json' => 'Huge\Rest\Process\Writers\JsonWriter'
));
$ioc->addDefinitions(array(
    array(
        'class' => 'MyWebApi\Resources\Person',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),
    array(
        'class' => 'Huge\Rest\Interceptors\PerfInterceptor',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    )
));
$ioc->addFiltersMapping(array(
    'Huge\Rest\Interceptors\PerfInterceptor' => '.*'
));


$ioc->run();
?>