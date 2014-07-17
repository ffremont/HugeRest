<?php

namespace Huge\Rest;

use Huge\IoC\Factory\SimpleFactory;

class ApiTest extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
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
        
        $ioc->getBean('Huge\Rest\Api')->loadRoutes();
        $routes = $ioc->getBean('Huge\Rest\Api')->getRoutes();
        
        $this->assertCount(3, $routes);
        /**
         * [idBean] => Huge\Rest\Resources\Person
            [uri] => person/contrats
            [methods] => Array
                (
                    [0] => GET
                )

            [contentTypes] =>
         */
        $this->assertArrayHasKey('138253c18d7885daf55b775a99c94d4d', $routes);
        
        /**
         * [idBean] => Huge\Rest\Resources\Person
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
        $this->assertArrayHasKey('09e3310e7092038abc46cedd369c233a', $routes);
        
        /**
         * [idBean] => Huge\Rest\Resources\Person
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
        $this->assertArrayHasKey('ed5c32b04ea9bf5569cf1b0b0e3d12a9', $routes);
        
    }
}

