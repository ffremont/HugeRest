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
 * Ressource "Person" qui a pour chemin "customer" 
 * 
 * @Component
 * @Resource
 * @Path("customer")
 */
class Customer {

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
     * @Produces({"text/vnd.huge+plain"})
     */
    public function getTxt() {
        return HttpResponse::ok()->entity('ok_txt');
    }
    
     /**
     * @Post
      * @Consumes({"text/vnd.huge+plain"})
     * @Produces({"text/vnd.huge+plain"})
     */
    public function postTxt() {
        return HttpResponse::ok();
    }

    /**
     * @Get
     * @Path(":mNumber")
     * @Consumes({"application/vnd.huge.v1+json"})
     * @Produces({"text/plain"})
     */
    public function getV1($id = '') {
        return HttpResponse::ok()->entity('v1');
    }
    
    /**
     * @Get
     * @Path(":mNumber")
     * @Consumes({"application/vnd.huge.v2+json", "application/json"})
     * @Produces({"text/plain"})
     */
    public function getV2($id = '') {
        $customer = new \stdClass();
        $customer->id = $id;

        return HttpResponse::ok()->entity('v2');
    }
    
     /**
      * On précise dans le contentType la version mais pas dans le accept
      * 
     * @Post
      * @Consumes({"application/vnd.huge.v2+json", "application/json"})
      * @Produces({"application/json", "application/vnd.huge.v2+json"})
     */
    public function postv2($id = '') {
        $requestBody = (object)$this->request->getEntity();
        $requestBody->id = $id;
        $requestBody->version = 2;
        
        return HttpResponse::ok()->entity($requestBody);
    }
    
    /**
     * @Post
      * @Consumes({"application/vnd.huge.v1+json"})
      * @Produces({"application/json"})
     */
    public function postv1($id = '') {
        $requestBody = (object)$this->request->getEntity();
        $requestBody->id = $id;
        $requestBody->version = 1;
        
        return HttpResponse::ok()->entity($requestBody);
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest($request) {
        $this->request = $request;
    }

}

