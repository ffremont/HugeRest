<?php

namespace MyWebApi\Resources;

use Guzzle\Http as GuzzleHttp;

class CustomerTestInteg extends \PHPUnit_Framework_TestCase {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 'text/vnd.huge+plain n'a pas de writer
     * 
     *@test
     */
    public function get_txt_badWriter() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->get('/customer')->setHeader('accept', 'text/vnd.huge+plain')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(406, $status);
    }
    
    /**
     * Le reader pour "text/vnd.huge+plain" n'existe pas
     * 
     *@test
     */
    public function post_txt_badReader() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->post('/customer')->setBody('ok?', 'text/vnd.huge+plain')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(415, $status);
    }
    
    /**
     *@test
     */
    public function get_v1_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->get('/customer/12')->setHeader('accept', 'application/vnd.huge.v1+json')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals('v1', $response->getBody(true));
    }
    
    /**
     *@test
     */
    public function get_v2_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->get('/customer/12')->setHeader('accept', 'application/json')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(200, $status);
        $this->assertEquals('v2', $response->getBody(true));
    }
    
    /**
     * On ne précise pas dans les accepts et le contentType la version
     * 
     *@test
     */
    public function post_v2_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->post('/customer')->setBody('{}', 'application/json')->setHeader('accept', 'application/json')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(200, $status);
        $o = $response->getBody(true);
        $out = json_decode($o);
        $this->assertEquals(2, $out->version);
    }
    
    /**
     * On précise dans le contentTYpe mais pas les accepts la version
     * 
     *@test
     */
    public function post_v2_onlyCT_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->post('/customer')->setBody('{}', 'application/vnd.huge.v2+json')->setHeader('accept', 'application/json')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(200, $status);
        $o = $response->getBody(true);
        $out = json_decode($o);
        $this->assertEquals(2, $out->version);
    }
    
    /**
     * On précise dans le contentTYpe mais pas les accepts la version
     * 
     *@test
     */
    public function post_v2_CTEtAccept_ok() {
        $client = new GuzzleHttp\Client($GLOBALS['variables']['apache.integrationTest.baseUrl']);
        
        $status = null;
        $response = null;
        try{
            $response = $client->post('/customer')->setBody('{}', 'application/vnd.huge.v2+json')->setHeader('accept', 'application/vnd.huge.v2+json')->send();
            $status = $response->getStatusCode();
        }catch(GuzzleHttp\Exception\BadResponseException $e){
            $status = $e->getResponse()->getStatusCode();
        }
        
        $this->assertEquals(200, $status);
        $o = $response->getBody(true);
        $out = json_decode($o);
        $this->assertEquals(2, $out->version);
    }

}

