<?php

namespace Huge\Rest\Samples;

use Guzzle\Http as GuzzleHttp;

class ApiTestInteg extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @test
     */
    public function not_found() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['remote.integrationTest.url']);
        
        $status = null;
        try{
            $response = $client->get('/person/toto')->send();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(404, $status);
    }
    
    /**
     * @test
     */
    public function get_person_badAccept() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['remote.integrationTest.url']);
        
        $status = null;
        try{
            $response = $client->get('/person/azerty1')->send();
        }catch(\Exception $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(404, $status);
    }
    
    /**
     * @test
     */
    public function get_person_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['remote.integrationTest.url']);
        
        $status = null;
        try{
            $response = $client->get('/person/azerty1')->setHeader('accept', 'application/vnd.person.v1+json')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail();
        }
        
        $this->assertEquals(200, $status);
    }

}

