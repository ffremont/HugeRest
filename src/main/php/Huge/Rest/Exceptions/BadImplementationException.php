<?php

namespace Huge\Rest\Exceptions;

class BadImplementationException extends \Exception{

    private $actual;
    private $expected;
    
    public function __construct ($actual, $expected, $message) {
        parent::__construct($message);
        
        $this->actual = $actual;
        $this->expected = $expected;
    }
    
    public function getActual() {
        return $this->actual;
    }

    public function getExpected() {
        return $this->expected;
    }
}

