<?php

namespace Huge\Rest\Annotations;

/**
* @Annotation
* @Target({"METHOD"})
*/
final class Consumes {

    public $value;
    
    public function __construct($values = array()) {
        $this->value    = $values['value'];
    }

}

