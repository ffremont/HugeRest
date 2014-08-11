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
    public function valideOk(){
        $_SERVER['REQUEST_URI'] = '/person/001';
        $_REQUEST = array();

        $ioc = new DefaultIoC();
        $ioc->addDefinitions(array(array(
            'class' => 'Huge\Rest\Http\HttpRequest',
            'factory' => new ConstructFactory(array())
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
            'adresseEmail' => 'ff@ff.fr'
        );
        $ioc->getBean('Huge\Rest\Http\HttpRequest')->setEntity((object)$data);
        $ex = false;
        try{
            $ioc->getBean('Huge\Rest\Http\BodyReader')->validateEntity('Huge\Rest\Data\Person');
        }catch(RestValidationException $e){
            $ex = $e;
        }
        $this->assertFalse($ex);
    }
    
    /**
     * @test
     */
    public function valideArrayOk(){
        $_SERVER['REQUEST_URI'] = '/person/001';
        $_REQUEST = array();

        $ioc = new DefaultIoC();
        $ioc->addDefinitions(array(array(
            'class' => 'Huge\Rest\Http\HttpRequest',
            'factory' => new ConstructFactory(array())
                ), array(
            'class' => 'Huge\Rest\Http\BodyReader',
            'factory' => SimpleFactory::getInstance()
        ),array(
            'class' => 'Huge\Rest\WebAppIoC',
            'factory' => SimpleFactory::getInstance()
        )));
        
        $ioc->start();
        $p1 = (object)array(
            'nom' => 'oo',
            'adresseEmail' => 'ff@ff.fr'
        );
        $p2 = (object)array(
            'nom' => 'oobbb',
            'adresseEmail' => 'fdddf@ff.fr'
        );
        $ioc->getBean('Huge\Rest\Http\HttpRequest')->setEntity(array($p1, $p2));
        $ex = false;
        try{
            $ioc->getBean('Huge\Rest\Http\BodyReader')->validateList('Huge\Rest\Data\Person');
        }catch(RestValidationException $e){
            $ex = $e;
        }
        $this->assertFalse($ex);
    }
    
    /**
     * @test
     */
    public function validateKo() {
        $_SERVER['REQUEST_URI'] = '/person/001';
        $_REQUEST = array();

        $ioc = new DefaultIoC();
        $ioc->addDefinitions(array(array(
            'class' => 'Huge\Rest\Http\HttpRequest',
            'factory' => new ConstructFactory(array())
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
        $ioc->getBean('Huge\Rest\Http\HttpRequest')->setEntity((object)$data);
        
        $ex = false;
        try{
            $ioc->getBean('Huge\Rest\Http\BodyReader')->validateEntity('Huge\Rest\Data\Person');
        }catch(RestValidationException $e){
            $ex = $e;
        }
        
        $this->assertNotNull($ex);
        $this->assertNotEmpty($ex->getViolations());
        $this->assertCount(1, $ex->getViolations());
    }

}


