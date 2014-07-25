<?php

namespace MyWebApi\Resources;

use Huge\Ioc\Annotations\Component;
use Huge\IoC\Annotations\Autowired;
use Huge\Rest\Annotations\Resource;
use Huge\Rest\Annotations\Path;
use Huge\Rest\Annotations\Produces;
use Huge\Rest\Annotations\Get;
use Huge\Rest\Annotations\Delete;
use Huge\Rest\Annotations\Put;
use Huge\Rest\Annotations\Post;
use Huge\Rest\Annotations\Consumes;
use Huge\Rest\Http\HttpRequest;
use Huge\Rest\Http\HttpResponse;

/**
 * Ressource "Person" qui a pour chemin "person" 
 * 
 * @Component
 * @Resource
 * @Path("person")
 * 
 * @Consumes({"application/vnd.person.v1+json", "application/json"})
 * @Produces({"application/vnd.person.v1+json"})
 */
class Person {

    /**
     * @Autowired("Huge\Rest\Http\HttpRequest")
     * @var \Huge\Rest\Http\HttpRequest
     */
    private $request;

    /**
     *
     * @var \Logger
     */
    private $logger; 
   
    public function __construct() {
        $this->logger = \Logger::getLogger(__CLASS__);
    }

    /**
     * @Get
     * @Consumes({"text/plain"})
     * @Produces({"text/plain"})
     */
    public function ping() {
        $this->logger->debug('ping');
        
        return HttpResponse::ok();
    }

    /**
     * @Get
     * @Path(":alpha")
     */
    public function get($id = '') {
        $person = new \stdClass();
        $person->id = $id;

        return HttpResponse::ok()->entity($person);
    }
    
    /**
     * @Delete
     * @Path(":alpha")
     */
    public function delete($id = '') {
        $person = new \stdClass();
        $person->id = $id;
        
        return HttpResponse::ok()->entity($person);
    }
    
     /**
     * @Put
     * @Path(":alpha")
     */
    public function put($id = '') {
        $requestBody = (object)$this->request->getEntity();
        $requestBody->id = $id;
        
        return HttpResponse::ok()->entity($requestBody);
    }

    /**
     * @Post
     */
    public function post() {
        $person = new \stdClass();
        $person->id = uniqid();
        
        return HttpResponse::ok()->code(201)->entity($person);
    }

    /**
     * @Get
     * @Path("search")
     */
    public function search() {
        $query = $this->request->getParamGet('query');

        $list = array();
        for ($i = 0; $i < 5; $i++) {
            $person = new \stdClass();
            $person->id = uniqid();
            $list[] = $person;
        }
        
        return HttpResponse::ok()->entity($list);
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

}

