<?php

namespace Huge\Rest\Http;

class JsonHttpResponse extends HttpResponse{

    public function __construct() {
        parent::__construct();
        $this->setContentType('application/json');
    }
    
    public function setEntity($entity) {
        parent::setEntity($entity);
        
        $this->body = json_encode($entity);
    }
}

