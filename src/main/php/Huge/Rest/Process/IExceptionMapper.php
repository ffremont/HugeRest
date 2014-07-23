<?php

namespace Huge\Rest\Process;


interface IExceptionMapper {
    /**
     * 
     * @param \Exception $e
     * @return \Huge\Rest\Http\HttpResponse
     */
    public static function map(\Exception $e);
}

