<?php

namespace Huge\Rest\Process\Readers;

use Huge\Rest\Process\IBodyReader;

class FormReader implements IBodyReader{

    public function __construct() {
        
    }

    public static function read($body) {
        return urldecode($body);
    }

}

