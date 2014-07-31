<?php

namespace Huge\Rest\Process\Writers;

use Huge\Rest\Process\IBodyWriter;

class TextWriter implements IBodyWriter{

    public function __construct() {
        
    }

    public static function write($entity) {
        if(is_string($entity))
            return $entity;
        
        if(is_object($entity)){
            if(method_exists($entity, '__toString')){
                return $entity->__toString();
            }else{
                return '';
            }
        }
        if(is_array($entity)){
            return implode("|",$entity);
        }
        
        return '';
    }

}

