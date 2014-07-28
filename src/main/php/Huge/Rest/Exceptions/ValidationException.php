<?php

namespace Huge\Rest\Exceptions;


class ValidationException extends \Exception{

    private $violations;
    
    public function __construct($violations = array(), $message =  '') {
        parent::__construct($message);
        
        $this->violations = $violations;
    }
    
    public function getViolations() {
        return $this->violations;
    }

    public function setViolations($violations) {
        $this->violations = $violations;
    }

}

