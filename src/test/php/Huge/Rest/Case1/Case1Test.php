<?php

namespace Huge\Rest;

use Huge\IoC\Factory\SimpleFactory;
use Huge\Rest\WebAppIoC;

class Case1Test extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @test
     */
    public function apiFindRouteKo() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $_SERVER['REQUEST_URI'] = '/person/search/azerty12';
        
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Case1\Resources\Person',
                'factory' => SimpleFactory::getInstance()
            )
        ));
        
        $ioc->start();       
    }
}

