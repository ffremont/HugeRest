<?php

namespace Huge\Rest\Case1\Resources;

use Huge\IoC\Annotations\Component;
use Huge\Rest\Annotations\Resource;
use Huge\Rest\Annotations\Path;
use Huge\Rest\Annotations\Get;

use Huge\Rest\Http\HttpResponse;

/**
 * @Component
 * @Resource
 * @Path("person")
 */
class Person {

    public function __construct() {
        
    }
    
    /**
     * @Get
     * @Path("search/:alpha")
     */
    public function search($query){
        
        return HttpResponse::ok()->setBody('Ok');
    }

}

