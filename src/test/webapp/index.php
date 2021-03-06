<?php

$loader = require(__DIR__ . '/../../../vendor/autoload.php');
$loader->add('MyWebApi\\', __DIR__ . '/../../../src/test/php/');
$loader->add('Huge\Rest\Utils4Test\\', __DIR__ . '/../../../src/test/php/');

// LOGGER log4php
$configurator = new \LoggerConfiguratorDefault();
\Logger::configure($configurator->parse(__DIR__.'/../resources/log4php.xml'));

\Huge\IoC\Container\SuperIoC::registerLoader(array($loader, 'loadClass'));

$ioc = new \Huge\Rest\WebAppIoC('1.1', array(
    'maxBodySize' => 20*1024 // 20Ko max
));
$ioc->setCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
$ioc->addBodyWriters(array(
    'application/vnd.person.v1+json' => 'Huge\Rest\Process\Writers\JsonWriter',
    'application/vnd.huge.v2+json' => 'Huge\Rest\Process\Writers\JsonWriter',
    'application/vnd.huge.v1+json' => 'Huge\Rest\Process\Writers\JsonWriter'    
));
$ioc->addBodyReaders(array(
    'application/vnd.huge.v2+json' => 'Huge\Rest\Process\Readers\JsonReader'
));
$ioc->addDefinitions(array(
    array(
        'class' => 'MyWebApi\Resources\Person',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),
    array(
        'class' => 'MyWebApi\Resources\Customer',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),
    array(
        'class' => 'Huge\Rest\Interceptors\PerfInterceptor',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),
    array(
        'class' => 'MyWebApi\Resources\Filters\AuthFilter',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),
    array(
        'class' => 'MyWebApi\Resources\Filters\PowerByFilter',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    ),
    array(
        'class' => 'Huge\Rest\Utils4Test\Log4phpFactory',
        'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance()
    )
));
$ioc->addRequestFiltersMapping(array(
    'Huge\Rest\Interceptors\PerfInterceptor' => '.*',
    'MyWebApi\Resources\Filters\AuthFilter' => 'customer/auth'
));
$ioc->addResponseFiltersMapping(array(
    'MyWebApi\Resources\Filters\PowerByFilter' => 'customer/auth'
));



$ioc->run();
?>