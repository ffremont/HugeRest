<?php

namespace Huge\Rest\Exceptions;

class NotFoundException extends \Exception {
    
    /**
     *
     * @var 
     */
    private $uri;

    public function __construct ($uri) {
        parent::__construct('Chemin de la ressource introuvable');
        
        $this->uri = $uri;
    }
    
    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }
}

