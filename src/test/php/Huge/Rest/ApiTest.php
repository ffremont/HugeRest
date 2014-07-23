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
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            )
        ));
        $ioc->start();
        
        $server = array(
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/rest/cr1/aa/bb'
        );
        $requete = new HttpRequest($server);
        $ioc->getBean('Huge\Rest\Api')->findRoute($requete);
        
        $this->assertFalse($ioc->getBean('Huge\Rest\Api')->getRoute()->isInit());        
    }
    
     /**
     * @test
     */
    public function apiFindRouteWithoutContextRootOk() {
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            )
        ));
        $ioc->run();
        
        $server = array(
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/person/contrats'
        );
        $requete = new HttpRequest($server);
        $ioc->getBean('Huge\Rest\Api')->findRoute($requete);
        
        $this->assertTrue($ioc->getBean('Huge\Rest\Api')->getRoute()->isInit());        
    }
    
     /**
     * @test
     */
    public function apiFindRouteWithContextRootOk() {
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            )
        ));
        $ioc->start();
        $ioc->getBean('Huge\Rest\Api')->setContextRoot('/services');
        
        $server = array(
            'HTTP_HOST' => 'localhost',
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/services/person/contrats'
        );
        $requete = new HttpRequest($server);
        $ioc->getBean('Huge\Rest\Api')->findRoute($requete);
        
        $this->assertNotNull($ioc->getBean('Huge\Rest\Api')->getRoute());     
    }
    
    /**
     * @test
     */
    public function apiLoadRoutesOk() {
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            )
        ));
        $ioc->run();
        $this->assertCount(6, $ioc->getDefinitions());
        
        $routes = $ioc->getBean('Huge\Rest\Api')->getRoutes();
        $this->assertCount(3, $routes);
        
        /**
         *[idBean] => Huge\Rest\Resources\Person
            [classResource] => Huge\Rest\Resources\Person
            [methodResource] => contrats
            [uri] => person/contrats
            [methods] => Array
                (
                    [0] => GET
                    [1] => HEAD
                )

            [contentTypes] => 
         */
        $this->assertArrayHasKey('ea964534a8049ac6f34133e9e5e7decb', $routes);
        
        /**
         * [idBean] => Huge\Rest\Resources\Person
            [classResource] => Huge\Rest\Resources\Person
            [methodResource] => get
            [uri] => person
            [methods] => Array
                (
                    [0] => GET
                    [1] => HEAD
                )

            [contentTypes] => Array
                (
                    [0] => application/json
                )

         */
        $this->assertArrayHasKey('8287e9f68a00f1039944ae9f9ab81318', $routes);
        
        /**
         *[idBean] => Huge\Rest\Resources\Person
            [classResource] => Huge\Rest\Resources\Person
            [methodResource] => getSearch
            [uri] => person/search
            [methods] => Array
                (
                    [0] => GET
                    [1] => HEAD
                    [2] => POST
                )

            [contentTypes] => Array
                (
                    [0] => application/json
                )

         */
        $this->assertArrayHasKey('36f9eea8db2b822010ab3f19fb19e3b2', $routes);
        
    }
}

