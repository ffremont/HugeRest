<?php

namespace Huge\Rest\Exceptions;

/**
 * Exception générique pour les erreurs dans la web app
 */
class WebApplicationException extends \Exception{

    private $status;
    
    public function __construct($message, $status = 500) {
        parent::__construct($message);
        
        $this->status = $status;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}

