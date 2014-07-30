<?php

namespace Huge\Rest\Exceptions;

class UploadException extends \Exception{

    public function __construct($message, $code = null) {
        parent::__construct($message, $code);
    }
}

