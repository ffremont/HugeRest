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
        $ioc->getBean('Huge\Rest\Api')->processRoute($requete);
        
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
        $ioc->getBean('Huge\Rest\Api')->processRoute($requete);
        
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
        $ioc->getBean('Huge\Rest\Api')->processRoute($requete);
        
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
                    [1] => HEAD
                )

            [consumes] => 
            [produces] =>
         */
        $this->assertArrayHasKey('8bb913007d0239d6a3fc3afc34cc15f1', $routes);
        
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

            [consumes] => Array
                (
                    [0] => application/json
                )

            [produces] =>

         */
        $this->assertArrayHasKey('7094bd55ad76be24e9e21267e0b26128', $routes);
        
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

            [consumes] => Array
                (
                    [0] => application/json
                )

            [produces] =>

         */
        $this->assertArrayHasKey('c798474fb40a5d785b1db715571044f8', $routes);
        
    }
}

