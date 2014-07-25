<?php

namespace MyWebApi\Resources;

use Guzzle\Http as GuzzleHttp;

class PersonTestInteg extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @test
     */
    public function not_found() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
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
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
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
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        $expectedJson = '{"id":"azerty2"}';
        try{
            $response = $client->get('/person/azerty2')->setHeader('accept', 'application/vnd.person.v1+json')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals($expectedJson, $response->getBody(true));
    }

}

