<?php

namespace Huge\Rest\Exceptions;

class InvalidResponseException extends \Exception{

    public function __construct($message) {
        parent::__construct($message);
    }

}

