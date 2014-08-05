<?php

namespace Huge\Rest\Process\Writers;

use Huge\Rest\Process\IBodyWriter;

class StreamWriter implements IBodyWriter{

    public function __construct() {
        
    }

    /**
     * Retourne la ressource
     * 
     * @param resource $entity
     * @return resource
     */
    public static function write($entity) {
        return get_resource_type($entity) === 'stream' ? $entity : null; 
    }

}

