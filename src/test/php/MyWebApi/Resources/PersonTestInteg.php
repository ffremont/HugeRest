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
            $response = $client->get('/person/2')->send();
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
        /* @var $response GuzzleHttp\Message\Response */
        $response = null;
        $expectedJson = '{"id":"2"}';
        try{
            $response = $client->get('/person/2')->setHeader('accept', 'application/vnd.person.v1+json')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals($expectedJson, $response->getBody(true));
        $this->assertEquals('application/vnd.person.v1+json', $response->getContentType());
    }
    
    /**
     * @test
     */
    public function head_person_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        /* @var $response GuzzleHttp\Message\Response */
        $response = null;
        try{
            $response = $client->head('/person/2')->setHeader('accept', 'application/vnd.person.v1+json')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals('application/vnd.person.v1+json', $response->getContentType());
    }
    
    /**
     * @test
     */
    public function get_ping_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->get('/person')->setHeader('accept', 'text/plain,text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*\/*;q=0.8')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals('', $response->getBody(true));
        $this->assertEquals('text/plain', $response->getContentType());
    }
    
    /**
     *@test
     */
    public function delete_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        $expectedJson = '{"id":"2"}';
        try{
            $response = $client->delete('/person/2')->setHeader('accept', 'text/plain,application/vnd.person.v1+json,application/xhtml+xml,application/xml;q=0.9,image/webp,*\/*;q=0.8')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals($expectedJson, $response->getBody(true));
        $this->assertEquals('application/vnd.person.v1+json', $response->getContentType());
    }
    
     /**
     * @test
     */
    public function put_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        $expectedJson = '{"nom":"azerty2","id":"2"}';
        try{
            $response = $client->put('/person/2')->setBody('{"nom":"azerty2"}', 'application/json')->setHeader('accept', 'text/plain,application/vnd.person.v1+json,application/xhtml+xml,application/xml;q=0.9,image/webp,*\/*;q=0.8')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals($expectedJson, $response->getBody(true));
        $this->assertEquals('application/vnd.person.v1+json', $response->getContentType());
    }
    
    /**
     * @test
     */
    public function post_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->post('/person')->setBody('{"nom":"azerty2"}', 'application/json')->setHeader('accept', 'application/json')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(201, $status);
        $this->assertNotEmpty($response->getBody(true));
        $this->assertEquals('application/vnd.person.v1+json', $response->getContentType());
    }
    
    /**
     * @test
     */
    public function search_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->get('/person/search?query=Azer')->setHeader('accept', 'application/json')->send();
            $status = $response->getStatusCode();
        }catch(\Exception $e){
            $this->fail($e->getMessage());
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals('application/vnd.person.v1+json', $response->getContentType());
        $json = $response->getBody(true);
        $list = json_decode($json);
        $this->assertTrue(is_array($list));
    }
}

