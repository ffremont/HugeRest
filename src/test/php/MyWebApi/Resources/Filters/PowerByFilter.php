<?php

namespace MyWebApi\Resources\Filters;

use Huge\IoC\Annotations\Component;
use Huge\Rest\Process\IResponseFilter;
use Huge\Rest\Exceptions\WebApplicationException;

/**
 * @Component
 */
class PowerByFilter implements IResponseFilter{

    const POWERBY = 'x-powerby';
    
    
    public function doFilter(\Huge\Rest\Http\HttpResponse $response) {
        $response->addHeader(self::POWERBY, 'test');
    }
}

