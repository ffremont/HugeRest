<?php

namespace Huge\Rest\Exceptions\Models;

class Violation {

    private $field;
    private $message;
    
    function __construct($field, $message) {
        $this->field = $field;
        $this->message = $message;
    }
    
    public function getField() {
        return $this->field;
    }

    public function setField($field) {
        $this->field = $field;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }


}

