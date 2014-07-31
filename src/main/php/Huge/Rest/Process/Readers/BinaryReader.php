<?php

namespace Huge\Rest\Process\Readers;

use Huge\Rest\Process\IBodyReader;

class BinaryReader implements IBodyReader{

    public function __construct() {
        
    }

    /**
     * @param \Huge\Rest\Http\HttpRequest $request
     */
    public static function read($request){
        return $request->getBody(10240); // lire par bloc de 10ko
    }

}

