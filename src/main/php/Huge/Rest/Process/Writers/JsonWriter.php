<?php

namespace Huge\Rest\Process\Writers;

use Huge\Rest\Process\IBodyWriter;

class JsonWriter implements IBodyWriter{

    public function __construct() {
        
    }

    public static function write($entity) {
        return json_encode($entity);
    }

}

