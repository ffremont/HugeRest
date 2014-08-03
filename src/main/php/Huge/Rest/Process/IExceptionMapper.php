<?php

namespace Huge\Rest\Process;


interface IExceptionMapper {
    /**
     * 
     * @param \Exception $e
     * @return \Huge\Rest\Http\HttpResponse
     */
    public function map(\Exception $e);
}

