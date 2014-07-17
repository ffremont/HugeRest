<?php

namespace Huge\Rest\Routing;

use Huge\IoC\Annotations\Component;

/**
 * @Component
 */
class Route {

    private $resourceClass;
    private $methodClass;
    private $uri;
    private $contentType;
    private $method;
    
    public function __construct() {
        
    }
    
    public function getResourceClass() {
        return $this->resourceClass;
    }

    public function setResourceClass($resourceClass) {
        $this->resourceClass = $resourceClass;
    }

    public function getMethodClass() {
        return $this->methodClass;
    }

    public function setMethodClass($methodClass) {
        $this->methodClass = $methodClass;
    }

    public function getUri() {
        return $this->uri;
    }

    public function setUri($uri) {
        $this->uri = $uri;
    }

    public function getContentType() {
        return $this->contentType;
    }

    public function setContentType($contentType) {
        $this->contentType = $contentType;
    }
    
    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = $method;
    }


}

