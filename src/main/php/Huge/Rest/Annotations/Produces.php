<?php

namespace Huge\Rest\Annotations;

/**
* @Annotation
* @Target({"CLASS","METHOD"})
*/
final class Produces {

    public $value;

    public function __construct($values = array()) {
        $this->value    = $values['value'];
    }
}

