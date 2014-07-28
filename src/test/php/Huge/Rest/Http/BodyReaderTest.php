<?php

namespace Huge\Rest\Http;

use Huge\IoC\Container\DefaultIoC;
use Huge\IoC\Factory\ConstructFactory;
use Huge\IoC\Factory\SimpleFactory;

use Huge\Rest\Exceptions\ValidationException as RestValidationException;

class BodyReaderTest extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @test
     */
    public function validateKo() {
        $server = array(
            'REQUEST_URI' => '/person/001'
        );
        $get = array();

        $ioc = new DefaultIoC();
        $ioc->addDefinitions(array(array(
            'class' => 'Huge\Rest\Http\HttpRequest',
            'factory' => new ConstructFactory(array($server, $get))
                ), array(
            'class' => 'Huge\Rest\Http\BodyReader',
            'factory' => SimpleFactory::getInstance()
        ),array(
            'class' => 'Huge\Rest\WebAppIoC',
            'factory' => SimpleFactory::getInstance()
        )));
        
        $ioc->start();
        $data = array(
            'nom' => 'oo',
            'adresseEmail' => 'll'
        );
        $ioc->getBean('Huge\Rest\Http\HttpRequest')->setEntity($data);
        
        $ex = false;
        try{
            $ioc->getBean('Huge\Rest\Http\BodyReader')->validate('Huge\Rest\Data\Person');
        }catch(RestValidationException $e){
            $ex = $e;
        }
        
        $this->assertNotNull($ex);
        $this->assertNotEmpty($ex->getViolations());
        $this->assertCount(1, $ex->getViolations());
    }

}


