<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;

abstract class DefaultExceptionMapper implements IExceptionMapper{

    public static function map(\Exception $e) {
        \Logger::getLogger(__CLASS__)->error($e);
        
        return HttpResponse::status(500);
    }

}

