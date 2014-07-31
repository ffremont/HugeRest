<?php

namespace Huge\Rest\Exceptions;

class IOException extends \Exception{

    public function __construct($message, $code = null) {
        parent::__construct($message, $code);
    }
}

