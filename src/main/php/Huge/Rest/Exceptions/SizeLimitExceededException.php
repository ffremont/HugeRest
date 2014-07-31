<?php

namespace Huge\Rest\Exceptions;

class SizeLimitExceededException extends \Exception{

    private $limit;
    private $value;
    
    public function __construct($message, $limit, $value) {
        parent::__construct($message);
        
        $this->limit = $limit;
        $this->value = $value;
    }
    
    public function getLimit() {
        return $this->limit;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }
}

