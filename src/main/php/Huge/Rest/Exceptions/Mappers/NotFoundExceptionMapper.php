<?php

namespace Huge\Rest\Exceptions\Mappers;

use Huge\Rest\Http\HttpResponse;
use Huge\Rest\Process\IExceptionMapper;

abstract class NotFoundExceptionMapper implements IExceptionMapper{

    public static function map(\Exception $e) {
        return HttpResponse::code(404)->contentTypeTxt()->body($e->getMessage());
    }

}

