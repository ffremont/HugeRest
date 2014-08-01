<?php

namespace Huge\Rest\Process\Readers;

use Huge\Rest\Process\IBodyReader;
use Huge\Rest\Exceptions\WebApplicationException;

class UploadReader implements IBodyReader{

    public function __construct() {
        
    }

    /**
     * @param \Huge\Rest\Http\HttpRequest $request
     */
    public static function read($request){
        if(empty($_FILES)){
            throw new WebApplicationException('Lecture du flux Upload impossible car $_FILES est vide', 415);
        }
        
        return $_FILES;
    }

}

