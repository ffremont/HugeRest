<?php

namespace Huge\Rest\Resources;

use Huge\IoC\Annotations\Component;
use Huge\Rest\Annotations\Resource;
use Huge\Rest\Annotations\Path;
use Huge\Rest\Annotations\Consumes;
use Huge\Rest\Annotations\Get;
use Huge\Rest\Annotations\Post;

/**
 * @Component
 * 
 * @Resource
 * @Path("/person")
 */
class Person {

    public function __construct() {
        
    }
    
    /**
     * @Get
     * @Path("contrats")
     */
    public function contrats(){
        
    }
    
    /**
     * @Get
     * @Consumes({"application/json"})
     */
    public function get(){
        
    }
    
    /**
     * @Get
     * @Post
     * @Path("search/")
     * @Consumes({"application/json"})
     */
    public function getSearch(){
        
    }

}

