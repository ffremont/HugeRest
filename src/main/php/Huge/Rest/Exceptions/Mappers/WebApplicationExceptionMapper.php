<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;
use Huge\Rest\Exceptions\WebApplicationException;

abstract class WebApplicationExceptionMapper implements IExceptionMapper{

    public static function map(\Exception $e) {
        \Logger::getLogger(__CLASS__)->error($e);
        
        if($e instanceof WebApplicationException){
            return HttpResponse::status($e->getStatus());
        }else{
            return HttpResponse::status(500);
        }
    }

}

