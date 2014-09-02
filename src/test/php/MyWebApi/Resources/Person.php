<?php

namespace MyWebApi\Resources;

use Huge\IoC\Annotations\Component;
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
        return HttpResponse::ok();
    }

    /**
     * @Get
     * @Path(":mNumber")
     */
    public function get($id = '') {
        $person = new \stdClass();
        $person->id = $id;

        return HttpResponse::ok()->entity($person);
    }
    
    /**
     * @Delete
     * @Path(":mNumber")
     */
    public function delete($id = '') {
        $person = new \stdClass();
        $person->id = $id;
        
        return HttpResponse::ok()->entity($person);
    }
    
     /**
     * @Put
     * @Path(":mNumber")
     */
    public function put($id = '') {
        $requestBody = (object)$this->request->getEntity();
        $requestBody->id = $id;
        
        return HttpResponse::ok()->entity($requestBody);
    }

    /**
     * @Post
     * @Path("multipart")
     * @Consumes({"multipart/form-data"})
     */
    public function uploadPersons() {
        return HttpResponse::ok()->status(201)->entity(serialize($this->request->getEntity()));
    }
    
    /**
     * @Post
     * @Path("stream")
     * @Consumes({"application/octet-stream"})
     */
    public function uploadPersonsStream() {
        return HttpResponse::ok()->status(201)->entity(serialize($this->request->getEntity()));
    }
    
     /**
     * @Post
      * @Consumes({"application/x-www-form-urlencoded"})
     */
    public function post() {
        $person = new \stdClass();
        $person->id = uniqid();
        $person->name = $this->request->getParam('name');
        $person->entity = $this->request->getEntity();
        
        return HttpResponse::ok()->status(201)->entity($person);
    }

    /**
     * @Get
     * @Path("search/?:oNumber/?:oNumber")
     */
    public function search($numberA = '', $numberB = '') {
        $query = $this->request->getParam('query');

        $list = array();
        for ($i = 0; $i < 5; $i++) {
            $person = new \stdClass();
            $person->id = uniqid();
            $person->query = $query;
            $person->a = $numberA;
            $person->b = $numberB;
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

