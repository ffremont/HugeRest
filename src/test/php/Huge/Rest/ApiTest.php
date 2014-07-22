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
        $ioc->start();
        
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
        $ioc->start();
        $this->assertCount(5, $ioc->getDefinitions());
        
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
                )

            [contentTypes] => 

         */
        $this->assertArrayHasKey('d98c94842cc51c662701456469ce2286', $routes);
        
        /**
         * [idBean] => Huge\Rest\Resources\Person
            [classResource] => Huge\Rest\Resources\Person
            [methodResource] => get
            [uri] => person
            [methods] => Array
                (
                    [0] => GET
                )

            [contentTypes] => Array
                (
                    [0] => application/json
                )
         */
        $this->assertArrayHasKey('611926bdc953b88838e951c53aac6e5f', $routes);
        
        /**
         *[idBean] => Huge\Rest\Resources\Person
            [classResource] => Huge\Rest\Resources\Person
            [methodResource] => getSearch
            [uri] => person/search
            [methods] => Array
                (
                    [0] => GET
                    [1] => POST
                )

            [contentTypes] => Array
                (
                    [0] => application/json
                )
         */
        $this->assertArrayHasKey('6ddd9e2a6438348d90da952cba5e8da9', $routes);
        
    }
}

