<?php

namespace Huge\Rest\Process;


interface IBodyReader {
    /**
     * 
     * @param \Huge\Rest\Http\HttpRequest $request
     */
    public static function read($request);
}

?>
