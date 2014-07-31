<?php

namespace Huge\Rest\Process\Readers;

use Huge\Rest\Process\IBodyReader;

class TextReader implements IBodyReader{

    public function __construct() {
        
    }

    /**
     * @param \Huge\Rest\Http\HttpRequest $request
     */
    public static function read($request){
        return $request->getBody();
    }

}

