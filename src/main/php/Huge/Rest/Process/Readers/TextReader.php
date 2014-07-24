<?php

namespace Huge\Rest\Process\Readers;

use Huge\Rest\Process\IBodyReader;

class TextReader implements IBodyReader{

    public function __construct() {
        
    }

    public static function read($body) {
        return $body;
    }

}

