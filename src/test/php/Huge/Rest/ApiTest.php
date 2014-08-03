<?php

namespace Huge\Rest;

use Huge\IoC\Factory\SimpleFactory;
use Huge\Rest\Http\HttpRequest;

class ApiTest extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @test
     */
    public function apiFindRouteKo() {
        $_SERVER =array( 'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/rest/cr1/aa/bb');
        
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            ), 
            array( 'class' => 'Huge\Rest\Utils4Test\Log4phpFactory', 'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance() )
        ));
        $ioc->start();
        
        $requete = new HttpRequest($_SERVER);
        $ioc->getBean('Huge\Rest\Api')->processRoute($requete);
        
        $this->assertFalse($ioc->getBean('Huge\Rest\Api')->getRoute()->isInit());        
    }
    
     /**
     * @test 
     */
    public function apiFindRouteWithoutContextRootOk() {
        $_SERVER =  array(
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/person/contrats'
        );
        
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            ),
            array( 'class' => 'Huge\Rest\Utils4Test\Log4phpFactory', 'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance() )
        ));
        $ioc->run();
        
        $requete = new HttpRequest($_SERVER);
        $ioc->getBean('Huge\Rest\Api')->processRoute($requete);
        
        $this->assertTrue($ioc->getBean('Huge\Rest\Api')->getRoute()->isInit());        
    }
    
     /**
     * @test
     */
    public function apiFindRouteWithContextRootOk() {
        $_SERVER = array(
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/services/person/contrats'
        );
        
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            ),
            array( 'class' => 'Huge\Rest\Utils4Test\Log4phpFactory', 'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance() )
        ));
        $ioc->start();
        $ioc->getBean('Huge\Rest\Api')->setContextRoot('/services');
        
        $requete = new HttpRequest($_SERVER);
        $ioc->getBean('Huge\Rest\Api')->processRoute($requete);
        
        $this->assertNotNull($ioc->getBean('Huge\Rest\Api')->getRoute());     
    }
    
    /**
     * @test
     */
    public function apiLoadRoutesOk() {
        $_SERVER = array(
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/services/person/contrats'
        );
        
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            ),
            array( 'class' => 'Huge\Rest\Utils4Test\Log4phpFactory', 'factory' => \Huge\IoC\Factory\SimpleFactory::getInstance() )
        ));
        $ioc->start();
        $ioc->getBean('Huge\Rest\Api')->loadRoutes();
        $this->assertCount(12, $ioc->getDefinitions());
        
        $routes = $ioc->getBean('Huge\Rest\Api')->getRoutes();
        $this->assertCount(3, $routes);        
    }
}

