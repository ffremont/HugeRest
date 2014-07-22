<?php

namespace Huge\Rest;

use Huge\IoC\Factory\SimpleFactory;

class WebAppIoCTest extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * @test
     */
    public function iocWebAppEmptyOk() {
        $ioc = new WebAppIoC('1.0');
        $ioc->start();
        
        /**
         * WebAppIo
         * Huge\Rest\Api
         *      Huge\Rest\Http\HttpRequest
         *      Huge\Rest\Routing\Route
         */
        $this->assertCount(4, $ioc->getBeans());
    }
    
    /**
     * @test
     */
    public function iocWebAppRessourceOk() {
        $ioc = new WebAppIoC('1.0');
        $ioc->addDefinitions(array(
            array(
                'class' => 'Huge\Rest\Resources\Person',
                'factory' =>  SimpleFactory::getInstance()
            )
        ));
        $ioc->start();
        
        $this->assertNotNull($ioc->getBean('Huge\Rest\Resources\Person'));
        $ressources = $ioc->getResources();
        
        $this->assertCount(1, $ressources);
        $this->assertContains('Huge\Rest\Resources\Person', $ressources);
    }

}

